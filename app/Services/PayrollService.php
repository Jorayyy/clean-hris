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
        return DB::transaction(function () use ($payroll) {
            $query = Employee::where('status', 'active');
            
            if ($payroll->payroll_group_id) {
                $query->where('payroll_group_id', $payroll->payroll_group_id);
            }

            $employees = $query->get();
            $items = [];

            foreach ($employees as $employee) {
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

                // Deductions (fixed percentages)
                $sss = $basicPay * 0.05;
                $pagibig = $basicPay * 0.02;
                $philhealth = $basicPay * 0.03;
                $otherDeductions = 0;

                $netPay = ($basicPay + $overtimePay + $bonuses + $nightDiff) - ($sss + $pagibig + $philhealth + $otherDeductions);

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
    }

    public function calculateAttendanceStats($timeIn, $timeOut, $employeeId = null, $date = null)
    {
        $in = Carbon::parse($timeIn);
        $out = Carbon::parse($timeOut);

        // Ensure total hours is positive (handles out > in correctly)
        // If out is 17:00 and in is 08:00, diff is 9 hours
        $totalHours = $out->diffInMinutes($in) / 60;
        
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

        $lateMinutes = $in->greaterThan($scheduleIn) ? $in->diffInMinutes($scheduleIn) : 0;
        $undertimeMinutes = $out->lessThan($scheduleOut) ? $scheduleOut->diffInMinutes($out) : 0;

        return [
            'total_hours' => abs(round($totalHours, 2)),
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
        ];
    }
}
