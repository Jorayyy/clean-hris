<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Employee;
use App\Models\Attendance;
use App\Services\PayrollService;
use Carbon\Carbon;

#[Signature('attendance:mark-missing')]
#[Description('Mark missing punches for the day and calculate penalties')]
class MarkMissingPunches extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $employees = Employee::where('employment_status', 'Regular')->get();
        $payrollService = app(PayrollService::class);

        foreach ($employees as $employee) {
            $schedule = $employee->active_schedule;
            if (!$schedule) continue;

            // Check if employee was scheduled for today
            if (is_array($schedule->days) && !in_array(Carbon::today()->format('l'), $schedule->days)) {
                continue;
            }

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$attendance) {
                // ABSENT
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $today,
                    'time_in' => '00:00:00',
                    'time_out' => '00:00:00',
                    'total_hours' => 0,
                    'late_minutes' => 480, // Default 8 hours late for payroll calculation
                    'undertime_minutes' => 0,
                    'remarks' => 'Absent (No punches record)'
                ]);
                $this->info("Marked {$employee->full_name} as Absent.");
                continue;
            }

            // If punched IN but never punched OUT
            if (($attendance->time_in && $attendance->time_in !== '00:00:00') && 
                (!$attendance->time_out || $attendance->time_out === '00:00:00')) {
                
                $attendance->update([
                    'time_out' => '00:00:00',
                    'remarks' => 'No Punch Out'
                ]);

                // Calculate stats with 00:00:00 out
                $stats = $payrollService->calculateAttendanceStats(
                    $attendance->time_in,
                    '00:00:00',
                    $employee->id,
                    $attendance->date
                );

                $attendance->update($stats);
                $this->warn("Marked Missing Punch Out for {$employee->full_name}.");
            }
        }

        $this->info('Attendance audit complete.');
    }
}
