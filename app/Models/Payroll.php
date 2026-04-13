<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_code', 'start_date', 'end_date', 'pay_date', 'status', 'payroll_group_id'
    ];

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
