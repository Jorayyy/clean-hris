<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\ContributionBracket;
use App\Models\Dtr;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Per-run memo of schedule resolution (employee_id:date -> is_rest_day).
     * Kills the N+1 on Employee::getScheduleForDate() during batch runs.
     */
    protected array $scheduleCache = [];

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

        // §2.3: open corrections block finalization, same pattern as missing/pending DTRs.
        $openCorrections = AttendanceCorrection::whereIn('employee_id', $employees->pluck('id'))
            ->whereIn('status', [AttendanceCorrection::STATUS_PENDING_EMPLOYEE, AttendanceCorrection::STATUS_PENDING_MANAGER])
            ->get()
            ->groupBy('employee_id');

        $results = [
            'total_employees' => $employees->count(),
            'missing_dtr' => [],
            'pending_dtr' => [],
            'open_corrections' => [],
            'with_absences' => [],
            'can_process' => true,
        ];

        foreach ($employees as $employee) {
            $dtr = $dtrs->get($employee->id);

            if (! $dtr) {
                $results['missing_dtr'][] = $employee->full_name;
                $results['can_process'] = false;
            } elseif ($dtr->status !== 'finalized') {
                $results['pending_dtr'][] = [
                    'name' => $employee->full_name,
                    'status' => $dtr->status,
                ];
                $results['can_process'] = false;
            }

            if ($openCorrections->has($employee->id)) {
                $results['open_corrections'][] = [
                    'name' => $employee->full_name,
                    'count' => $openCorrections->get($employee->id)->count(),
                ];
                $results['can_process'] = false;
            }

            if ($dtr && $dtr->total_absent_days > 0) {
                $results['with_absences'][] = [
                    'name' => $employee->full_name,
                    'days' => $dtr->total_absent_days,
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
                $this->scheduleCache = [];

                // ATOMIC LOCK: Use database-level lock to prevent double-processing
                $affected = DB::table('payrolls')
                    ->where('id', $payroll->id)
                    ->whereIn('status', ['draft', 'pending'])
                    ->update([
                        'status' => 'processing',
                        'updated_at' => now(),
                    ]);

                if (! $affected && $payroll->status !== 'processing') {
                    return false;
                }

                $query = Employee::where('status', 'active');

                if ($payroll->payroll_group_id) {
                    $query->where('payroll_group_id', $payroll->payroll_group_id);
                } elseif ($payroll->employee_id) {
                    $query->where('id', $payroll->employee_id);
                }

                $employees = $query->get();
                $employeeIds = $employees->pluck('id');

                // PERFORMANCE: Eager load DTRs and attendances in ONE query each,
                // eliminating the per-employee-per-day N+1 (audit finding #8).
                $dtrs = Dtr::whereIn('employee_id', $employeeIds)
                    ->whereDate('start_date', $payroll->start_date)
                    ->whereDate('end_date', $payroll->end_date)
                    ->get()
                    ->keyBy('employee_id');

                $attendancesByEmployee = Attendance::whereIn('employee_id', $employeeIds)
                    ->whereBetween('date', [$payroll->start_date, $payroll->end_date])
                    ->orderBy('date')
                    ->get()
                    ->groupBy('employee_id');

                // §2.1: Holidays resolved ONCE per period, applied per attendance
                // date (a mid-period holiday affects only that day's pay).
                $holidays = Holiday::whereBetween('date', [$payroll->start_date, $payroll->end_date])
                    ->get()
                    ->keyBy(fn ($holiday) => Carbon::parse($holiday->date)->toDateString());

                // CONSISTENCY: Fetch settings once before the loop.
                $settings = AppSetting::first();
                $nightDiffRate = (float) ($settings->night_diff_rate ?? 0.10);
                $legacyRates = [
                    'sss' => (float) ($settings->sss_rate ?? 0.05),
                    'pagibig' => (float) ($settings->pagibig_rate ?? 0.02),
                    'philhealth' => (float) ($settings->philhealth_rate ?? 0.03),
                ];

                // §2.2: Bracket tables loaded once; empty tables degrade to the
                // legacy flat rates so unseeded installs keep computing.
                $bracketsByType = ContributionBracket::all()->groupBy('type');

                $items = [];

                foreach ($employees as $employee) {
                    $dtr = $dtrs->get($employee->id);

                    $dailyRate = $employee->daily_rate;
                    $hourlyRate = $dailyRate / 8;

                    $attendances = $attendancesByEmployee->get($employee->id, collect());

                    $basicPay = 0;
                    $overtimePay = 0;
                    $nightDiff = 0;

                    if ($attendances->isNotEmpty()) {
                        // §2.1: Resolve multipliers PER DATE and sum across the
                        // period, instead of one flat rate over the whole DTR.
                        foreach ($attendances as $attendance) {
                            $dateStr = Carbon::parse($attendance->date)->toDateString();
                            $multipliers = $this->resolvePayMultiplier(
                                $dateStr,
                                $this->isRestDay($employee, $dateStr),
                                $holidays->get($dateStr)
                            );

                            $dayHours = (float) ($attendance->total_hours ?? 0);
                            $otHours = (float) ($attendance->overtime_hours ?? 0);

                            if ($dayHours > 0) {
                                $basicPay += ($dayHours / 8) * $dailyRate * $multipliers['basic'];
                            }

                            if ($otHours > 0) {
                                $overtimePay += $otHours * $hourlyRate * $multipliers['ot'];
                            }

                            // §2.1: Night differential from actual punch overlap
                            // with the 22:00-06:00 window (no longer hardcoded 0).
                            if ($attendance->time_in && $attendance->time_in !== '00:00:00'
                                && $attendance->time_out && $attendance->time_out !== '00:00:00') {
                                $in = Carbon::parse($dateStr.' '.$attendance->time_in);
                                $out = Carbon::parse($dateStr.' '.$attendance->time_out);
                                if ($out->lessThan($in)) {
                                    $out->addDay();
                                }
                                $ndMinutes = $this->computeNightDiffMinutes($in, $out);
                                if ($ndMinutes > 0) {
                                    $nightDiff += ($ndMinutes / 60) * $hourlyRate * $nightDiffRate;
                                }
                            }
                        }

                        $basicPay = round($basicPay, 2);
                        $overtimePay = round($overtimePay, 2);
                        $nightDiff = round($nightDiff, 2);
                    } elseif ($dtr) {
                        // FALLBACK: No attendance rows for this employee — keep the
                        // legacy DTR-driven computation rather than paying zero.
                        $basicPay = ($dtr->total_regular_hours / 8) * $dailyRate;
                        $overtimePay = ($dtr->is_ot_authorized ?? false)
                            ? round($dtr->total_overtime_hours * $hourlyRate * 1.25, 2)
                            : 0;
                    }

                    $totalDays = $attendances->count();
                    $totalHours = round((float) $attendances->sum('total_hours'), 2);

                    // Bonuses: Only award "Perfect Attendance" bonus if they actually
                    // worked at least 5 days, have no absences, AND have actual hours rendered.
                    $hasAbsences = $dtr ? ($dtr->total_absent_days > 0) : true;
                    $bonuses = ($totalDays >= 5 && ! $hasAbsences && $totalHours > 0) ? 500 : 0;

                    $deductions = [];
                    $sssAmt = 0;
                    $pagibigAmt = 0;
                    $philhealthAmt = 0;
                    $withholdingAmt = 0;
                    $otherDeductions = 0;

                    // Late/UT Deductions
                    // Skip attendance deductions if BASIC PAY is 0 (fully absent)
                    // to prevent "double-penalizing".
                    if ($basicPay > 0 && $dtr) {
                        if ($dtr->total_late_minutes > 0) {
                            $amt = min($basicPay * 0.5, floor(($dtr->total_late_minutes / 60) * $hourlyRate));
                            $deductions[] = ['type' => 'LATE', 'amount' => $amt];
                            $otherDeductions += $amt;
                        }
                        if ($dtr->total_undertime_minutes > 0) {
                            $amt = min($basicPay * 0.5, floor(($dtr->total_undertime_minutes / 60) * $hourlyRate));
                            $deductions[] = ['type' => 'UT', 'amount' => $amt];
                            $otherDeductions += $amt;
                        }
                    }

                    // §2.2: Statutory deductions from bracket tables, falling back
                    // to flat settings rates when a table has no rows.
                    foreach (['sss', 'pagibig', 'philhealth'] as $type) {
                        $amt = $this->statutoryDeduction($bracketsByType->get($type, collect()), $type, $basicPay, $legacyRates[$type]);

                        if ($amt > 0) {
                            $deductions[] = ['type' => strtoupper($type), 'amount' => $amt];
                        }

                        if ($type === 'sss') {
                            $sssAmt = $amt;
                        }
                        if ($type === 'pagibig') {
                            $pagibigAmt = $amt;
                        }
                        if ($type === 'philhealth') {
                            $philhealthAmt = $amt;
                        }
                    }

                    $grossPay = $basicPay + $overtimePay + $bonuses + $nightDiff;

                    // §2.2: Progressive withholding tax on gross minus statutory
                    // contributions, against the semi-monthly BIR table.
                    $taxablePay = max(0, $grossPay - $sssAmt - $pagibigAmt - $philhealthAmt);
                    $withholdingAmt = $this->statutoryDeduction($bracketsByType->get('withholding', collect()), 'withholding', $taxablePay, 0);
                    if ($withholdingAmt > 0) {
                        $deductions[] = ['type' => 'WTAX', 'amount' => $withholdingAmt];
                    }

                    $totalDeductions = array_sum(array_column($deductions, 'amount'));

                    // ENSURE NO NEGATIVE NET PAY: Cap at zero to handle cases with zero basic but fixed deductions
                    $netPay = max(0, $grossPay - $totalDeductions);

                    // IDEMPOTENCY: updateOrCreate backed by the DB unique constraint
                    // added in the idempotency migration (race-safe under retries).
                    $items[] = PayrollItem::updateOrCreate(
                        ['payroll_id' => $payroll->id, 'employee_id' => $employee->id],
                        [
                            'dtr_id' => $dtr?->id,
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
                            'withholding_tax' => $withholdingAmt,
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

    /**
     * §2.1: Multi-tier OT/holiday multiplier resolution. The Holiday model
     * finally participates in payroll math; 'local' holidays are treated as
     * special non-working days.
     */
    public function resolvePayMultiplier(string $date, bool $isRestDay, ?Holiday $holiday = null): array
    {
        $holiday = $holiday ?? Holiday::whereDate('date', $date)->first();
        $type = $holiday?->type;

        return match (true) {
            $type === 'regular' && $isRestDay => ['basic' => 2.0, 'ot' => 2.6],
            $type === 'regular' => ['basic' => 2.0, 'ot' => 2.6],
            in_array($type, ['special', 'local'], true) && $isRestDay => ['basic' => 1.5, 'ot' => 1.69],
            in_array($type, ['special', 'local'], true) => ['basic' => 1.3, 'ot' => 1.69],
            $isRestDay => ['basic' => 1.3, 'ot' => 1.69],
            default => ['basic' => 1.0, 'ot' => 1.25],
        };
    }

    /**
     * §2.1: Night differential as overlap minutes between the shift and the
     * 22:00-06:00 window (window spans midnight, mirroring the night-shift
     * handling in calculateAttendanceStats).
     */
    public function computeNightDiffMinutes(Carbon $in, Carbon $out, string $nightStart = '22:00', string $nightEnd = '06:00'): int
    {
        $windowStart = Carbon::parse($in->toDateString() . ' ' . $nightStart);
        $windowEnd = Carbon::parse($in->toDateString() . ' ' . $nightEnd)->addDay();

        $overlapStart = $in->greaterThan($windowStart) ? $in : $windowStart;
        $overlapEnd = $out->lessThan($windowEnd) ? $out : $windowEnd;

        // No intersection (shift entirely outside the night window)
        if ($overlapEnd->lessThanOrEqualTo($overlapStart)) {
            return 0;
        }

        return (int) $overlapStart->diffInMinutes($overlapEnd);
    }

    /**
     * §2.2: Bracket-table deduction against a preloaded group, with legacy
     * flat-rate fallback when the table is empty.
     */
    protected function statutoryDeduction(Collection $brackets, string $type, float $basePay, float $fallbackRate): float
    {
        if ($brackets->isEmpty()) {
            return round($basePay * $fallbackRate, 2);
        }

        $bracket = $brackets->first(
            fn ($b) => $b->min_salary <= $basePay
                && (is_null($b->max_salary) || $b->max_salary >= $basePay)
        );

        return $bracket ? $bracket->compute($basePay) : 0.0;
    }

    /**
     * Memoized rest-day detection so getScheduleForDate() runs at most once
     * per employee per date during a batch run.
     */
    protected function isRestDay(Employee $employee, string $dateStr): bool
    {
        $key = $employee->id.':'.$dateStr;

        if (! array_key_exists($key, $this->scheduleCache)) {
            $schedule = $employee->getScheduleForDate($dateStr);
            $this->scheduleCache[$key] = (bool) ($schedule->is_rest_day ?? false);
        }

        return $this->scheduleCache[$key];
    }

    public function calculateAttendanceStats($timeIn, $timeOut, $employeeId = null, $date = null, ?Attendance $attendance = null, ?Employee $employee = null)
    {
        // Treat 00:00:00 as no punch/invalid for calculations
        $isNoPunchIn = $timeIn === '00:00:00' || ! $timeIn;
        $isNoPunchOut = $timeOut === '00:00:00' || ! $timeOut;

        $dateStr = $date ?? Carbon::now()->toDateString();

        if ($isNoPunchIn) {
            return [
                'total_hours' => 0,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
            ];
        }

        // §2.1 fix: Reuse the already-loaded Attendance model instead of
        // re-querying it (and ot_authorized) up to three times per call.
        if (! $attendance && $employeeId && $date) {
            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $date)
                ->first();
        }

        $in = Carbon::parse($dateStr.' '.$timeIn);
        $out = $isNoPunchOut ? null : Carbon::parse($dateStr.' '.$timeOut);

        // Default fallbacks
        $scheduleInTime = '08:00:00';
        $scheduleOutTime = '17:00:00';
        $isRestDay = false;
        $isNoSchedule = false;

        // Try to get actual schedule if employee provided
        if ($employeeId) {
            $employee = $employee ?? Employee::find($employeeId);
            if ($employee) {
                $sched = $employee->getScheduleForDate($dateStr);
                if ($sched) {
                    $scheduleInTime = $sched->time_in;
                    $scheduleOutTime = $sched->time_out;
                    if ($sched->is_rest_day) {
                        $isRestDay = true;
                    }
                } else {
                    $isNoSchedule = true;
                }
            }
        }

        // Handle actual punches crossing midnight
        if ($out && $out->lessThan($in)) {
            $out->addDay();
        }

        if ($isRestDay || $isNoSchedule) {
            $totalStayMinutes = ! $out ? 0 : $out->diffInMinutes($in, true);

            // Check if OT is authorized even for rest days — reused model,
            // no extra query.
            $isAuthorized = (bool) ($attendance->ot_authorized ?? false);

            // If not authorized, rest day work doesn't count as OT (might count as regular? usually it's OT)
            // But we respect "don't calculate OT unless official"
            return [
                'total_hours' => 0,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => $isAuthorized ? round($totalStayMinutes / 60, 2) : 0,
            ];
        }

        $scheduleIn = Carbon::parse($dateStr.' '.$scheduleInTime);
        $scheduleOut = Carbon::parse($dateStr.' '.$scheduleOutTime);

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

        // OT is only calculated if "Official" (ot_authorized is true) —
        // reused from the fetched model, no redundant EXISTS() query.
        $isAuthorized = (bool) ($attendance->ot_authorized ?? false);

        if ($isAuthorized && $out && $out->greaterThan($scheduleOut)) {
            $overtimeMinutes = (int) $scheduleOut->diffInMinutes($out, true);
        }

        // Calculate actual break durations if they exist
        $actualLunchMinutes = 0;

        // Lunch Break
        if ($attendance && $attendance->lunch_out && $attendance->lunch_in &&
            $attendance->lunch_out !== '00:00:00' && $attendance->lunch_in !== '00:00:00') {

            $lout = Carbon::parse($dateStr.' '.$attendance->lunch_out);
            $lin = Carbon::parse($dateStr.' '.$attendance->lunch_in);

            if ($lin->lessThan($lout)) {
                $lin->addDay();
            }
            $actualLunchMinutes = $lin->diffInMinutes($lout, true);
        }

        $actualOtherBreakMinutes = 0;

        // Break 1 (1st Break)
        if ($attendance && $attendance->break1_out && $attendance->break1_in &&
            $attendance->break1_out !== '00:00:00' && $attendance->break1_in !== '00:00:00') {

            $b1out = Carbon::parse($dateStr.' '.$attendance->break1_out);
            $b1in = Carbon::parse($dateStr.' '.$attendance->break1_in);

            if ($b1in->lessThan($b1out)) {
                $b1in->addDay();
            }
            $actualOtherBreakMinutes += $b1in->diffInMinutes($b1out, true);
        }

        // Break 2 (Optional/Coffee)
        if ($attendance && $attendance->break2_out && $attendance->break2_in &&
            $attendance->break2_out !== '00:00:00' && $attendance->break2_in !== '00:00:00') {

            $b2out = Carbon::parse($dateStr.' '.$attendance->break2_out);
            $b2in = Carbon::parse($dateStr.' '.$attendance->break2_in);

            if ($b2in->lessThan($b2out)) {
                $b2in->addDay();
            }
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
        if ($lateMinutes > $scheduleDuration) {
            $lateMinutes = $scheduleDuration;
        }
        if ($undertimeMinutes > $scheduleDuration) {
            $undertimeMinutes = $scheduleDuration;
        }

        // Calculated worked minutes
        $workDurationMinutes = $expectedWorkMinutes - $lateMinutes - $undertimeMinutes;

        // If they worked more than the schedule, it's OT, not Regular
        // So Reg Hours is capped at expectedWorkMinutes
        if ($workDurationMinutes < 0) {
            $workDurationMinutes = 0;
        }
        if ($workDurationMinutes > $expectedWorkMinutes) {
            $workDurationMinutes = $expectedWorkMinutes;
        }

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
