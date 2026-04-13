<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

#[Signature('admin:reset')]
#[Description('Reset Super Admin')]
class ResetSuperAdminAccount extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Permission::firstOrCreate(['name' => 'approve-payroll']);
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->syncPermissions(Permission::all());

        User::updateOrCreate(
            ['email' => 'superadmin@hris.com'],
            ['name' => 'MEBS Super Admin', 'password' => Hash::make('SuperAdmin2026!'), 'role' => 'admin']
        )->assignRole($role);

        $this->info('Super Admin ready: superadmin@hris.com / SuperAdmin2026!');
    }
}
