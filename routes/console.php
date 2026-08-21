<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
Schedule::command('attendance:mark-missing')->dailyAt('23:59');

// §2.3: Flag missing clock-outs nightly so corrections resolve before payroll lock
Schedule::command('attendance:scan-discrepancies')->dailyAt('23:30');
