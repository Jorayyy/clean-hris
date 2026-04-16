<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CreateHrAndAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:hr-staff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create HR staff Mia and Jam and generate April attendance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Ensure a Night Shift Schedule exists (9pm - 6am)
        $schedule = Schedule::firstOrCreate(
            ['name' => 'HR Night Shift'],
            [
                'time_in' => '21:00:00',
                'time_out' => '06:00:00',
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
            ]
        );

        $staff = [
            ['first' => 'Mia', 'last' => 'HR', 'id' => 'HR-MIA'],
            ['first' => 'Jam', 'last' => 'HR', 'id' => 'HR-JAM'],
        ];

        $employees = [];
        foreach ($staff as $s) {
            $employee = Employee::updateOrCreate(
                ['employee_id' => $s['id']],
                [
                    'first_name' => $s['first'],
                    'last_name' => $s['last'],
                    'position' => 'HR Admin',
                    'status' => 'active',
                    'web_bundy_code' => '1234',
                    'daily_rate' => 600,
                    // You mentioned you will set the group yourself, 
                    // so we leave payroll_group_id null for now or keep existing
                ]
            );
            
            // Attach schedule if not attached
            $employee->active_schedule()->associate($schedule);
            $employee->save();
            
            $employees[] = $employee;
            $this->info("Staff created/updated: {$s['first']}");
        }

        // 2. Generate Attendance (April 1 to Yesterday)
        $start = Carbon::parse('2026-04-01');
        $end = Carbon::yesterday();
        $period = CarbonPeriod::create($start, $end);

        foreach ($employees as $employee) {
            $count = 0;
            foreach ($period as $date) {
                $dayName = $date->format('l');
                
                // Only work days (Mon-Fri)
                if (in_array($dayName, $schedule->days)) {
                    $dateStr = $date->toDateString();
                    
                    if (!Attendance::where('employee_id', $employee->id)->where('date', $dateStr)->exists()) {
                        // 9:00 PM to 6:00 AM (Next Day)
                        $in = Carbon::parse($dateStr . ' 21:00:00')->addMinutes(rand(-5, 5));
                        $out = $in->copy()->addHours(9)->addMinutes(rand(0, 15));

                        Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $dateStr,
                            'time_in' => $in->format('H:i:s'),
                            'time_out' => $out->format('H:i:s'),
                            'total_hours' => 9,
                            'late_minutes' => $in->format('H:i') > '21:00' ? $in->diffInMinutes(Carbon::parse($dateStr . ' 21:00:00')) : 0,
                            'undertime_minutes' => 0,
                            'status' => 'present'
                        ]);
                        $count++;
                    }
                }
            }
            $this->info("Generated {$count} attendance records for {$employee->first_name}");
        }

        $this->info('Setup completed successfully.');
    }
}
