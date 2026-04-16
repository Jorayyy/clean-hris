<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class CreateAccountingTester extends Command
{
    protected $signature = 'setup:accounting-test';
    protected $description = 'Create an Accounting test user to verify field visibility';

    public function handle()
    {
        // 1. Create or Find the Employee with Accounting classification
        $employee = Employee::updateOrCreate(
            ['employee_id' => 'ACC-TEST'],
            [
                'first_name' => 'Test',
                'last_name' => 'Accounting',
                'email' => 'accounting.test@example.com',
                'position' => 'Accounting Officer',
                'classification' => 'Accounting',
                'status' => 'active',
                'web_bundy_code' => '1234',
                'daily_rate' => 1000,
            ]
        );

        // 2. Create a User linked to this employee
        $user = User::updateOrCreate(
            ['email' => 'accounting.test@example.com'],
            [
                'name' => 'Accounting Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin', // This gives them access to /admin/employees
                'employee_id' => $employee->id
            ]
        );

        $this->info("Accounting test user created successfully!");
        $this->info("Login: accounting.test@example.com");
        $this->info("Password: password123");
        $this->warn("Once logged in, go to any employee edit page to verify you CAN see the Daily Rate.");
    }
}
