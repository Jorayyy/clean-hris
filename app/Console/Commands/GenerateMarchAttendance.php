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
    protected $signature = 'attendance:generate-test {--start=2026-03-30} {--end=2026-04-03}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing attendance records based on employee schedules for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start');
        $endDate = $this->option('end');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $period = CarbonPeriod::create($start, $end);
        $employees = Employee::all();
        $count = 0;

        foreach ($employees as $employee) {
            $schedule = $employee->active_schedule;
            if (!$schedule) {
                continue;
            }

            foreach ($period as $date) {
                $dayName = $date->format('l');

                if (is_array($schedule->days) && in_array($dayName, $schedule->days)) {
                    // Check if already exists
                    $exists = Attendance::where('employee_id', $employee->id)
                        ->where('date', $date->toDateString())
                        ->exists();

                    if (!$exists) {
                        $newTimeIn = $schedule->time_in;
                        $newTimeOut = $schedule->time_out;

                        // Calculate total hours
                        $in = Carbon::parse($date->toDateString() . ' ' . $newTimeIn);
                        $out = Carbon::parse($date->toDateString() . ' ' . $newTimeOut);
                        
                        if ($out->lessThan($in)) {
                            $out->addDay();
                        }

                        $totalHours = abs($out->diffInMinutes($in) / 60);

                        Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $date->toDateString(),
                            'time_in' => $newTimeIn ?? '08:00:00',
                            'time_out' => $newTimeOut ?? '17:00:00',
                            'total_hours' => $totalHours,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                        ]);

                        $this->line("Generated for {$employee->first_name} on {$date->toDateString()}");
                        $count++;
                    }
                }
            }
        }

        $this->info("Finished. Generated {$count} records.");
        return 0;
    }
}
