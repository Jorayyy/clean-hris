<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    public const STATUS_PENDING_EMPLOYEE = 'pending_employee';

    public const STATUS_PENDING_MANAGER = 'pending_manager_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'submitted_by',
        'approved_by',
        'flagged_reason',
        'employee_note',
        'old_values',
        'new_values',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'approved_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING_EMPLOYEE, self::STATUS_PENDING_MANAGER], true);
    }
}
