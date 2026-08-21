<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    /**
     * Fields this model opts into masking before audit-log persistence.
     * Merged with the observer's global defaults by AuditObserver::log().
     */
    public static array $maskedAttributes = [
        'dtr_edit_password',
    ];

    protected $fillable = [
        'app_name', 
        'app_logo', 
        'dtr_edit_password', 
        'payroll_cut_off_start', 
        'payroll_cut_off_end',
        'sss_rate',
        'pagibig_rate',
        'philhealth_rate',
        'night_diff_rate',
        'late_rate',
        'undertime_rate'
    ];
}
