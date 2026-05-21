<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'time_in', 
        'time_out', 
        'days', 
        'payroll_group_id', 
        'schedule_group_id', 
        'employee_id', 
        'is_template',
        'shift_id',
        'custom_shift_id',
        'schedule_date',
        'assigned_by',
        'remarks'
    ];

    protected $casts = [
        'days' => 'array',
        'is_template' => 'boolean',
        'schedule_date' => 'date',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function customShift()
    {
        return $this->belongsTo(CustomShift::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

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
