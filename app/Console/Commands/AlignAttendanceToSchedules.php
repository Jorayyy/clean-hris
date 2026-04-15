<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AlignAttendanceToSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:align {--start= : Start date (YYYY-MM-DD)} {--end= : End date (YYYY-MM-DD)} {--dry-run : Run without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Align existing attendance records to follow employee current schedules for a given date range';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start');
        $endDate = $this->option('end');

        if (!$startDate || !$endDate) {
            $this->error('Please provide both --start and --end dates.');
            return 1;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("DRY RUN: No changes will be saved.");
        }

        $this->info("Aligning attendance from {$start->toDateString()} to {$end->toDateString()}...");

        $employees = Employee::all();
        $totalUpdated = 0;

        foreach ($employees as $employee) {
            $schedule = $employee->active_schedule;
            if (!$schedule) {
                // $this->warn("No schedule found for employee: {$employee->full_name} ({$employee->employee_id})");
                continue;
            }

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            foreach ($attendances as $attendance) {
                $dayName = Carbon::parse($attendance->date)->format('l');

                // Check if the employee is scheduled for this day
                if (is_array($schedule->days) && in_array($dayName, $schedule->days)) {
                    $newTimeIn = $schedule->time_in;
                    $newTimeOut = $schedule->time_out;

                    // Parse times for total_hours calculation
                    $in = Carbon::parse($attendance->date . ' ' . $newTimeIn);
                    $out = Carbon::parse($attendance->date . ' ' . $newTimeOut);
                    
                    if ($out->lessThan($in)) {
                        $out->addDay();
                    }

                    $totalHours = abs($out->diffInMinutes($in) / 60);

                    $this->line("Updating {$employee->full_name} on {$attendance->date}: {$newTimeIn} - {$newTimeOut} ({$totalHours} hrs)");

                    if (!$dryRun) {
                        $attendance->update([
                            'time_in' => $newTimeIn,
                            'time_out' => $newTimeOut,
                            'total_hours' => $totalHours,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                        ]);
                    }
                    $totalUpdated++;
                } else {
                    // $this->line("Skipping {$employee->full_name} on {$attendance->date} (Not a scheduled day: {$dayName})");
                }
            }
        }

        $this->info("Finished. Total records " . ($dryRun ? "found: " : "updated: ") . $totalUpdated);
        return 0;
    }
}
