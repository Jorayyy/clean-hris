<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'site_id', 'schedule_group_id', 'web_bundy_code', 'registered_ip', 'first_name', 'last_name', 'email', 'position', 'daily_rate', 'status', 'payroll_group_id',
        'title', 'middle_name', 'name_extension', 'birthday', 'gender', 'civil_status', 'place_of_birth', 'blood_type', 'citizenship', 'religion', 'photo',
        'company', 'location', 'employment_type', 'classification', 'level', 'date_employed', 'tax_code', 'pay_type', 'report_to',
        'bank_name', 'account_no', 'rcbc_no', 'palawan_pay_no', 'tin_no', 'sss_no', 'pagibig_no', 'philhealth_no',
        'mobile_no_1', 'mobile_no_2', 'tel_no_1', 'tel_no_2', 'facebook_url', 'twitter_url', 'instagram_url',
        'permanent_address_brgy', 'permanent_address_province', 'present_address_brgy', 'present_address_province', 'other_information'
    ];

    protected $appends = ['full_name'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class);
    }

    public function scheduleGroup()
    {
        return $this->belongsTo(ScheduleGroup::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function lastAttendance()
    {
        return $this->hasOne(Attendance::class)->latestOfMany('date');
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
        // Check for specific individual schedule first (regardless of date, for simple display)
        $individual = $this->schedules()->whereNotNull('time_in')->first();
        if ($individual) return $individual;

        // Otherwise, use group schedule
        return $this->payrollGroup?->schedules()->first();
    }

    public function getScheduleForDate($date)
    {
        $dateStr = \Carbon\Carbon::parse($date)->toDateString();
        $dayName = \Carbon\Carbon::parse($date)->format('l');

        // PRIORITY 1: Individual Override for this date
        $manual = $this->schedules()->whereDate('schedule_date', $dateStr)->first();
        if ($manual) return $manual;

        // PRIORITY 1.5: Individual 7-Day Pattern (Matching day of week OR day name in 'days' array)
        $phpDayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $pattern = $this->schedules()
            ->whereNull('schedule_date')
            ->where(function($q) use ($phpDayOfWeek, $dayName) {
                $q->where('day_of_week', $phpDayOfWeek)
                  ->orWhereJsonContains('days', $dayName);
            })
            ->first();

        if ($pattern && !$pattern->is_rest_day) {
            // Load times from the linked shift if it exists
            if ($pattern->shift_id && $pattern->shift) {
                $pattern->time_in = $pattern->shift->time_in;
                $pattern->time_out = $pattern->shift->time_out;
            } elseif ($pattern->custom_shift_id && $pattern->customShift) {
                $pattern->time_in = $pattern->customShift->start_time;
                $pattern->time_out = $pattern->customShift->end_time;
            }
            return $pattern;
        }

        // PRIORITY 2: Schedule Group assigned to employee
        if ($this->schedule_group_id && $this->scheduleGroup) {
            $resolved = $this->resolveScheduleFromConfig($this->scheduleGroup->schedule_config[$dayName] ?? null);
            if ($resolved) return $resolved;
        }

        // PRIORITY 3: Site Schedule
        if ($this->site && $this->site->schedule_group_id && $this->site->scheduleGroup) {
            $resolved = $this->resolveScheduleFromConfig($this->site->scheduleGroup->schedule_config[$dayName] ?? null);
            if ($resolved) return $resolved;
        }

        // Fallback
        return null;
    }

    private function resolveScheduleFromConfig($config)
    {
        if (!$config || $config === 'OFF') return null;
        
        $isRestDay = is_array($config) && ($config['is_rest_day'] ?? false);
        if ($isRestDay) return null;

        $id = is_array($config) ? ($config['id'] ?? null) : $config;
        if (!$id) return null;

        // Try Shift first
        $shift = \App\Models\Shift::find($id);
        if ($shift) {
            $temp = new \App\Models\Schedule();
            $temp->time_in = $shift->time_in;
            $temp->time_out = $shift->time_out;
            return $temp;
        }

        // Try CustomShift
        $custom = \App\Models\CustomShift::find($id);
        if ($custom) {
            $temp = new \App\Models\Schedule();
            $temp->time_in = $custom->start_time;
            $temp->time_out = $custom->end_time;
            return $temp;
        }

        // Fallback to Schedule (Legacy)
        return \App\Models\Schedule::find($id);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->name_extension}");
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }
}
