<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;

$employees = Employee::where('status', 'active')->get();
echo "--- ACTIVE EMPLOYEES BUNDY CODES ---\n";
$updated = 0;
foreach ($employees as $e) {
    if (empty($e->web_bundy_code)) {
        $e->web_bundy_code = '1234';
        $e->save();
        $updated++;
    }
    echo "ID: " . str_pad($e->employee_id, 10) . " | Name: " . str_pad($e->first_name . " " . $e->last_name, 25) . " | PIN: " . $e->web_bundy_code . "\n";
}
echo "------------------------------------\n";

// Added reset logic
use App\Models\Attendance;
$deleted = Attendance::whereDate('date', \Carbon\Carbon::today())->delete();
echo "TEST RESET: Deleted $deleted attendance records for today.\n";
echo "You can now test your punch sequence again!\n";
