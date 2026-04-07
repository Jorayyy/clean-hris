<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PayrollSeeder::class);

        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'HR Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Employee User (Linking to first employee from PayrollSeeder)
        $employee = \App\Models\Employee::first();
        if ($employee) {
            User::updateOrCreate(
                ['email' => 'employee@test.com'],
                [
                    'name' => $employee->full_name,
                    'password' => bcrypt('password'),
                    'role' => 'employee',
                    'employee_id' => $employee->id,
                ]
            );
        }
    }
}
