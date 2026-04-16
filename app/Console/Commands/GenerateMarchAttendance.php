<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GenerateMarchAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-march {--start=2026-03-01} {--end=2026-03-31} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate realistic attendance records for existing employees for March 2026';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start');
        $endDate = $this->option('end');
        $dryRun = $this->option('dry-run');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $period = CarbonPeriod::create($start, $end);
        $employees = Employee::where('status', 'active')->get();
        
        if ($employees->isEmpty()) {
            $employees = Employee::all();
        }

        $count = 0;
        $holidays = \App\Models\Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->toArray();

        foreach ($employees as $employee) {
            $schedule = $employee->active_schedule;
            if (!$schedule) {
                $this->warn("No schedule for {$employee->full_name}");
                continue;
            }

            foreach ($period as $date) {
                $dateString = $date->toDateString();
                $dayName = $date->format('l');

                // Skip if it's a holiday
                if (in_array($dateString, $holidays)) {
                    continue;
                }

                if (is_array($schedule->days) && in_array($dayName, $schedule->days)) {
                    // Check if already exists
                    $exists = Attendance::where('employee_id', $employee->id)
                        ->where('date', $dateString)
                        ->exists();

                    if (!$exists) {
                        // Generate realistic times with random variation (-10 to +10 minutes)
                        $baseIn = Carbon::parse($dateString . ' ' . ($schedule->time_in ?? '08:00:00'));
                        $baseOut = Carbon::parse($dateString . ' ' . ($schedule->time_out ?? '17:00:00'));

                        $timeInVariation = rand(-10, 5); // Slightly more likely to be early or on time
                        $timeOutVariation = rand(0, 15); // Slightly more likely to stay late

                        $realIn = $baseIn->copy()->addMinutes($timeInVariation);
                        $realOut = $baseOut->copy()->addMinutes($timeOutVariation);

                        if ($realOut->lessThan($realIn)) {
                            $realOut->addDay();
                        }

                        $totalHours = round($realOut->diffInMinutes($realIn) / 60, 2);
                        
                        // Calculate late minutes if any
                        $lateMinutes = 0;
                        if ($realIn->greaterThan($baseIn)) {
                            $lateMinutes = $realIn->diffInMinutes($baseIn);
                        }

                        if ($dryRun) {
                            $this->line("[DRY RUN] Would generate for {$employee->full_name} on {$dateString}: {$realIn->format('H:i')} - {$realOut->format('H:i')} ({$totalHours} hrs)");
                        } else {
                            Attendance::create([
                                'employee_id' => $employee->id,
                                'date' => $dateString,
                                'time_in' => $realIn->format('H:i:s'),
                                'time_out' => $realOut->format('H:i:s'),
                                'total_hours' => $totalHours,
                                'late_minutes' => $lateMinutes,
                                'undertime_minutes' => 0,
                            ]);
                            $this->line("Generated for {$employee->full_name} on {$dateString}");
                        }
                        $count++;
                    }
                }
            }
        }

        $this->info("Finished. " . ($dryRun ? "Would generate " : "Generated ") . "{$count} records.");
        return 0;
    }
}
