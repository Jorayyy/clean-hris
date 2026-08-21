<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Schedule;
use Carbon\Carbon;

/**
 * §2.4: Shift-conflict validation — overlap, minimum rest between shifts,
 * and weekly hour cap. Returns human-readable violations as WARNINGS so
 * schedulers can override for legitimate reasons (emergency coverage);
 * overrides must be explicitly acknowledged and are audit-logged by the
 * controller.
 */
class ScheduleValidationService
{
    protected array $scheduleCache = [];

    /**
     * Validate a concrete shift for an employee on a concrete date.
     *
     * @return string[] list of violation messages (empty = no conflicts)
     */
    public function validateScheduleAssignment(Employee $employee, Carbon $date, string $timeIn, string $timeOut, ?int $ignoreScheduleId = null, int $minRestHours = 10, int $weeklyCapHours = 48): array
    {
        $violations = [];
        $newStart = Carbon::parse($date->toDateString().' '.$timeIn);
        $newEnd = Carbon::parse($date->toDateString().' '.$timeOut);
        if ($newEnd->lessThan($newStart)) {
            $newEnd->addDay();
        }

        // 1. Overlap with any existing schedule that date
        $existing = $this->resolvedSchedule($employee, $date);
        if ($existing && ! ($existing->is_rest_day ?? false)) {
            $isSelf = $ignoreScheduleId !== null && ($existing->id ?? null) === $ignoreScheduleId;
            if (! $isSelf && $this->overlaps(
                Carbon::parse($date->toDateString().' '.$existing->time_in),
                Carbon::parse($date->toDateString().' '.$existing->time_out),
                $newStart,
                $newEnd
            )) {
                $violations[] = sprintf(
                    '%s: Overlaps existing shift %s-%s on this date.',
                    $date->format('M d'),
                    substr((string) $existing->time_in, 0, 5),
                    substr((string) $existing->time_out, 0, 5)
                );
            }
        }

        // 2. Minimum rest between consecutive shifts
        $prevShiftEnd = $this->getPriorShiftEnd($employee, $date);
        if ($prevShiftEnd && $newStart->lessThan($prevShiftEnd->copy()->addHours($minRestHours))) {
            $violations[] = sprintf(
                '%s: Less than %d hours rest since previous shift ending %s.',
                $date->format('M d'),
                $minRestHours,
                $prevShiftEnd->format('M d H:i')
            );
        }

        // 3. Weekly hour cap
        $weekTotal = $this->getWeeklyScheduledHours($employee, $date, $ignoreScheduleId) + ($newStart->diffInMinutes($newEnd, true) / 60);
        if ($weekTotal > $weeklyCapHours) {
            $violations[] = sprintf(
                '%s: Exceeds %d-hour weekly cap (%.1fh scheduled including this shift).',
                $date->format('M d'),
                $weeklyCapHours,
                $weekTotal
            );
        }

        return $violations;
    }

    /**
     * Expand a recurring days[] pattern into the upcoming week's concrete
     * dates and validate each occurrence.
     *
     * @param  array  $dayNames  e.g. ['Monday', 'Wednesday']
     * @return string[] violations keyed as "EmployeeName — violation"
     */
    public function validateRecurringPattern(Employee $employee, array $dayNames, string $timeIn, string $timeOut, ?int $ignoreScheduleId = null): array
    {
        $violations = [];
        $monday = Carbon::now()->startOfWeek();

        foreach ($dayNames as $dayName) {
            for ($offset = 0; $offset < 7; $offset++) {
                $candidate = $monday->copy()->addDays($offset);
                if ($candidate->format('l') === $dayName) {
                    foreach ($this->validateScheduleAssignment($employee, $candidate, $timeIn, $timeOut, $ignoreScheduleId) as $violation) {
                        $violations[] = "{$employee->full_name} — {$violation}";
                    }
                    break;
                }
            }
        }

        return $violations;
    }

    protected function overlaps(Carbon $existingIn, Carbon $existingOut, Carbon $newStart, Carbon $newEnd): bool
    {
        if ($existingOut->lessThan($existingIn)) {
            $existingOut->addDay();
        }

        return $newStart->lessThan($existingOut) && $existingIn->lessThan($newEnd);
    }

    protected function getPriorShiftEnd(Employee $employee, Carbon $date): ?Carbon
    {
        $prev = $this->resolvedSchedule($employee, $date->copy()->subDay());
        if (! $prev || ($prev->is_rest_day ?? false) || ! $prev->time_in || ! $prev->time_out) {
            return null;
        }

        $end = Carbon::parse($date->copy()->subDay()->toDateString().' '.$prev->time_out);
        $start = Carbon::parse($date->copy()->subDay()->toDateString().' '.$prev->time_in);
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return $end;
    }

    protected function getWeeklyScheduledHours(Employee $employee, Carbon $date, ?int $ignoreScheduleId = null): float
    {
        $monday = $date->copy()->startOfWeek();
        $totalMinutes = 0;

        for ($offset = 0; $offset < 7; $offset++) {
            $day = $monday->copy()->addDays($offset);
            $sched = $this->resolvedSchedule($employee, $day);

            if ($sched && ! ($sched->is_rest_day ?? false) && $sched->time_in && $sched->time_out) {
                $isSelf = $ignoreScheduleId !== null && ($sched->id ?? null) === $ignoreScheduleId;
                if (! $isSelf) {
                    $in = Carbon::parse($day->toDateString().' '.$sched->time_in);
                    $out = Carbon::parse($day->toDateString().' '.$sched->time_out);
                    if ($out->lessThan($in)) {
                        $out->addDay();
                    }
                    $totalMinutes += $in->diffInMinutes($out, true);
                }
            }
        }

        return $totalMinutes / 60;
    }

    protected function resolvedSchedule(Employee $employee, Carbon $date)
    {
        $key = $employee->id.':'.$date->toDateString();

        if (! array_key_exists($key, $this->scheduleCache)) {
            $this->scheduleCache[$key] = $employee->getScheduleForDate($date->toDateString());
        }

        return $this->scheduleCache[$key];
    }
}
