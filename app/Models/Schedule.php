<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'time_in', 'time_out', 'days', 'payroll_group_id', 'schedule_group_id', 'employee_id', 'is_template'];

    protected $casts = [
        'days' => 'array',
        'is_template' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class);
    }

    public function scheduleGroup()
    {
        return $this->belongsTo(ScheduleGroup::class);
    }
}
