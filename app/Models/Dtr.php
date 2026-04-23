<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dtr extends Model
{
    protected $fillable = [
        'employee_id', 'start_date', 'end_date', 'total_late_minutes', 
        'total_undertime_minutes', 'total_overtime_hours', 'total_regular_hours', 
        'total_absent_days', 'status', 'verified_by', 'finalized_by', 
        'verified_at', 'finalized_at', 'admin_notes', 'is_ot_authorized', 'ot_authorized_by',
        'total_night_diff_hours', 'total_holiday_hours', 'incentives', 
        'is_nd_authorized', 'is_holiday_authorized'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'verified_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }
    public function finalizer() { return $this->belongsTo(User::class, 'finalized_by'); }
}
