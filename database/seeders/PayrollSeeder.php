<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollGroup;
use App\Services\PayrollService;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Create Payroll Group
        $group = PayrollGroup::firstOrCreate(['name' => 'Monthly Staff'], ['description' => 'Regular monthly employees']);

        // 1. Create Sample Employees
        $emp1 = Employee::updateOrCreate(
            ['employee_id' => 'EMP-001'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@admin.com',
                'position' => 'Senior Developer',
                'daily_rate' => 1200.00,
                'status' => 'active',
                'payroll_group_id' => $group->id,
            ]
        );

        $emp2 = Employee::updateOrCreate(
            ['employee_id' => 'EMP-002'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@hr.com',
                'position' => 'HR Manager',
                'daily_rate' => 1000.00,
                'status' => 'active',
                'payroll_group_id' => $group->id,
            ]
        );

        // 2. Create Attendance for last week (Mon-Fri)
        $monday = Carbon::now()->startOfWeek();
        $service = new PayrollService();

        for ($i = 0; $i < 5; $i++) {
            $date = $monday->copy()->addDays($i)->format('Y-m-d');
            
            // Emp1: Regular 8-5
            $stats1 = $service->calculateAttendanceStats('08:00', '17:00');
            Attendance::create(array_merge([
                'employee_id' => $emp1->id,
                'date' => $date,
                'time_in' => '08:00',
                'time_out' => '17:00',
            ], $stats1));

            // Emp2: Late 8:30-5:30 (9 hours total)
            $stats2 = $service->calculateAttendanceStats('08:30', '17:30');
            Attendance::create(array_merge([
                'employee_id' => $emp2->id,
                'date' => $date,
                'time_in' => '08:30',
                'time_out' => '17:30',
            ], $stats2));
        }

        // 3. Create a Payroll Batch
        Payroll::create([
            'payroll_code' => 'PAY-SEED-01',
            'start_date' => $monday->format('Y-m-d'),
            'end_date' => $monday->copy()->addDays(4)->format('Y-m-d'),
            'pay_date' => $monday->copy()->addDays(4)->format('Y-m-d'),
            'status' => 'draft',
            'payroll_group_id' => $group->id,
        ]);
    }
}
