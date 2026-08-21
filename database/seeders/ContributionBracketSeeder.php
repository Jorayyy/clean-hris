<?php

namespace Database\Seeders;

use App\Models\ContributionBracket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContributionBracketSeeder extends Seeder
{
    /**
     * Published Philippine statutory tables, seeded as data (§2.2) so rate
     * changes no longer require a code deploy. Rows are keyed by
     * (type, min_salary) so re-running updates in place.
     *
     * Sources: SSS 2025 contribution schedule (employee share),
     * PhilHealth 2025 (5% with 10k floor / 100k ceiling),
     * Pag-IBIG 2025 (1%/2% capped at 100),
     * BIR revised withholding tax table, semi-monthly.
     */
    public function run(): void
    {
        $brackets = [];

        // --- SSS: employee share, monthly compensation brackets ---
        $brackets[] = ['type' => 'sss', 'min' => 0, 'max' => 4250, 'rate' => 0, 'fixed' => 180.00];
        for ($min = 4250; $min < 29750; $min += 500) {
            $brackets[] = [
                'type' => 'sss',
                'min' => $min,
                'max' => $min + 500,
                'rate' => 0,
                'fixed' => round(202.50 + (($min - 4250) / 500) * 22.50, 2),
            ];
        }
        $brackets[] = ['type' => 'sss', 'min' => 29750, 'max' => null, 'rate' => 0, 'fixed' => 1350.00];

        // --- PhilHealth: 5% of monthly basic salary, floored and capped ---
        $brackets[] = ['type' => 'philhealth', 'min' => 0, 'max' => 10000, 'rate' => 0, 'fixed' => 500.00];
        $brackets[] = ['type' => 'philhealth', 'min' => 10000, 'max' => 100000, 'rate' => 0.05, 'fixed' => 0.00];
        $brackets[] = ['type' => 'philhealth', 'min' => 100000, 'max' => null, 'rate' => 0, 'fixed' => 5000.00];

        // --- Pag-IBIG: 1% up to 1,500, then 2%, employee share capped at 100 ---
        $brackets[] = ['type' => 'pagibig', 'min' => 0, 'max' => 1500, 'rate' => 0.01, 'fixed' => 0.00];
        $brackets[] = ['type' => 'pagibig', 'min' => 1500, 'max' => 5000, 'rate' => 0.02, 'fixed' => 0.00];
        $brackets[] = ['type' => 'pagibig', 'min' => 5000, 'max' => null, 'rate' => 0, 'fixed' => 100.00];

        // --- Withholding tax: BIR semi-monthly table, marginal ("excess") rates ---
        $brackets[] = ['type' => 'withholding', 'min' => 0, 'max' => 2083, 'rate' => 0, 'fixed' => 0.00];
        $brackets[] = ['type' => 'withholding', 'min' => 2083, 'max' => 3333, 'rate' => 0.25, 'fixed' => 0.00];
        $brackets[] = ['type' => 'withholding', 'min' => 3333, 'max' => 5000, 'rate' => 0.25, 'fixed' => 312.50];
        $brackets[] = ['type' => 'withholding', 'min' => 5000, 'max' => 7917, 'rate' => 0.30, 'fixed' => 937.50];
        $brackets[] = ['type' => 'withholding', 'min' => 7917, 'max' => 12500, 'rate' => 0.35, 'fixed' => 1812.50];
        $brackets[] = ['type' => 'withholding', 'min' => 12500, 'max' => 22500, 'rate' => 0.40, 'fixed' => 3430.00];
        $brackets[] = ['type' => 'withholding', 'min' => 22500, 'max' => null, 'rate' => 0.45, 'fixed' => 7430.00];

        DB::transaction(function () use ($brackets) {
            foreach ($brackets as $bracket) {
                ContributionBracket::updateOrCreate(
                    ['type' => $bracket['type'], 'min_salary' => $bracket['min']],
                    [
                        'max_salary' => $bracket['max'],
                        'employee_rate' => $bracket['rate'],
                        'fixed_amount' => $bracket['fixed'],
                        'rate_applies_to' => $bracket['type'] === 'withholding' ? 'excess' : 'full',
                        'effective_year' => $bracket['type'] === 'withholding' ? 2023 : 2025,
                    ]
                );
            }
        });
    }
}
