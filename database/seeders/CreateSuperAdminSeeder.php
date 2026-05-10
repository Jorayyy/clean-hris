<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the Super Admin role exists
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // Sync all existing permissions to the Super Admin role
        $role->syncPermissions(Permission::all());

        // List of admin users to ensure exists and have Super Admin role
        $admins = [
            [
                'email' => 'super.admin@example.com',
                'name' => 'Super Admin User',
                'password' => Hash::make('password123'),
                'role' => 'super-admin',
            ],
            [
                'email' => 'admin@test.com',
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        ];

        foreach ($admins as $adminData) {
            // Create or update the admin user
            $user = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => $adminData['password'],
                    'role' => $adminData['role'],
                ]
            );

            // Assign the role to the user
            $user->assignRole($role);
        }
    }
}
