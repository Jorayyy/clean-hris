<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Dtr;
use App\Models\Payroll;
use App\Services\PayrollService;
use Carbon\Carbon;

class PayrollFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_payroll_flow()
    {
        // 1. Setup Admin and Employee
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create([
            'daily_rate' => 800,
            'status' => 'active'
        ]);

        // 2. Mock Attendance for a period
        $date = '2026-06-01';
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'total_hours' => 8,
            'late_minutes' => 0,
            'undertime_minutes' => 0
        ]);

        // 3. Create and Finalize DTR
        $dtr = Dtr::create([
            'employee_id' => $employee->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-15',
            'total_regular_hours' => 8,
            'total_late_minutes' => 0,
            'total_undertime_minutes' => 0,
            'total_overtime_hours' => 0,
            'status' => 'finalized'
        ]);

        // 4. Create Payroll Batch
        $payroll = Payroll::create([
            'payroll_code' => 'TEST-' . time(),
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-15',
            'pay_date' => '2026-06-20',
            'status' => 'draft'
        ]);

        // 5. Run Payroll Service
        $service = new PayrollService();
        $service->computePayroll($payroll);

        // 6. Assertions
        $this->assertDatabaseHas('payroll_items', [
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'basic_pay' => 800 // 1 day * 800
        ]);
        
        $this->assertEquals('processed', $payroll->fresh()->status);
    }
}
