<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('schedules:migrate-days')]
#[Description('Migrate days string to day_of_week integers')]
class MigrateSchedulesToDayOfWeek extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dayMap = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
            'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6
        ];

        $schedules = \App\Models\Schedule::whereNotNull('days')
            ->whereNull('day_of_week')
            ->whereNull('schedule_date')
            ->get();
            
        $count = 0;

        foreach ($schedules as $schedule) {
            $dayName = trim($schedule->days);
            // Handle common variations
            $dayName = ucfirst(strtolower($dayName));
            
            if (isset($dayMap[$dayName])) {
                $schedule->day_of_week = $dayMap[$dayName];
                $schedule->save();
                $count++;
            }
        }

        $this->info("Migrated $count schedules.");
    }
}
