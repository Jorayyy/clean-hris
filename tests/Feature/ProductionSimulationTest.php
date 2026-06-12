<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\Dtr;
use App\Models\AuthorizedNetwork;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ProductionSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_simulation()
    {
        // 0. Setup Environment
        AuthorizedNetwork::create(['ip_address' => '127.0.0.1', 'name' => 'Office', 'label' => 'HQ', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin']);
        
        AppSetting::create([
            'sss_rate' => 0.0450,
            'pagibig_rate' => 0.0200,
            'philhealth_rate' => 0.0500,
            'app_name' => 'Initial Name'
        ]);

        // 1. Setup Many Employees (Agent 2 - Massive Batch)
        $employees = Employee::factory()->count(55)->create([
            'status' => 'active',
            'daily_rate' => 1000
        ]);

        $payroll = Payroll::create([
            'payroll_code' => 'BATCH-SIM-001',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-15',
            'pay_date' => '2026-06-20',
            'status' => 'draft'
        ]);

        // Finalize DTRs for all 55 employees for the period
        foreach ($employees as $emp) {
            Dtr::create([
                'employee_id' => $emp->id,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-15',
                'total_regular_hours' => 80, // 10 days
                'total_late_minutes' => 0,
                'total_undertime_minutes' => 0,
                'total_overtime_hours' => 0,
                'status' => 'finalized'
            ]);
        }

        // 2. Simulating Agent Actions
        
        // Agent 3 (Day Shift): Punching IN for TODAY
        Carbon::setTestNow(Carbon::parse('2026-06-13 08:30:00'));
        $empA = $employees[0];
        $empA->update(['web_bundy_code' => '111111']);

        // Give empA a schedule
        \App\Models\Schedule::create([
            'employee_id' => $empA->id,
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'days' => ['Saturday'],
            'is_active' => true
        ]);
        
        $response = $this->post(route('bundy.punch'), [
            'employee_id_string' => $empA->employee_id,
            'web_bundy_code' => '111111',
            'punch_type' => 'am_in',
        ]);
        $response->assertSessionHasNoErrors();
        
        // Agent 4 (Night Shift): Punching in late at 11 PM for an overnight shift
        Carbon::setTestNow(Carbon::parse('2026-06-13 23:00:00'));
        $empB = $employees[1];
        $empB->update(['web_bundy_code' => '222222']);
        
        // Ensure he has a night shift schedule (22:00 - 06:00)
        \App\Models\Schedule::create([
            'employee_id' => $empB->id,
            'time_in' => '22:00:00',
            'time_out' => '06:00:00',
            'days' => ['Saturday'], // June 13, 2026 is Saturday
            'is_active' => true
        ]);

        $response = $this->post(route('bundy.punch'), [
            'employee_id_string' => $empB->employee_id,
            'web_bundy_code' => '222222',
            'punch_type' => 'am_in',
        ]);
        $response->assertSessionHasNoErrors();

        // 3. Agent 2 (HR Admin): Process the batch
        $service = new PayrollService();
        $service->computePayroll($payroll);

        // Verify Payroll results
        $this->assertEquals(55, $payroll->items()->count(), "Massive batch should process all 55 employees");
        $this->assertEquals('processed', $payroll->fresh()->status);

        // Check if Agent 3's punch is recorded correctly
        $attendanceA = Attendance::where('employee_id', $empA->id)->where('date', '2026-06-13')->first();
        $this->assertNotNull($attendanceA);
        $this->assertEquals('08:30:00', $attendanceA->time_in);

        // Check if Agent 4's late punch recorded correct date (should be June 13)
        $attendanceB = Attendance::where('employee_id', $empB->id)->where('date', '2026-06-13')->first();
        $this->assertNotNull($attendanceB);
        $this->assertEquals('23:00:00', $attendanceB->time_in);
        // June 13 11PM start for a 10PM shift means 60 minutes late
        $this->assertEquals(60, $attendanceB->late_minutes);

        // Agent 1 (Super Admin): Modify settings while payroll is processing (log only since we can't truly thread)
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'app_name' => 'Modified Name',
            'sss_rate' => 0.05,
            'pagibig_rate' => 0.03,
            'philhealth_rate' => 0.04,
            'late_rate' => 1.0,
            'undertime_rate' => 1.0,
        ]);

        // Verify Audit Logs
        $this->assertTrue(\App\Models\AuditLog::where('model_type', Payroll::class)->exists(), "Payroll processing should be logged");
        $this->assertTrue(\App\Models\AuditLog::where('model_type', AppSetting::class)->exists(), "Setting updates should be logged");
    }
}
