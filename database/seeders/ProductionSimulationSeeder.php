<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionSimulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Baseline Data
        $positions = ['Developer', 'Project Manager', 'HR Specialist', 'Accounting', 'Support'];
        foreach ($positions as $p) {
            \App\Models\Position::firstOrCreate(['name' => $p]);
        }

        $sites = ['Main Office', 'Technopark', 'Satellite B'];
        $siteModels = [];
        foreach ($sites as $s) {
            $siteModels[] = \App\Models\Site::firstOrCreate(['name' => $s]);
        }

        // 2. Create Employees
        $employees = \App\Models\Employee::factory()->count(20)->create();
        
        foreach ($employees as $emp) {
            $emp->update([
                'site_id' => $siteModels[array_rand($siteModels)]->id,
                'classification' => ['Full-time', 'Part-time', 'Contractor'][rand(0, 2)],
                'position' => $positions[array_rand($positions)]
            ]);
        }

        // 3. Create Attendance for the last 7 days (for the chart)
        foreach ($employees as $employee) {
            for ($i = 6; $i >= 0; $i--) {
                if (rand(1, 10) > 8) continue;

                $date = \Carbon\Carbon::now()->subDays($i)->toDateString();
                
                \App\Models\Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'time_in' => '08:00:00',
                    'time_out' => '17:00:00',
                    'total_hours' => 8,
                    'late_minutes' => rand(0, 15),
                    'undertime_minutes' => 0,
                ]);
            }
        }

        // 4. Create some "Priority Tasks"
        \App\Models\Dtr::create([
            'employee_id' => $employees->random()->id,
            'start_date' => \Carbon\Carbon::now()->subDays(15)->toDateString(),
            'end_date' => \Carbon\Carbon::now()->subDays(1)->toDateString(),
            'status' => 'draft',
            'total_regular_hours' => 40
        ]);

        \App\Models\SupportTicket::create([
            'employee_id' => $employees->random()->id,
            'type' => 'DTR Issue',
            'subject' => 'DTR Correction Request',
            'description' => 'I forgot to punch out last Tuesday.',
            'status' => 'open',
            'priority' => 'high'
        ]);

        // 5. Create a Sample Payroll
        $payroll = \App\Models\Payroll::create([
            'payroll_code' => 'BATCH-' . strtoupper(bin2hex(random_bytes(3))),
            'start_date' => \Carbon\Carbon::now()->subDays(30)->toDateString(),
            'end_date' => \Carbon\Carbon::now()->subDays(15)->toDateString(),
            'pay_date' => \Carbon\Carbon::now()->toDateString(),
            'status' => 'draft'
        ]);
        
        foreach ($employees->take(8) as $emp) {
            \App\Models\PayrollItem::create([
                'payroll_id' => $payroll->id,
                'employee_id' => $emp->id,
                'total_days' => 10,
                'total_hours' => 80,
                'basic_pay' => 8000,
                'overtime_pay' => rand(0, 1000),
                'night_diff' => 0,
                'bonuses' => 0,
                'deductions_sss' => 400,
                'deductions_pagibig' => 200,
                'deductions_philhealth' => 300,
                'other_deductions' => 0,
                'net_pay' => 7100
            ]);
        }
    }
}
