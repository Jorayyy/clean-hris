<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id', 
        'sick_leave_total', 'sick_leave_used',
        'vacation_leave_total', 'vacation_leave_used',
        'sil_total', 'sil_used'
    ];
}
