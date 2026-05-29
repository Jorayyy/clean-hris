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
    public function computePayroll(Payroll $payroll)
    {
        try {
            return DB::transaction(function () use ($payroll) {
            // Allow processing if draft or processing
            if (!in_array($payroll->status, ['draft', 'processing'])) {
                return false; 
            }

            // Set to processing
            $payroll->update(['status' => 'processing']);

            $query = Employee::where('status', 'active');
                
                if ($payroll->payroll_group_id) {
                    $query->where('payroll_group_id', $payroll->payroll_group_id);
                } elseif ($payroll->employee_id) {
                    $query->where('id', $payroll->employee_id);
                }

                $employees = $query->get();
                $items = [];

                foreach ($employees as $employee) {
                    // Skip if item already exists to avoid duplicates
                    if (PayrollItem::where('payroll_id', $payroll->id)->where('employee_id', $employee->id)->exists()) {
                        continue;
                    }

                    // Check for finalized DTR
                    $dtr = Dtr::where('employee_id', $employee->id)
                        ->where('start_date', $payroll->start_date)
                        ->where('end_date', $payroll->end_date)
                        ->where('status', 'finalized')
                        ->first();

                    // If NO finalized DTR, we skip them from the automated batch.
                    // This prevents unverified attendance from being paid out.
                    if (!$dtr) {
                        continue;
                    }

                    $attendances = Attendance::where('employee_id', $employee->id)
                        ->whereBetween('date', [$payroll->start_date, $payroll->end_date])
                        ->get();

                    $totalDays = $attendances->count();
                    $totalHours = $attendances->sum('total_hours');
                    
                    $dailyRate = $employee->daily_rate;
                    $hourlyRate = $dailyRate / 8;

                    // Use DTR stats if available for better accuracy (handle lates/undertime if model has it)
                    $basicPay = ($dtr->total_regular_hours / 8) * $dailyRate;
                    
                    // Logic for OT from DTR
                    $overtimePay = $dtr->total_overtime_hours * $hourlyRate * 1.25; 


                    // Bonuses & Night Diff (simplified as requested)
                    $bonuses = ($totalDays >= 5) ? 500 : 0; // Perfect attendance bonus
                    $nightDiff = 0; // Simplified for this implementation

                    // Fetch dynamic rates from settings
                    $settings = \App\Models\AppSetting::first();
                    $sssRate = $settings->sss_rate ?? 0.05;
                    $pagibigRate = $settings->pagibig_rate ?? 0.02;
                    $philhealthRate = $settings->philhealth_rate ?? 0.03;

                    // Deductions logic
                    $deductions = [];

                    // Late/UT Deductions
                    if ($dtr->total_late_minutes > 0) {
                        $deductions[] = [
                            'type' => 'LATE', 
                            'amount' => floor(($dtr->total_late_minutes / 60) * $hourlyRate)
                        ];
                    }
                    if ($dtr->total_undertime_minutes > 0) {
                        $deductions[] = [
                            'type' => 'UT', 
                            'amount' => floor(($dtr->total_undertime_minutes / 60) * $hourlyRate)
                        ];
                    }

                    foreach (['sss', 'pagibig', 'philhealth'] as $type) {
                        $rate = $settings->{$type . '_rate'} ?? 0.05;
                        $amt = floor($basicPay * $rate);
                        if ($amt > 0) {
                            $deductions[] = ['type' => strtoupper($type), 'amount' => $amt];
                        }
                    }

                    $totalDeductions = array_sum(array_column($deductions, 'amount'));
                    $netPay = ($basicPay + $overtimePay + $bonuses + $nightDiff) - $totalDeductions;

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
            // If it's a rest day, all hours are technically Overtime (or Rest Day pay)
            return [
                'total_hours' => 0, 
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => round($totalStayMinutes / 60, 2),
            ];
        }

        $scheduleIn = Carbon::parse($dateStr . ' ' . $scheduleInTime);
        $scheduleOut = Carbon::parse($dateStr . ' ' . $scheduleOutTime);

        // Handle Night Shift schedule
        if ($scheduleOut->lessThan($scheduleIn)) {
            $scheduleOut->addDay();
        }

        // LATE CALCULATION
        $lateMinutes = 0;
        // Only count as late if they timed in AFTER the schedule
        // AND before the schedule ends
        if ($in->greaterThan($scheduleIn) && $in->lessThan($scheduleOut)) {
             $lateMinutes = $scheduleIn->diffInMinutes($in, true);
        }
        
        // UNDERTIME CALCULATION
        $undertimeMinutes = 0;
        if ($out) {
            if ($out->lessThan($scheduleOut)) {
                // If they timed out before the shift ends
                $undertimeMinutes = $out->diffInMinutes($scheduleOut, true);
            }
        } else {
            // Missing out punch - assume full undertime for the shift
            $undertimeMinutes = $scheduleOut->diffInMinutes($scheduleIn, true);
        }

        // OVERTIME CALCULATION (Minutes beyond schedule out)
        $overtimeMinutes = 0;
        if ($out && $out->greaterThan($scheduleOut)) {
             $overtimeMinutes = $scheduleOut->diffInMinutes($out, true);
        }

        // TOTAL REGULAR HOURS WORKED
        // Standard logic: Shift Duration - Lunch - Late - Undertime
        $scheduleDuration = $scheduleOut->diffInMinutes($scheduleIn, true);
        
        // Deduct 1 hour lunch if shift is at least 5 hours
        $mealBreak = $scheduleDuration >= 300 ? 60 : 0;
        $expectedWorkMinutes = $scheduleDuration - $mealBreak;

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
