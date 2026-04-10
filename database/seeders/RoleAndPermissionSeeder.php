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
            Permission::create(['name' => $permission]);
        }

        // Create Roles and assign permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $hrAdmin = Role::create(['name' => 'HR Admin']);
        $hrAdmin->givePermissionTo([
            'view employees', 'create employees', 'edit employees',
            'view attendance', 'create attendance', 'edit attendance',
            'view payroll', 'view users'
        ]);

        $accountingAdmin = Role::create(['name' => 'Accounting Admin']);
        $accountingAdmin->givePermissionTo([
            'view payroll', 'create payroll', 'edit payroll', 'process payroll',
            'view employees', 'view attendance'
        ]);

        $employeeRole = Role::create(['name' => 'Employee']);
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
