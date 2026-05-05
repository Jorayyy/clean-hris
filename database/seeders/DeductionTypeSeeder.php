<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeductionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'SSS', 'name' => 'SSS Contribution', 'description' => 'Social Security System standard deduction', 'is_active' => true],
            ['code' => 'PAGIBIG', 'name' => 'Pag-IBIG Contribution', 'description' => 'Home Development Mutual Fund standard deduction', 'is_active' => true],
            ['code' => 'PHILHEALTH', 'name' => 'PhilHealth Contribution', 'description' => 'Philippine Health Insurance Corporation standard deduction', 'is_active' => true],
            ['code' => 'LATE', 'name' => 'Late Penalty', 'description' => 'Automatic deduction based on late minutes', 'is_active' => true],
            ['code' => 'UT', 'name' => 'Undertime Penalty', 'description' => 'Automatic deduction based on undertime minutes', 'is_active' => true],
            ['code' => 'CA', 'name' => 'Cash Advance', 'description' => 'Salary loan or cash advance', 'is_active' => true],
            ['code' => 'LOAN_SSS', 'name' => 'SSS Loan', 'description' => 'Repayment of SSS Salary Loan', 'is_active' => true],
            ['code' => 'LOAN_PAGIBIG', 'name' => 'Pag-IBIG Loan', 'description' => 'Repayment of Pag-IBIG Multi-Purpose Loan', 'is_active' => true],
            ['code' => 'HMO_DEP', 'name' => 'HMO Dependent', 'description' => 'Additional HMO coverage for dependents', 'is_active' => true],
        ];

        foreach ($types as $type) {
            \App\Models\DeductionType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
