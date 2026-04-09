<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['app_name', 'app_logo', 'dtr_edit_password', 'payroll_cut_off_start', 'payroll_cut_off_end'];
}
