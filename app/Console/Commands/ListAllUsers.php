<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:list-all')]
#[Description('List all users with roles')]
class ListAllUsers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $roles = $user->getRoleNames()->implode(', ');
            $this->info("ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | RoleAttr: {$user->role} | SpatieRoles: {$roles}");
        }
    }
}
