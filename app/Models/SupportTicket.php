<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'correction_date',
        'correction_time_in',
        'correction_time_out',
        'subject',
        'description',
        'status',
        'priority',
        'admin_reply'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
