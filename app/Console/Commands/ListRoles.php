<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:list-roles')]
#[Description('List all spatie roles')]
class ListRoles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        foreach ($roles as $role) {
            $this->info("Role: {$role->name}");
        }
    }
}
