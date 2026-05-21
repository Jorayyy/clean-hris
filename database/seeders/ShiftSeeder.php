<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Shift::create([
            'name' => 'Morning Shift',
            'code' => 'MOR',
            'time_in' => '06:00:00',
            'time_out' => '15:00:00',
            'color' => '#3b82f6',
        ]);

        \App\Models\Shift::create([
            'name' => 'Mid Shift',
            'code' => 'MID',
            'time_in' => '14:00:00',
            'time_out' => '23:00:00',
            'color' => '#10b981',
        ]);

        \App\Models\Shift::create([
            'name' => 'Night Shift',
            'code' => 'NIGHT',
            'time_in' => '22:00:00',
            'time_out' => '07:00:00',
            'color' => '#f59e0b',
        ]);
    }
}
