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
            ['code' => 'CA', 'name' => 'Cash Advance', 'description' => 'Salary loan or cash advance'],
            ['code' => 'UNI', 'name' => 'Uniform', 'description' => 'Uniform deduction'],
            ['code' => 'HMO_DEP', 'name' => 'HMO Dependent', 'description' => 'Additional HMO coverage for dependents'],
            ['code' => 'TARDY', 'name' => 'Tardiness', 'description' => 'Deduction for being late'],
            ['code' => 'ABS', 'name' => 'Absences', 'description' => 'Deduction for unexplained absences'],
            ['code' => 'LOAN_SSS', 'name' => 'SSS Loan', 'description' => 'Repayment of SSS Salary Loan'],
            ['code' => 'LOAN_PAGIBIG', 'name' => 'Pag-IBIG Loan', 'description' => 'Repayment of Pag-IBIG Multi-Purpose Loan'],
            ['code' => 'OTHER', 'name' => 'Other Deductions', 'description' => 'Miscellaneous deductions'],
        ];

        foreach ($types as $type) {
            \App\Models\DeductionType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
