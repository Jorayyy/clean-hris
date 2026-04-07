<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'time_in', 'time_out', 'days', 'payroll_group_id', 'employee_id'];

    protected $casts = [
        'days' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class);
    }
}
