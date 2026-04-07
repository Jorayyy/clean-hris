<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'web_bundy_code', 'registered_ip', 'first_name', 'last_name', 'email', 'position', 'daily_rate', 'status', 'payroll_group_id',
        'title', 'middle_name', 'name_extension', 'birthday', 'gender', 'civil_status', 'place_of_birth', 'blood_type', 'citizenship', 'religion', 'photo',
        'company', 'location', 'employment_type', 'classification', 'date_employed', 'tax_code', 'pay_type', 'report_to',
        'bank_name', 'account_no', 'tin_no', 'sss_no', 'pagibig_no', 'philhealth_no',
        'mobile_no_1', 'mobile_no_2', 'tel_no_1', 'tel_no_2', 'facebook_url', 'twitter_url', 'instagram_url',
        'permanent_address_brgy', 'permanent_address_province', 'present_address_brgy', 'present_address_province', 'other_information'
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
