<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'break1_out',
        'break1_in',
        'break2_out',
        'break2_in',
        'total_hours',
        'late_minutes',
        'undertime_minutes',
        'overtime_hours'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
