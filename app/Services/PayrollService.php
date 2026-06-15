<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Dtr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Verifies if all employees in the payroll batch have finalized DTRs 
     * and identifies any attendance red flags.
     */
    public function verifyAttendance(Payroll $payroll)
    {
        $query = Employee::where('status', 'active');
        
        if ($payroll->payroll_group_id) {
            $query->where('payroll_group_id', $payroll->payroll_group_id);
        } elseif ($payroll->employee_id) {
            $query->where('id', $payroll->employee_id);
        }

        $employees = $query->get();
        $dtrs = Dtr::whereIn('employee_id', $employees->pluck('id'))
            ->whereDate('start_date', $payroll->start_date)
            ->whereDate('end_date', $payroll->end_date)
            ->get()
            ->keyBy('employee_id');

        $results = [
            'total_employees' => $employees->count(),
            'missing_dtr' => [],
            'pending_dtr' => [],
            'with_absences' => [],
            'can_process' => true,
        ];

        foreach ($employees as $employee) {
            $dtr = $dtrs->get($employee->id);

            if (!$dtr) {
                $results['missing_dtr'][] = $employee->full_name;
                $results['can_process'] = false;
            } elseif ($dtr->status !== 'finalized') {
                $results['pending_dtr'][] = [
                    'name' => $employee->full_name,
                    'status' => $dtr->status
                ];
                $results['can_process'] = false;
            }
            
            if ($dtr && $dtr->total_absent_days > 0) {
                $results['with_absences'][] = [
                    'name' => $employee->full_name,
                    'days' => $dtr->total_absent_days
                ];
                // The user says "absent employee" blocks payroll
                $results['can_process'] = false;
            }
        }

        return $results;
    }

    public function computePayroll(Payroll $payroll)
    {
        try {
            return DB::transaction(function () use ($payroll) {
                // ATOMIC LOCK: Use database-level lock to prevent double-processing
                $affected = DB::table('payrolls')
                    ->where('id', $payroll->id)
                    ->whereIn('status', ['draft', 'pending'])
                    ->update([
                        'status' => 'processing',
                        'updated_at' => now()
                    ]);

                if (!$affected && $payroll->status !== 'processing') {
                    return false;
                }

                $query = Employee::where('status', 'active');
                
                if ($payroll->payroll_group_id) {
                    $query->where('payroll_group_id', $payroll->payroll_group_id);
                } elseif ($payroll->employee_id) {
                    $query->where('id', $payroll->employee_id);
                }

                $employees = $query->get();
                
                // PERFORMANCE: Eager load DTRs to avoid N+1 query problem
                $dtrs = Dtr::whereIn('employee_id', $employees->pluck('id'))
                    ->whereDate('start_date', $payroll->start_date)
                    ->whereDate('end_date', $payroll->end_date)
                    ->get()
                    ->keyBy('employee_id');

                // CONSISTENCY: Fetch settings once before the loop (Agent 1 simulation fix)
                $settings = \App\Models\AppSetting::first();
                $sssRate = $settings->sss_rate ?? 0.05;
                $pagibigRate = $settings->pagibig_rate ?? 0.02;
                $philhealthRate = $settings->philhealth_rate ?? 0.03;

                $items = [];

                foreach ($employees as $employee) {
                    // Check for DTR (finalized or otherwise)
                    $dtr = $dtrs->get($employee->id);

                    $dailyRate = $employee->daily_rate;
                    $hourlyRate = $dailyRate / 8;

                    // Compute values based on DTR if it exists, otherwise default to zero
                    $basicPay = 0;
                    $overtimePay = 0;
                    if ($dtr) {
                        $basicPay = ($dtr->total_regular_hours / 8) * $dailyRate;
                        $overtimePay = $dtr->total_overtime_hours * $hourlyRate * 1.25; 
                    }

                    $attendances = Attendance::where('employee_id', $employee->id)
                        ->whereBetween('date', [$payroll->start_date, $payroll->end_date])
                        ->get();

                    $totalDays = $attendances->count();
                    $totalHours = $attendances->sum('total_hours');
                    
                    // Bonuses & Night Diff (simplified as requested)
                    // Logic: Only award "Perfect Attendance" bonus if they actually worked at least 5 days, 
                    // have no absences, AND have actual hours rendered (not just empty clocks).
                    $hasAbsences = $dtr ? ($dtr->total_absent_days > 0) : true;
                    $bonuses = ($totalDays >= 5 && !$hasAbsences && $totalHours > 0) ? 500 : 0; 
                    $nightDiff = 0; 

                    // Deductions logic
                    $deductions = [];
                    $sssAmt = 0;
                    $pagibigAmt = 0;
                    $philhealthAmt = 0;
                    $otherDeductions = 0;

                    // Late/UT Deductions
                    // Optimization: Skip attendance deductions if BASIC PAY is 0 (fully absent)
                    // This prevents "double-penalizing" where an employee gets 0 pay AND high negative deductions.
                    if ($basicPay > 0 && $dtr) {
                        if ($dtr->total_late_minutes > 0) {
                            $amt = min($basicPay * 0.5, floor(($dtr->total_late_minutes / 60) * $hourlyRate));
                            $deductions[] = [
                                'type' => 'LATE', 
                                'amount' => $amt
                            ];
                            $otherDeductions += $amt;
                        }
                        if ($dtr->total_undertime_minutes > 0) {
                            $amt = min($basicPay * 0.5, floor(($dtr->total_undertime_minutes / 60) * $hourlyRate));
                            $deductions[] = [
                                'type' => 'UT', 
                                'amount' => $amt
                            ];
                            $otherDeductions += $amt;
                        }
                    }

                    foreach (['sss', 'pagibig', 'philhealth'] as $type) {
                        $rate = $settings->{$type . '_rate'} ?? 0.05;
                        $amt = floor($basicPay * $rate);
                        if ($amt > 0) {
                            $deductions[] = ['type' => strtoupper($type), 'amount' => $amt];
                            if ($type === 'sss') $sssAmt = $amt;
                            if ($type === 'pagibig') $pagibigAmt = $amt;
                            if ($type === 'philhealth') $philhealthAmt = $amt;
                        }
                    }

                    $totalDeductions = array_sum(array_column($deductions, 'amount'));
                    
                    // ENSURE NO NEGATIVE NET PAY: Cap at zero to handle cases with zero basic but fixed deductions
                    $grossPay = $basicPay + $overtimePay + $bonuses + $nightDiff;
                    $netPay = max(0, $grossPay - $totalDeductions);

                    // IDEMPOTENCY: Use updateOrCreate to ensure no double-records if job retries
                    $items[] = PayrollItem::updateOrCreate(
                        ['payroll_id' => $payroll->id, 'employee_id' => $employee->id],
                        [
                            'snapshot_daily_rate' => $dailyRate,
                            'snapshot_position' => $employee->position,
                            'snapshot_group' => $employee->payrollGroup?->name ?? 'N/A',
                            'total_days' => $totalDays,
                            'total_hours' => $totalHours,
                            'basic_pay' => $basicPay,
                            'overtime_pay' => $overtimePay,
                            'night_diff' => $nightDiff,
                            'bonuses' => $bonuses,
                            'deductions_json' => $deductions,
                            'deductions_sss' => $sssAmt,
                            'deductions_pagibig' => $pagibigAmt,
                            'deductions_philhealth' => $philhealthAmt,
                            'other_deductions' => $otherDeductions,
                            'net_pay' => $netPay,
                        ]
                    );
                }

                $payroll->update(['status' => 'processed']);
                return $items;
            });

        } catch (\Exception $e) {
            // Rollback status if something fails during the batch
            $payroll->update(['status' => 'pending']);
            throw $e;
        }
    }

    public function calculateAttendanceStats($timeIn, $timeOut, $employeeId = null, $date = null)
    {
        // Fetch the specific attendance record to check for breaks
        $attendance = null;
        if ($employeeId && $date) {
            $attendance = \App\Models\Attendance::where('employee_id', $employeeId)
                ->where('date', $date)
                ->first();
        }

        // Treat 00:00:00 as no punch/invalid for calculations
        $isNoPunchIn = $timeIn === '00:00:00' || !$timeIn;
        $isNoPunchOut = $timeOut === '00:00:00' || !$timeOut;

        $dateStr = $date ?? Carbon::now()->toDateString();
        $in = Carbon::parse($dateStr . ' ' . $timeIn);
        $out = $isNoPunchOut ? null : Carbon::parse($dateStr . ' ' . $timeOut);

        if ($isNoPunchIn) {
            return [
                'total_hours' => 0,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
            ];
        }

        $dayName = Carbon::parse($dateStr)->format('l');

        // Default fallbacks
        $scheduleInTime = '08:00:00';
        $scheduleOutTime = '17:00:00';
        $isRestDay = false;

        $isNoSchedule = false;

        // Try to get actual schedule if employee provided
        if ($employeeId) {
            $employee = \App\Models\Employee::find($employeeId);
            if ($employee) {
                $sched = $employee->getScheduleForDate($dateStr);
                if ($sched) {
                    $scheduleInTime = $sched->time_in;
                    $scheduleOutTime = $sched->time_out;
                    if ($sched->is_rest_day) $isRestDay = true;
                    goto schedule_found;
                } else {
                    $isNoSchedule = true;
                }
            }
        }

        schedule_found:

        // Handle actual punches crossing midnight
        if ($out && $out->lessThan($in)) {
            $out->addDay();
        }

        if ($isRestDay || $isNoSchedule) {
            $totalStayMinutes = !$out ? 0 : $out->diffInMinutes($in, true);
            
            // Check if OT is authorized even for rest days
            $isAuthorized = false;
            if ($employeeId && $date) {
                $isAuthorized = \App\Models\Attendance::where('employee_id', $employeeId)
                    ->where('date', $date)
                    ->where('ot_authorized', true)
                    ->exists();
            }

            // If not authorized, rest day work doesn't count as OT (might count as regular? usually it's OT)
            // But we respect "don't calculate OT unless official"
            return [
                'total_hours' => 0, 
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => $isAuthorized ? round($totalStayMinutes / 60, 2) : 0,
            ];
        }

        $scheduleIn = Carbon::parse($dateStr . ' ' . $scheduleInTime);
        $scheduleOut = Carbon::parse($dateStr . ' ' . $scheduleOutTime);

        // Handle Night Shift schedule
        $isNightShift = false;
        if ($scheduleOut->lessThan($scheduleIn)) {
            $scheduleOut->addDay();
            $isNightShift = true;
        }

        // Handle punch crossing midnight for night shifts
        // If punch time (e.g. 02:00) is much earlier than schedule in (e.g. 22:00) 
        // on the same dateStr, it's likely actually the next day for a night shift.
        if ($isNightShift && $in->lessThan($scheduleIn) && $in->hour < 12) {
             $in->addDay();
        }

        // Same for out punch if it exists
        if ($out && $isNightShift && $out->lessThan($scheduleIn) && $out->hour < 12) {
             $out->addDay();
        }

        // LATE CALCULATION
        $lateMinutes = 0;
        if ($in->greaterThan($scheduleIn)) {
             $lateMinutes = (int) $scheduleIn->diffInMinutes($in, true);
        }
        
        // UNDERTIME CALCULATION
        $undertimeMinutes = 0;
        if ($out) {
            if ($out->lessThan($scheduleOut)) {
                $undertimeMinutes = (int) $out->diffInMinutes($scheduleOut, true);
            }
        } else {
            // Missing out punch - full undertime
            $undertimeMinutes = (int) $scheduleOut->diffInMinutes($scheduleIn, true);
        }

        // OVERTIME CALCULATION (Minutes beyond schedule out)
        $overtimeMinutes = 0;
        
        // OT is only calculated if "Official" (ot_authorized is true)
        $isAuthorized = false;
        if ($employeeId && $date) {
            $isAuthorized = \App\Models\Attendance::where('employee_id', $employeeId)
                ->where('date', $date)
                ->where('ot_authorized', true)
                ->exists();
        }

        if ($isAuthorized && $out && $out->greaterThan($scheduleOut)) {
             $overtimeMinutes = (int) $scheduleOut->diffInMinutes($out, true);
        }

        // Calculate actual break durations if they exist
        $actualLunchMinutes = 0;
        
        // Lunch Break
        if ($attendance && $attendance->lunch_out && $attendance->lunch_in && 
            $attendance->lunch_out !== '00:00:00' && $attendance->lunch_in !== '00:00:00') {
            
            $lout = Carbon::parse($dateStr . ' ' . $attendance->lunch_out);
            $lin = Carbon::parse($dateStr . ' ' . $attendance->lunch_in);
            
            if ($lin->lessThan($lout)) $lin->addDay();
            $actualLunchMinutes = $lin->diffInMinutes($lout, true);
        }

        $actualOtherBreakMinutes = 0;

        // Break 1 (1st Break)
        if ($attendance && $attendance->break1_out && $attendance->break1_in && 
            $attendance->break1_out !== '00:00:00' && $attendance->break1_in !== '00:00:00') {
            
            $b1out = Carbon::parse($dateStr . ' ' . $attendance->break1_out);
            $b1in = Carbon::parse($dateStr . ' ' . $attendance->break1_in);
            
            if ($b1in->lessThan($b1out)) $b1in->addDay();
            $actualOtherBreakMinutes += $b1in->diffInMinutes($b1out, true);
        }

        // Break 2 (Optional/Coffee)
        if ($attendance && $attendance->break2_out && $attendance->break2_in && 
            $attendance->break2_out !== '00:00:00' && $attendance->break2_in !== '00:00:00') {
            
            $b2out = Carbon::parse($dateStr . ' ' . $attendance->break2_out);
            $b2in = Carbon::parse($dateStr . ' ' . $attendance->break2_in);
            
            if ($b2in->lessThan($b2out)) $b2in->addDay();
            $actualOtherBreakMinutes += $b2in->diffInMinutes($b2out, true);
        }

        // TOTAL REGULAR HOURS WORKED
        // Standard logic: Shift Duration - Actual Breaks - Late - Undertime
        $scheduleDuration = $scheduleOut->diffInMinutes($scheduleIn, true);
        
        // Standard meal deduction logic (usually 60 or 120 mins)
        $standardMealDeduction = 0;
        if ($scheduleDuration >= 600) {
            $standardMealDeduction = 120;
        } elseif ($scheduleDuration >= 300) {
            $standardMealDeduction = 60;
        }

        // Total deduction is either actual lunch (if punched) or standard lunch, 
        // PLUS any other actual breaks punched.
        $totalBreakMinutes = ($actualLunchMinutes > 0 ? $actualLunchMinutes : $standardMealDeduction) + $actualOtherBreakMinutes;

        $expectedWorkMinutes = $scheduleDuration - $totalBreakMinutes;

        // Cap late and undertime to the schedule duration
        if ($lateMinutes > $scheduleDuration) $lateMinutes = $scheduleDuration;
        if ($undertimeMinutes > $scheduleDuration) $undertimeMinutes = $scheduleDuration;

        // Calculated worked minutes
        $workDurationMinutes = $expectedWorkMinutes - $lateMinutes - $undertimeMinutes;
        
        // If they worked more than the schedule, it's OT, not Regular
        // So Reg Hours is capped at expectedWorkMinutes
        if ($workDurationMinutes < 0) $workDurationMinutes = 0;
        if ($workDurationMinutes > $expectedWorkMinutes) $workDurationMinutes = $expectedWorkMinutes;
        
        $totalHours = round($workDurationMinutes / 60, 2);
        
        $overtimeHours = round($overtimeMinutes / 60, 2);

        return [
            'total_hours' => $totalHours,
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'overtime_hours' => $overtimeHours,
        ];
    }
}
