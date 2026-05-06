<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Classification;
use App\Models\Level;

class BpoDesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // BPO Classifications
        $classifications = ['Operations', 'Support', 'Quality Assurance', 'Training', 'Human Resources', 'Accounting', 'IT Support', 'Marketing'];
        foreach ($classifications as $name) {
            Classification::updateOrCreate(['name' => $name]);
        }

        // BPO Levels
        $levels = ['Entry Level', 'Junior', 'Senior', 'Team Lead', 'Supervisor', 'Manager', 'Director', 'Executive'];
        foreach ($levels as $name) {
            Level::updateOrCreate(['name' => $name]);
        }

        // BPO Positions
        $positions = [
            'Customer Service Representative (CSR)',
            'Technical Support Representative (TSR)',
            'Outbound Sales Agent',
            'Quality Assurance (QA) Specialist',
            'Team Leader (TL)',
            'Operations Manager (OM)',
            'Trainer',
            'Subject Matter Expert (SME)',
            'Recruitment Specialist',
            'HR Generalist',
            'IT Support Engineer',
            'Data Analyst'
        ];
        foreach ($positions as $name) {
            Position::updateOrCreate(['name' => $name]);
        }
    }
}
