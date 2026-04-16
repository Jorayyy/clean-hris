<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
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
                }

                $employees = $query->get();
                $items = [];

                foreach ($employees as $employee) {
                    // Skip if item already exists to avoid duplicates
                    if (PayrollItem::where('payroll_id', $payroll->id)->where('employee_id', $employee->id)->exists()) {
                        continue;
                    }

                    $attendances = Attendance::where('employee_id', $employee->id)
                        ->whereBetween('date', [$payroll->start_date, $payroll->end_date])
                        ->get();

                    $totalDays = $attendances->count();
                    $totalHours = $attendances->sum('total_hours');
                    
                    $dailyRate = $employee->daily_rate;
                    $hourlyRate = $dailyRate / 8;

                    $basicPay = $totalDays * $dailyRate;
                    
                    // Logic for OT (hours over 8 per day)
                    $overtimePay = 0;
                    foreach ($attendances as $attendance) {
                        if ($attendance->total_hours > 8) {
                            $overtimePay += ($attendance->total_hours - 8) * $hourlyRate * 1.25; // 1.25x for OT
                        }
                    }

                    // Bonuses & Night Diff (simplified as requested)
                    $bonuses = ($totalDays >= 5) ? 500 : 0; // Perfect attendance bonus
                    $nightDiff = 0; // Simplified for this implementation

                    // Fetch dynamic rates from settings
                    $settings = \App\Models\AppSetting::first();
                    $sssRate = $settings->sss_rate ?? 0.05;
                    $pagibigRate = $settings->pagibig_rate ?? 0.02;
                    $philhealthRate = $settings->philhealth_rate ?? 0.03;

                    // Deductions
                    $sss = floor($basicPay * $sssRate);
                    $pagibig = floor($basicPay * $pagibigRate);
                    $philhealth = floor($basicPay * $philhealthRate);
                    $otherDeductions = 0;

                    $netPay = ($basicPay + $overtimePay + $bonuses + $nightDiff) - ($sss + $pagibig + $philhealth + $otherDeductions);

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
                            'deductions_sss' => $sss,
                            'deductions_pagibig' => $pagibig,
                            'deductions_philhealth' => $philhealth,
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

        // Try to get actual schedule if employee provided
        if ($employeeId) {
            $employee = \App\Models\Employee::find($employeeId);
            $schedule = $employee?->active_schedule;
            if ($schedule) {
                // If specific date provided, check if scheduled for that day
                $dayName = $date ? Carbon::parse($date)->format('l') : null;
                if (!$dayName || (is_array($schedule->days) && in_array($dayName, $schedule->days))) {
                    $scheduleInTime = $schedule->time_in;
                    $scheduleOutTime = $schedule->time_out;
                }
            }
        }

        $datePrefix = $date ?? $in->toDateString();
        $scheduleIn = Carbon::parse($datePrefix . ' ' . $scheduleInTime);
        $scheduleOut = Carbon::parse($datePrefix . ' ' . $scheduleOutTime);

        // Handle Night Shift: If schedule OUT is before schedule IN (e.g., 20:00 to 06:00)
        if ($scheduleOut->lessThan($scheduleIn)) {
            // Check if actual 'In' punch is before midnight or after
            // If we are punching at 3:53 PM for a 9:00 PM shift, we are in the 'Before Midnight' phase
            // If the schedule ends at 06:00 AM, that's already the next calendar day
            $scheduleOut->addDay();
        }

        // Similarly for the actual Out punch if it's before the In punch
        if (!$isNoPunchOut && $out->lessThan($in)) {
            $out->addDay();
            $totalHours = $out->diffInMinutes($in) / 60;
        }

        // LATE CALCULATION REFINEMENT
        // If the punch-in is many hours before the schedule (like 3 PM for a 9 PM shift), 
        // it shouldn't be counted as 'late' from the previous day's schedule.
        if ($in->greaterThan($scheduleIn)) {
             $lateMinutes = $scheduleIn->diffInMinutes($in);
             // If late minutes is more than 12 hours, we assume it's actually an early punch for a night shift
             if ($lateMinutes > 720) $lateMinutes = 0;
        } else {
             $lateMinutes = 0;
        }
        
        // Only calculate undertime if a REAL Out punch happened
        $isActualOut = $timeOut && $timeOut !== '00:00:00';
        $undertimeMinutes = ($isActualOut && $out->lessThan($scheduleOut)) ? $out->diffInMinutes($scheduleOut) : 0;

        return [
            'total_hours' => abs(round($totalHours, 2)),
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
        ];
    }
}
