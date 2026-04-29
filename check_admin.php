<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'accountant.hr@mebs.com')->first();
if ($user) {
    $role = \Spatie\Permission\Models\Role::findByName('Accounting Admin');
    $user->assignRole($role);
    echo "Assigned 'Accounting Admin' role to accountant.hr@mebs.com\n";
} else {
    echo "Accountant user not found\n";
}
