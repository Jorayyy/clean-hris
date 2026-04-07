<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id', 'employee_id', 'total_days', 'total_hours', 'basic_pay', 'overtime_pay',
        'night_diff', 'bonuses', 'deductions_sss', 'deductions_pagibig', 'deductions_philhealth',
        'other_deductions', 'net_pay'
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
