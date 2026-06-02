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
        // 1. Check for specific individual override for TODAY
        $today = \Carbon\Carbon::now()->toDateString();
        $manual = $this->schedules()->whereDate('schedule_date', $today)->first();
        if ($manual) return $manual;

        // 2. Check for individual pattern
        $dayName = \Carbon\Carbon::now()->format('l');
        $patterns = $this->schedules()->whereNull('schedule_date')->get();
        $pattern = $patterns->first(function($p) use ($dayName) {
            return $p->days && is_array($p->days) && in_array($dayName, $p->days);
        });
        if ($pattern) return $pattern;

        // 3. Use group schedule
        if ($this->schedule_group_id && $this->scheduleGroup) {
            $resolved = $this->resolveScheduleFromConfig($this->scheduleGroup->schedule_config[$dayName] ?? $this->scheduleGroup->schedule_config[strtoupper($dayName)] ?? null);
            if ($resolved) return $resolved;
        }

        // 4. Use site schedule
        if ($this->site && $this->site->schedule_group_id && $this->site->scheduleGroup) {
            $resolved = $this->resolveScheduleFromConfig($this->site->scheduleGroup->schedule_config[$dayName] ?? $this->site->scheduleGroup->schedule_config[strtoupper($dayName)] ?? null);
            if ($resolved) return $resolved;
        }

        return null;
    }

    public function getScheduleForDate($date)
    {
        $dateStr = \Carbon\Carbon::parse($date)->toDateString();
        $dayName = \Carbon\Carbon::parse($date)->format('l');
        $phpDayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek; // 0 (Sun) to 6 (Sat)

        // PRIORITY 1: Individual Override for this date (Manual Plotting)
        $manual = $this->schedules()->whereDate('schedule_date', $dateStr)->first();
        if ($manual) {
            // Even if it's a rest day, return it so we know it's a rest day
            return $manual;
        }

        // PRIORITY 1.5: Individual 7-Day Pattern
        $patterns = $this->schedules()->whereNull('schedule_date')->get();
        
        $pattern = $patterns->first(function($p) use ($phpDayOfWeek, $dayName) {
            if ($p->day_of_week !== null && (int)$p->day_of_week === $phpDayOfWeek) return true;
            if ($p->days && is_array($p->days)) return in_array($dayName, $p->days);
            return false;
        });

        if ($pattern) {
            if (!$pattern->is_rest_day) {
                if ($pattern->shift_id && $pattern->shift) {
                    $pattern->time_in = $pattern->shift->time_in;
                    $pattern->time_out = $pattern->shift->time_out;
                    $pattern->name = $pattern->shift->name;
                } elseif ($pattern->custom_shift_id && $pattern->customShift) {
                    $pattern->time_in = $pattern->customShift->start_time;
                    $pattern->time_out = $pattern->customShift->end_time;
                    $pattern->name = $pattern->customShift->title;
                }
            }
            return $pattern;
        }

        // PRIORITY 2: Schedule Group assigned to employee
        if ($this->schedule_group_id && $this->scheduleGroup) {
            // Try both Title Case and UPPERCASE for day names to handle different DB formats
            $config = $this->scheduleGroup->schedule_config[$dayName] ?? $this->scheduleGroup->schedule_config[strtoupper($dayName)] ?? null;
            $resolved = $this->resolveScheduleFromConfig($config);
            if ($resolved) return $resolved;
        }

        // PRIORITY 3: Site Schedule
        if ($this->site && $this->site->schedule_group_id && $this->site->scheduleGroup) {
            $config = $this->site->scheduleGroup->schedule_config[$dayName] ?? $this->site->scheduleGroup->schedule_config[strtoupper($dayName)] ?? null;
            $resolved = $this->resolveScheduleFromConfig($config);
            if ($resolved) return $resolved;
        }

        return null;
    }

    private function resolveScheduleFromConfig($config)
    {
        if (!$config || $config === 'OFF') return null;
        
        $isRestDay = is_array($config) && ($config['is_rest_day'] ?? false);
        if ($isRestDay) {
            $temp = new \App\Models\Schedule();
            $temp->is_rest_day = true;
            return $temp;
        }

        $id = is_array($config) ? ($config['id'] ?? null) : $config;
        if (!$id) return null;

        // Try Shift first
        $shift = \App\Models\Shift::find($id);
        if ($shift) {
            $temp = new \App\Models\Schedule();
            $temp->time_in = $shift->time_in;
            $temp->time_out = $shift->time_out;
            $temp->name = $shift->name;
            return $temp;
        }

        // Try CustomShift
        $custom = \App\Models\CustomShift::find($id);
        if ($custom) {
            $temp = new \App\Models\Schedule();
            $temp->time_in = $custom->start_time;
            $temp->time_out = $custom->end_time;
            $temp->name = $custom->title;
            return $temp;
        }

        // Fallback to Schedule (Legacy)
        $legacy = \App\Models\Schedule::find($id);
        if ($legacy) {
            // Ensure values are copied if it's acting as a template
            $temp = new \App\Models\Schedule();
            $temp->time_in = $legacy->time_in;
            $temp->time_out = $legacy->time_out;
            $temp->name = $legacy->name;
            return $temp;
        }

        return null;
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
