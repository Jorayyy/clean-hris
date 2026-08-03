<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Models\Schedule;
use App\Models\Shift;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TempMondayLiveShiftSeeder extends Seeder
{
    public function run(): void
    {
        $monday = Carbon::now()->startOfWeek()->toDateString();

        $group = PayrollGroup::firstOrCreate(
            ['name' => 'Demo Monday Group'],
            ['description' => 'Temporary demo payroll group for live shift preview']
        );

        $shift = Shift::firstOrCreate(
            ['code' => 'DEMO-MON'],
            [
                'name' => 'Demo Monday Live Shift',
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'break_minutes' => 60,
                'grace_period' => 0,
                'color' => '#198754',
                'type' => 'Standard',
                'is_active' => true,
            ]
        );

        $employee = Employee::updateOrCreate(
            ['employee_id' => 'DEMO-MON-001'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Monday',
                'email' => 'demo.monday@local.test',
                'position' => 'Operations Staff',
                'daily_rate' => 1000.00,
                'status' => 'active',
                'payroll_group_id' => $group->id,
            ]
        );

        Schedule::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'schedule_date' => $monday,
            ],
            [
                'name' => 'Monday Live Shift',
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'days' => json_encode(['Monday']),
                'payroll_group_id' => $group->id,
                'shift_id' => $shift->id,
                'custom_shift_id' => null,
                'assigned_by' => null,
                'remarks' => 'Temporary demo seed for UI preview',
            ]
        );

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $monday,
            ],
            [
                'time_in' => '08:05:00',
                'time_out' => '00:00:00',
                'total_hours' => 0,
                'late_minutes' => 5,
                'undertime_minutes' => 0,
                'ot_authorized' => false,
            ]
        );

        $stats = app(PayrollService::class)->calculateAttendanceStats(
            $attendance->time_in,
            $attendance->time_out,
            $employee->id,
            $monday
        );

        $attendance->update($stats);
    }
}