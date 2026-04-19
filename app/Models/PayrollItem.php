<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id', 'employee_id', 'total_days', 'total_hours', 'basic_pay', 'overtime_pay',
        'night_diff', 'bonuses', 'deductions_json', 'net_pay'
    ];

    protected $casts = [
        'deductions_json' => 'array',
        'custom_deductions' => 'array',
        'deduction_data' => 'array',
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
