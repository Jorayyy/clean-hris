<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run core systemic seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            CreateSuperAdminSeeder::class, // Ensure this file exists and uses 'superadmin@gmail.com'
            PayrollSeeder::class,
        ]);

        // 2. Backup Plan: Explicitly ensure the Super Admin exists here if the sub-seeder fails
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Change 'password' to your preferred login password
                'role' => 'superadmin', 
            ]
        );

        // 3. Employee User (Linking to first employee from PayrollSeeder)
        $employee = Employee::first();
        if ($employee) {
            User::updateOrCreate(
                ['email' => 'employee@test.com'],
                [
                    'name' => $employee->full_name,
                    'password' => Hash::make('password'),
                    'role' => 'employee',
                    'employee_id' => $employee->id,
                ]
            );
        }
    }
}
