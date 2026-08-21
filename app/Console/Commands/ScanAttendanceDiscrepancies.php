<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScanAttendanceDiscrepancies extends Command
{
    /**
     * Nightly sweep (§2.3): flag past shifts with a start punch but no end
     * punch so they route through the correction workflow instead of
     * silently counting as full-day undertime at payroll lock.
     */
    protected $signature = 'attendance:scan-discrepancies';

    protected $description = 'Flag past attendances with missing clock-outs as pending attendance corrections';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $flagged = Attendance::query()
            ->whereDate('date', '<', $today)
            ->where(function ($q) {
                $q->whereNull('time_in')->orWhere('time_in', '!=', '00:00:00');
            })
            ->where(function ($q) {
                $q->whereNull('time_out')->orWhere('time_out', '=', '00:00:00');
            })
            ->whereDoesntHave('corrections', function ($q) {
                // Don't re-flag shifts that already have an open correction
                $q->whereIn('status', [
                    AttendanceCorrection::STATUS_PENDING_EMPLOYEE,
                    AttendanceCorrection::STATUS_PENDING_MANAGER,
                ]);
            })
            ->get();

        foreach ($flagged as $attendance) {
            AttendanceCorrection::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'flagged_reason' => 'missing_out',
                'status' => AttendanceCorrection::STATUS_PENDING_EMPLOYEE,
                'old_values' => [
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                ],
            ]);
        }

        $this->info("Flagged {$flagged->count()} attendance(s) with missing clock-outs.");

        return self::SUCCESS;
    }
}
