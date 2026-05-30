<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id', 'employee_id', 'total_days', 'total_hours', 'basic_pay', 'overtime_pay',
        'night_diff', 'bonuses', 'allowances_json', 'deductions_json', 'net_pay',
        'deductions_sss', 'deductions_pagibig', 'deductions_philhealth', 'other_deductions'
    ];

    protected $casts = [
        'deductions_json' => 'array',
        'allowances_json' => 'array',
        'custom_deductions' => 'array',
        'deduction_data' => 'array',
    ];

    public function getTotalDeductionsAttribute()
    {
        $specific = ($this->deductions_sss ?? 0) + 
                    ($this->deductions_pagibig ?? 0) + 
                    ($this->deductions_philhealth ?? 0) + 
                    ($this->other_deductions ?? 0);

        if ($specific > 0) return $specific;

        if (is_array($this->deductions_json)) {
            return array_sum(array_column($this->deductions_json, 'amount'));
        }

        return 0;
    }

    public function getSssDeductionAttribute()
    {
        if (($this->deductions_sss ?? 0) > 0) return $this->deductions_sss;
        return $this->getDeductionFromBuffer('SSS');
    }

    public function getPagibigDeductionAttribute()
    {
        if (($this->deductions_pagibig ?? 0) > 0) return $this->deductions_pagibig;
        return $this->getDeductionFromBuffer('PAGIBIG');
    }

    public function getPhilhealthDeductionAttribute()
    {
        if (($this->deductions_philhealth ?? 0) > 0) return $this->deductions_philhealth;
        return $this->getDeductionFromBuffer('PHILHEALTH');
    }

    private function getDeductionFromBuffer($type)
    {
        if (!is_array($this->deductions_json)) return 0;
        foreach ($this->deductions_json as $d) {
            if (strtoupper($d['type']) === strtoupper($type)) return $d['amount'];
        }
        return 0;
    }

    public function getTotalEarningsAttribute()
    {
        return ($this->basic_pay ?? 0) + 
               ($this->overtime_pay ?? 0) + 
               ($this->bonuses ?? 0) + 
               ($this->night_diff ?? 0);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
