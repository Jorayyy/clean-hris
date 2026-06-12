<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AuthorizedNetwork;
use App\Models\Schedule;
use App\Models\ScheduleGroup;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebBundyPunchTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;
    protected $ip = '127.0.0.1';

    protected function setUp(): void
    {
        parent::setUp();

        // Setup authorized network
        AuthorizedNetwork::create(['name' => 'Localhost', 'ip_address' => $this->ip]);

        // Setup Schedule
        $schedule = Schedule::create([
            'name' => 'Regular Shift',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
        ]);

        $group = ScheduleGroup::create([
            'name' => 'Standard Group',
            'schedule_config' => [
                'Monday' => $schedule->id,
                'Tuesday' => $schedule->id,
                'Wednesday' => $schedule->id,
                'Thursday' => $schedule->id,
                'Friday' => $schedule->id,
                'Saturday' => 'OFF',
                'Sunday' => 'OFF',
            ]
        ]);

        // Setup Employee
        $this->employee = Employee::factory()->create([
            'employee_id' => 'EMP001',
            'web_bundy_code' => '1234',
            'schedule_group_id' => $group->id,
        ]);
    }

    public function test_it_can_record_a_normal_4_punch_day()
    {
        Carbon::setTestNow('2026-06-15 08:00:00'); // Monday

        // 1. AM IN
        $this->postPunch('am_in')->assertSessionHas('bundy_success');
        
        // 2. LUNCH OUT
        Carbon::setTestNow('2026-06-15 12:00:00');
        $this->postPunch('lunch_out')->assertSessionHas('bundy_success');

        // 3. LUNCH IN
        Carbon::setTestNow('2026-06-15 13:00:00');
        $this->postPunch('lunch_in')->assertSessionHas('bundy_success');

        // 4. PM OUT
        Carbon::setTestNow('2026-06-15 17:00:00');
        $this->postPunch('pm_out')->assertSessionHas('bundy_success');

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', '2026-06-15')
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('08:00:00', $attendance->time_in);
        $this->assertEquals('12:00:00', $attendance->lunch_out);
        $this->assertEquals('13:00:00', $attendance->lunch_in);
        $this->assertEquals('17:00:00', $attendance->time_out);
    }

    public function test_it_can_handle_optional_breaks()
    {
        Carbon::setTestNow('2026-06-15 08:00:00');

        $this->postPunch('am_in')->assertSessionHas('bundy_success');
        
        Carbon::setTestNow('2026-06-15 10:00:00');
        $this->postPunch('break1_out')->assertSessionHas('bundy_success');

        Carbon::setTestNow('2026-06-15 10:15:00');
        $this->postPunch('break1_in')->assertSessionHas('bundy_success');

        Carbon::setTestNow('2026-06-15 17:00:00');
        $this->postPunch('pm_out')->assertSessionHas('bundy_success');

        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertEquals('10:00:00', $attendance->break1_out);
        $this->assertEquals('10:15:00', $attendance->break1_in);
    }

    public function test_it_enforces_punch_sequence()
    {
        Carbon::setTestNow('2026-06-15 08:00:00');

        // Cannot lunch out before AM IN
        $this->postPunch('lunch_out')->assertSessionHas('bundy_error');

        $this->postPunch('am_in');

        // Cannot lunch in before lunch out
        $this->postPunch('lunch_in')->assertSessionHas('bundy_error');

        // Cannot PM OUT before AM IN (covered by "must punch START SHIFT first")
        
        // Cannot lunch out EARLIER than am in (e.g. clock drift or malicious intent)
        Carbon::setTestNow('2026-06-15 07:59:00');
        $this->postPunch('lunch_out')->assertSessionHas('bundy_error', 'SEQUENCE ERROR: Lunch Out cannot be earlier than Start Shift.');
    }

    public function test_it_prevents_duplicate_punches()
    {
        Carbon::setTestNow('2026-06-15 08:00:00');

        $this->postPunch('am_in')->assertSessionHas('bundy_success');
        $this->postPunch('am_in')->assertSessionHas('bundy_error');
    }

    public function test_it_handles_overnight_shifts()
    {
        // Setup Night Shift Schedule
        $nightSchedule = Schedule::create([
            'name' => 'Night Shift',
            'time_in' => '22:00:00',
            'time_out' => '06:00:00',
        ]);

        $this->employee->scheduleGroup->update([
            'schedule_config' => [
                'Monday' => $nightSchedule->id,
                'Tuesday' => $nightSchedule->id,
            ]
        ]);

        // Monday 10 PM
        Carbon::setTestNow('2026-06-15 22:00:00');
        $this->postPunch('am_in')->assertSessionHas('bundy_success');

        // Tuesday 6 AM
        Carbon::setTestNow('2026-06-16 06:00:00');
        $this->postPunch('pm_out')->assertSessionHas('bundy_success');

        // Should be recorded under Monday's date
        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', '2026-06-15')
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('22:00:00', $attendance->time_in);
        $this->assertEquals('06:00:00', $attendance->time_out);
    }

    public function test_it_prevents_punches_after_pm_out()
    {
        Carbon::setTestNow('2026-06-15 08:00:00');
        $this->postPunch('am_in');
        
        Carbon::setTestNow('2026-06-15 17:00:00');
        $this->postPunch('pm_out');

        Carbon::setTestNow('2026-06-15 17:30:00');
        $this->postPunch('lunch_out')->assertSessionHas('bundy_error', 'ACTION NOT POSSIBLE: You have already punched PM OUT (END) for this shift. You cannot record more activities for this period.');
    }

    public function test_it_calculates_late_minutes_correctly()
    {
        // Schedule is 08:00
        Carbon::setTestNow('2026-06-15 08:15:00'); 
        $this->postPunch('am_in')->assertSessionHas('bundy_success');

        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertEquals(15, $attendance->late_minutes);
    }

    public function test_it_calculates_overtime_when_authorized()
    {
        Carbon::setTestNow('2026-06-15 08:00:00');
        $this->postPunch('am_in');

        Carbon::setTestNow('2026-06-15 18:00:00'); // 1 hour OT (17:00 is out)
        
        // Before authorizing OT
        $this->postPunch('pm_out');
        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertEquals(0, $attendance->overtime_hours);

        // Reset and try with OT authorized
        $attendance->update(['time_out' => '00:00:00', 'ot_authorized' => true]);
        
        Carbon::setTestNow('2026-06-15 19:00:00'); // 2 hours OT
        $this->postPunch('pm_out');
        
        $attendance->refresh();
        $this->assertEquals(2.0, $attendance->overtime_hours);
    }

    public function test_it_auto_clocks_out_forgotten_shift_from_yesterday()
    {
        // Yesterday 8 AM - Punch In but NO out
        Carbon::setTestNow('2026-06-14 08:00:00'); 
        $this->postPunch('am_in');

        // Today 8 AM - Starting new shift
        Carbon::setTestNow('2026-06-15 08:00:00');
        $this->postPunch('am_in')->assertSessionHas('bundy_success');

        // Yesterday's shift should be closed
        $yesterdayAttendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', '2026-06-14')
            ->first();

        $this->assertNotNull($yesterdayAttendance->time_out);
        $this->assertStringContainsString('Auto-Clockout', $yesterdayAttendance->remarks);
    }

    protected function postPunch($type)
    {
        return $this->post(route('bundy.punch'), [
            'employee_id_string' => 'EMP001',
            'web_bundy_code' => '1234',
            'punch_type' => $type
        ]);
    }
}
