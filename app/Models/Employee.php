<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'web_bundy_code', 'first_name', 'last_name', 'email', 'position', 'daily_rate', 'status', 'payroll_group_id'
    ];

    protected $appends = ['full_name'];

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function getActiveScheduleAttribute()
    {
        // Check for specific individual schedule first
        $individual = $this->schedules()->first();
        if ($individual) return $individual;

        // Otherwise, use group schedule
        return $this->payrollGroup?->schedules()->first();
    }
}
