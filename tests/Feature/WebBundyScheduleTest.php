<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\AuthorizedNetwork;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WebBundyScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Authorize the testing IP
        AuthorizedNetwork::create([
            'ip_address' => '127.0.0.1',
            'label' => 'Test Office',
            'name' => 'Test Network',
            'is_active' => true
        ]);

        // Create a test employee with a specific schedule
        $employee = Employee::create([
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Developer',
            'daily_rate' => 1000.00,
            'web_bundy_code' => '123456',
            'employment_status' => 'Regular',
            'department' => 'IT'
        ]);

        // 9:00 AM - 6:00 PM Schedule
        Schedule::create([
            'employee_id' => $employee->id,
            'time_in' => '09:00:00',
            'time_out' => '18:00:00',
            'days' => [Carbon::now()->format('l')],
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_web_bundy_calculates_late_based_on_schedule()
    {
        // Mock time to 09:30 AM (Employee is 30 mins late)
        Carbon::setTestNow(Carbon::today()->setTime(9, 30));

        $this->post(route('bundy.punch'), [
            'employee_id_string' => 'EMP001',
            'web_bundy_code' => '123456',
            'punch_type' => 'am_in',
        ]);

        // Mock time to 06:30 PM (18:30) (Punch out later than schedule)
        Carbon::setTestNow(Carbon::today()->setTime(18, 30));

        $this->post(route('bundy.punch'), [
            'employee_id_string' => 'EMP001',
            'web_bundy_code' => '123456',
            'punch_type' => 'pm_out',
        ]);

        $attendance = Attendance::where('employee_id', 1)->first();
        
        // Late calculation: 09:30 - 09:00 = 30 minutes
        $this->assertEquals(30, $attendance->late_minutes);
        
        // Total Hours: 09:30 to 18:30 = 9 hours
        $this->assertEquals(9, $attendance->total_hours);

        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function test_web_bundy_calculates_undertime_based_on_schedule()
    {
        // Mock time to 08:30 AM (Punch in early - not late)
        Carbon::setTestNow(Carbon::today()->setTime(8, 30));

        $this->post(route('bundy.punch'), [
            'employee_id_string' => 'EMP001',
            'web_bundy_code' => '123456',
            'punch_type' => 'am_in',
        ]);

        // Mock time to 05:30 PM (17:30) (Employee leaves 30 mins early)
        Carbon::setTestNow(Carbon::today()->setTime(17, 30));

        $this->post(route('bundy.punch'), [
            'employee_id_string' => 'EMP001',
            'web_bundy_code' => '123456',
            'punch_type' => 'pm_out',
        ]);

        $attendance = Attendance::where('employee_id', 1)->first();
        
        // Late: 0
        $this->assertEquals(0, $attendance->late_minutes);
        
        // Undertime: 18:00 - 17:30 = 30 minutes
        $this->assertEquals(30, $attendance->undertime_minutes);
        
        // Total Hours: 8:30 to 17:30 = 9 hours
        $this->assertEquals(9, $attendance->total_hours);

        Carbon::setTestNow(); // Reset
    }
}
