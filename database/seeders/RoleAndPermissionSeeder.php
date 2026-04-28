<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view attendance', 'create attendance', 'edit attendance', 'delete attendance',
            'view payroll', 'create payroll', 'edit payroll', 'delete payroll', 'process payroll',
            'view users', 'create users', 'edit users', 'delete users',
            'view settings', 'edit settings'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Roles and assign permissions
        $superAdmin = Role::findOrCreate('Super Admin');
        $superAdmin->syncPermissions(Permission::all());

        $hrAdmin = Role::findOrCreate('HR Admin');
        $hrAdmin->syncPermissions([
            'view employees', 'create employees', 'edit employees',
            'view attendance', 'create attendance', 'edit attendance',
            'view payroll', 'view users'
        ]);

        $accountingAdmin = Role::findOrCreate('Accounting Admin');
        $accountingAdmin->syncPermissions([
            'view payroll', 'create payroll', 'edit payroll', 'process payroll',
            'view employees', 'view attendance'
        ]);

        $employeeRole = Role::findOrCreate('Employee');
        // Employees usually have specific policies or dashboard permissions

        // Assign Super Admin role to the first admin user
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->assignRole($superAdmin);
        }

        // Assign Employee role to all other users
        $employees = User::where('role', 'employee')->get();
        foreach ($employees as $emp) {
            $emp->assignRole($employeeRole);
        }
    }
}
