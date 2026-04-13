<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_payroll_calculation()
    {
        $service = new PayrollService();
        
        \App\Models\AppSetting::create([
            'sss_rate' => 0.0450,
            'pagibig_rate' => 0.0200,
            'philhealth_rate' => 0.0500
        ]);

        $employee = Employee::create([
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'daily_rate' => 1000,
            'status' => 'active',
            'email' => 'john@example.com',
            'position' => 'Staff'
        ]);

        $payroll = Payroll::create([
            'payroll_code' => 'PAY-2026-001',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-05',
            'pay_date' => '2026-04-10',
            'status' => 'pending'
        ]);

        // Mock 1 day of attendance (8 hours)
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-04-01',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'total_hours' => 8,
            'late_minutes' => 0,
            'undertime_minutes' => 0,
            'status' => 'verified'
        ]);

        $service->computePayroll($payroll);
        
        $item = $payroll->items()->first();

        // Basic pay: 1 day * 1000 = 1000
        // Deductions (Default rates): 
        // 4.5% SSS (45), 2% Pag-IBIG (20), 5% PhilHealth (50) = 115
        // Net: 1000 - 115 = 885
        $this->assertEquals(1000, $item->basic_pay);
        $this->assertEquals(885, $item->net_pay);
    }
}
