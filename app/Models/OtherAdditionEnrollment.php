<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherAdditionEnrollment extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_type_id',
        'amount',
        'payroll_period_start',
        'payroll_period_end',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function allowanceType()
    {
        return $this->belongsTo(AllowanceType::class);
    }
}
