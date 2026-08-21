<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\FieldVisibility;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Coarse model-level permissions kept for backward compatibility with
     * policies/middleware already in place.
     */
    protected array $modelPermissions = [
        'view employees', 'create employees', 'edit employees', 'delete employees',
        'view attendance', 'create attendance', 'edit attendance', 'delete attendance',
        'view payroll', 'create payroll', 'edit payroll', 'delete payroll', 'process payroll',
        'view users', 'create users', 'edit users', 'delete users',
        'view settings', 'edit settings',
        'approve payroll',
    ];

    /**
     * Field-level permissions (§1.3): "view {table}.{field}" and
     * "edit {table}.{field}" for every sensitive column, consumed by
     * App\Support\FieldVisibility.
     */
    protected array $fieldTables = [
        'employees' => ['daily_rate', 'bank_name', 'account_no', 'tin_no', 'sss_no', 'pagibig_no', 'philhealth_no'],
        'payroll_items' => ['basic_pay', 'net_pay', 'deductions_json'],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->modelPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach ($this->fieldTables as $table => $fields) {
            foreach ($fields as $field) {
                Permission::findOrCreate("view {$table}.{$field}");
                Permission::findOrCreate("edit {$table}.{$field}");
            }
        }

        // Super Admin sees everything
        $superAdmin = Role::findOrCreate('Super Admin');
        $superAdmin->syncPermissions(Permission::all());

        // HR Admin: full identity-field access, but bank details are read-only
        // for them (financial account maintenance belongs to accounting).
        $hrAdmin = Role::findOrCreate('HR Admin');
        $hrAdmin->syncPermissions(array_merge([
            'view employees', 'create employees', 'edit employees',
            'view attendance', 'create attendance', 'edit attendance',
            'view payroll', 'view users',
        ], $this->fieldPermissions('employees', ['daily_rate', 'tin_no', 'sss_no', 'pagibig_no', 'philhealth_no'], ['view', 'edit']),
            $this->fieldPermissions('employees', ['bank_name', 'account_no'], ['view'])
        ));

        // Accounting Admin: compensation visibility plus payroll item editing.
        $accountingAdmin = Role::findOrCreate('Accounting Admin');
        $accountingAdmin->syncPermissions(array_merge([
            'view payroll', 'create payroll', 'edit payroll', 'process payroll', 'approve payroll',
            'view employees', 'view attendance',
        ],
            $this->fieldPermissions('employees', ['daily_rate'], ['view', 'edit']),
            $this->fieldPermissions('employees', ['bank_name', 'account_no'], ['view', 'edit']),
            $this->fieldPermissions('payroll_items', FieldVisibility::fieldsFor('payroll_items'), ['view', 'edit'])
        ));

        // Payroll Clerk (§1.3 example): can see daily rates and payroll math,
        // but can never touch bank account numbers.
        $payrollClerk = Role::findOrCreate('Payroll Clerk');
        $payrollClerk->syncPermissions(array_merge([
            'view payroll', 'process payroll', 'view employees', 'view attendance',
        ],
            $this->fieldPermissions('employees', ['daily_rate'], ['view']),
            $this->fieldPermissions('payroll_items', FieldVisibility::fieldsFor('payroll_items'), ['view', 'edit'])
        ));

        // Bank File Exporter (§1.3 example): the reverse of the clerk —
        // bank coordinates only, no compensation figures.
        $bankExporter = Role::findOrCreate('Bank File Exporter');
        $bankExporter->syncPermissions(array_merge([
            'view employees',
        ],
            $this->fieldPermissions('employees', ['bank_name', 'account_no'], ['view'])
        ));

        $employeeRole = Role::findOrCreate('Employee');

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

    protected function fieldPermissions(string $table, array $fields, array $actions): array
    {
        $permissions = [];

        foreach ($fields as $field) {
            foreach ($actions as $action) {
                $permissions[] = "{$action} {$table}.{$field}";
            }
        }

        return $permissions;
    }
}
