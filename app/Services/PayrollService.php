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
        $in = Carbon::parse($timeIn);
        $out = Carbon::parse($timeOut);

        // Treat 00:00:00 as no punch/invalid for calculations
        $isNoPunchIn = $timeIn === '00:00:00' || !$timeIn;
        $isNoPunchOut = $timeOut === '00:00:00' || !$timeOut;

        if ($isNoPunchIn) {
            return [
                'total_hours' => 0,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
            ];
        }

        // Ensure total hours is positive (handles out > in correctly)
        $totalHours = $isNoPunchOut ? 0 : ($out->diffInMinutes($in) / 60);
        
        // Default fallbacks
        $scheduleInTime = '08:00:00';
        $scheduleOutTime = '17:00:00';

        $dateStr = $date ?? $in->toDateString();

        // Try to get actual schedule if employee provided
        if ($employeeId) {
            $employee = \App\Models\Employee::find($employeeId);
            $schedule = $employee?->active_schedule;
            if ($schedule) {
                // If specific date provided, check if scheduled for that day
                $dayName = Carbon::parse($dateStr)->format('l');
                if (!$dayName || (is_array($schedule->days) && in_array($dayName, $schedule->days))) {
                    $scheduleInTime = $schedule->time_in;
                    $scheduleOutTime = $schedule->time_out;
                }
            }
        }

        $scheduleIn = Carbon::parse($dateStr . ' ' . $scheduleInTime);
        $scheduleOut = Carbon::parse($dateStr . ' ' . $scheduleOutTime);

        // Re-parse in/out with the correct date to ensure comparisons work
        $actualIn = Carbon::parse($dateStr . ' ' . $timeIn);
        $actualOut = $isNoPunchOut ? null : Carbon::parse($dateStr . ' ' . $timeOut);

        // Handle Night Shift: If schedule OUT is before schedule IN (e.g., 20:00 to 06:00)
        if ($scheduleOut->lessThan($scheduleIn)) {
            $scheduleOut->addDay();
        }

        // Handle actual out if it crossed midnight
        if ($actualOut && $actualOut->lessThan($actualIn)) {
            $actualOut->addDay();
        }

        // LATE CALCULATION
        if ($actualIn->greaterThan($scheduleIn)) {
             $lateMinutes = $scheduleIn->diffInMinutes($actualIn);
             if ($lateMinutes > 720) $lateMinutes = 0;
        } else {
             $lateMinutes = 0;
        }
        
        // UNDERTIME CALCULATION
        $undertimeMinutes = ($actualOut && $actualOut->lessThan($scheduleOut)) ? $actualOut->diffInMinutes($scheduleOut) : 0;

        // OVERTIME CALCULATION (Minutes beyond schedule out)
        $overtimeMinutes = ($actualOut && $actualOut->greaterThan($scheduleOut)) ? $scheduleOut->diffInMinutes($actualOut) : 0;
        $overtimeHours = round($overtimeMinutes / 60, 2);

        return [
            'total_hours' => abs(round($totalHours, 2)),
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'overtime_hours' => $overtimeHours,
        ];
    }
}
