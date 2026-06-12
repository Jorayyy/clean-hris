<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\Dtr;
use App\Models\AuthorizedNetwork;
use App\Models\Schedule;
use App\Models\ScheduleGroup;
use App\Models\AuditLog;
use App\Models\PayrollGroup;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FullSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_system_integration()
    {
        // --- 1. Initialize Simulation ---
        
        // Setup environment
        AuthorizedNetwork::create(['ip_address' => '127.0.0.1', 'name' => 'Office', 'label' => 'HQ', 'is_active' => true]);
        
        // Create Super Admin
        $superAdmin = User::factory()->create(['role' => 'super-admin', 'email' => 'superadmin@example.com']);
        
        // Create 2 HR Admins
        $hrAdmin1 = User::factory()->create(['role' => 'admin', 'email' => 'hr1@example.com']);
        $hrAdmin2 = User::factory()->create(['role' => 'admin', 'email' => 'hr2@example.com']);
        
        // Create Payroll Group
        $pg = PayrollGroup::create(['name' => 'Regular Staff']);

        // Create Schedule Templates
        $dayShift = Schedule::create([
            'name' => 'Day Shift Template',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'is_template' => true
        ]);

        $nightShift = Schedule::create([
            'name' => 'Night Shift Template',
            'time_in' => '22:00:00',
            'time_out' => '06:00:00',
            'is_template' => true
        ]);

        // Create Schedule Groups
        $dayShiftGroup = ScheduleGroup::create([
            'name' => 'Day Shift (8-5)',
            'schedule_config' => [
                'Monday' => $dayShift->id,
                'Tuesday' => $dayShift->id,
                'Wednesday' => $dayShift->id,
                'Thursday' => $dayShift->id,
                'Friday' => $dayShift->id,
                'Saturday' => $dayShift->id,
                'Sunday' => 'OFF'
            ]
        ]);

        $nightShiftGroup = ScheduleGroup::create([
            'name' => 'Night Shift (10-6)',
            'schedule_config' => [
                'Monday' => $nightShift->id,
                'Tuesday' => $nightShift->id,
                'Wednesday' => $nightShift->id,
                'Thursday' => $nightShift->id,
                'Friday' => $nightShift->id,
                'Saturday' => $nightShift->id,
                'Sunday' => 'OFF'
            ]
        ]);

        $restDayTodayGroup = ScheduleGroup::create([
            'name' => 'Weekend Off',
            'schedule_config' => [
                'Monday' => $dayShift->id,
                'Tuesday' => $dayShift->id,
                'Wednesday' => $dayShift->id,
                'Thursday' => $dayShift->id,
                'Friday' => $dayShift->id,
                'Saturday' => 'OFF',
                'Sunday' => 'OFF'
            ]
        ]);

        // Create 10 Employees
        // 1-4 Day Shift
        // 5-8 Night Shift
        // 9-10 Rest Day today (Saturday)
        $employees = [];
        for ($i = 1; $i <= 10; $i++) {
            $groupId = null;
            if ($i <= 4) $groupId = $dayShiftGroup->id;
            elseif ($i <= 8) $groupId = $nightShiftGroup->id;
            else $groupId = $restDayTodayGroup->id;

            $emp = Employee::factory()->create([
                'employee_id' => 'EMP-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'web_bundy_code' => '123456',
                'schedule_group_id' => $groupId,
                'payroll_group_id' => $pg->id,
                'status' => 'active'
            ]);
            $employees[] = $emp;
        }

        // Initialize App Settings
        AppSetting::create([
            'sss_rate' => 0.045,
            'pagibig_rate' => 0.02,
            'philhealth_rate' => 0.05,
            'app_name' => 'HRIS System'
        ]);

        // --- 2. Action Simulation ---

        // Step A: Employees 1-5 punch IN for Day Shift
        // Today is 2026-06-13 (Saturday)
        Carbon::setTestNow('2026-06-13 08:15:00'); // 15 mins late for day shift
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('bundy.punch'), [
                'employee_id_string' => $employees[$i]->employee_id,
                'web_bundy_code' => '123456',
                'punch_type' => 'am_in',
            ])->assertSessionHasNoErrors();
        }

        // Step B: HR Admin 1 processes a Payroll Batch for the previous period
        $previousPayroll = Payroll::create([
            'payroll_code' => 'PREV-BATCH-001',
            'start_date' => '2026-05-15',
            'end_date' => '2026-05-31',
            'pay_date' => '2026-06-15',
            'status' => 'draft'
        ]);

        // Seed some DTRs for the previous period
        foreach ($employees as $emp) {
            Dtr::create([
                'employee_id' => $emp->id,
                'start_date' => '2026-05-15',
                'end_date' => '2026-05-31',
                'total_regular_hours' => 88,
                'status' => 'finalized'
            ]);
        }

        $this->actingAs($hrAdmin1)->post(route('payroll.process-batch', $previousPayroll))
            ->assertRedirect();
        $this->assertEquals('processed', $previousPayroll->fresh()->status);

        // Step C: Super Admin updates an App Setting
        $this->actingAs($superAdmin)->post(route('admin.settings.update'), [
            'app_name' => 'New HRIS Name',
            'sss_rate' => 0.050, // Updated from 0.045
            'pagibig_rate' => 0.02,
            'philhealth_rate' => 0.05,
            'late_rate' => 1.0,
            'undertime_rate' => 1.0,
        ])->assertRedirect();

        // Step D: Employee 6 (Night Shift) punches IN at a simulated current time of 10 PM yesterday record
        // Yesterday record means June 12. Current time should be something like June 13 early morning.
        // If they punch at 2 AM June 13 for a shift that started 10 PM June 12.
        Carbon::setTestNow('2026-06-13 02:00:00'); 
        $emp6 = $employees[5]; // Index 5 is EMP-006
        
        $this->post(route('bundy.punch'), [
            'employee_id_string' => $emp6->employee_id,
            'web_bundy_code' => '123456',
            'punch_type' => 'am_in',
        ])->assertSessionHasNoErrors();

        // Step E: HR Admin 2 tries to delete an Employee currently in the Payroll batch
        // Using EMP-001 who is in 'PREV-BATCH-001'
        $empToDelete = $employees[0];
        $response = $this->actingAs($hrAdmin2)->delete(route('employees.destroy', $empToDelete));
        
        // --- 3. Verification ---

        // Check late_minutes for Day Shift (EMP 1-4)
        // Shift start 08:00, punched at 08:15 = 15 mins late
        for ($i = 0; $i < 4; $i++) {
            $att = Attendance::where('employee_id', $employees[$i]->id)->where('date', '2026-06-13')->first();
            $this->assertNotNull($att, "Attendance for EMP-" . ($i+1) . " should exist");
            $this->assertEquals(15, $att->late_minutes, "EMP-" . ($i+1) . " should have 15 late minutes");
        }

        // Check EMP-006 late_minutes
        // Shift start June 12 22:00, Punched June 13 02:00.
        // That is 4 hours late = 240 minutes.
        $att6 = Attendance::where('employee_id', $emp6->id)->where('date', '2026-06-12')->first();
        $this->assertNotNull($att6, "Attendance for EMP-006 should exist for June 12");
        $this->assertEquals(240, $att6->late_minutes, "EMP-006 should have 240 late minutes");

        // Check AuditLogs
        $this->assertTrue(AuditLog::where('user_id', $hrAdmin1->id)->where('model_type', Payroll::class)->exists(), "HR Admin 1 payroll action should be logged in AuditLog");
        $this->assertTrue(AuditLog::where('user_id', $superAdmin->id)->where('model_type', AppSetting::class)->exists(), "Super Admin setting update should be logged in AuditLog");

        // Verify Step E: Employee should still exist if protection is working (or check the message/response)
        $this->assertTrue(Employee::where('id', $empToDelete->id)->exists(), "Employee should NOT be deleted because they are in a payroll batch");

        // N+1 Query Warning Check (Manual count or just check no explosion)
        // (In a real scenario, one could use Laravel Telescope or a package like laravel-query-detector)
    }
}
