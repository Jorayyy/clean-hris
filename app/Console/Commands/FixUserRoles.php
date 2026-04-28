<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:fix-roles')]
#[Description('Updates the user role attribute for system-wide access')]
class FixUserRoles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            // Check emails or role names
            if ($user->email === 'superadmin@hris.com' || $user->email === 'super.admin@example.com' || $user->name === 'MEBS Super Admin') {
                $roleName = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->exists() ? 'Super Admin' : 'super-admin';
                $user->syncRoles([$roleName]);
                $user->role = 'super-admin';
                $user->save();
                $this->info("Updated {$user->email} to Super Admin status.");
            } elseif ($user->name === 'HR Admin' || $user->email === 'admin@test.com' || $user->email === 'janie.mebs@hris.com') {
                $user->syncRoles(['HR Admin']);
                $user->role = 'admin';
                $user->save();
                $this->info("Updated {$user->email} to HR Admin status.");
            } elseif ($user->hasRole('Accounting Admin') || str_contains(strtolower($user->name), 'accountant') || $user->email === 'accounting@test.com') {
                $user->syncRoles(['Accounting Admin']);
                $user->role = 'admin';
                $user->save();
                $this->info("Updated {$user->email} to Accounting Admin status.");
            } else {
                $user->role = 'employee';
                $user->save();
                $this->info("Updated {$user->email} to Employee status.");
            }
        }
        $this->info('Done!');
    }
}
