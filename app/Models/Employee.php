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

        // PRIORITY 1.5: Individual 7-Day Pattern (Matching day of week)
        $phpDayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek; // 0 (Sun) to 6 (Sat)
        // Convert to our model's logic if needed (Laravel's Carbon dayOfWeek is 0=Sun, 6=Sat)
        // The form sends 0=Mon, 6=Sun or 0=Sun? Let's assume matches 0-6.
        $pattern = $this->schedules()
            ->whereNull('schedule_date')
            ->where('day_of_week', $phpDayOfWeek)
            ->first();

        if ($pattern && !$pattern->is_rest_day) {
            // Load times from the linked shift if it exists
            if ($pattern->shift_id && $pattern->shift) {
                $pattern->time_in = $pattern->shift->time_in;
                $pattern->time_out = $pattern->shift->time_out;
            }
            return $pattern;
        }

        // PRIORITY 2: Schedule Group assigned to employee
        if ($this->schedule_group_id && $this->scheduleGroup) {
            $config = $this->scheduleGroup->schedule_config[$dayName] ?? null;
            if ($config) {
                if ($config === 'OFF' || (is_array($config) && ($config['is_rest_day'] ?? false))) return null;
                $schedId = is_array($config) ? ($config['id'] ?? null) : $config;
                
                // Try finding in Schedule first (for legacy)
                $sched = \App\Models\Schedule::find($schedId);
                if ($sched) return $sched;

                // Then try finding in Shift (which seems to be the case in your current configs)
                $shift = \App\Models\Shift::find($schedId);
                if ($shift) {
                    // Create a temporary schedule object to return
                    $temp = new \App\Models\Schedule();
                    $temp->time_in = $shift->time_in;
                    $temp->time_out = $shift->time_out;
                    return $temp;
                }
            }
        }

        // PRIORITY 3: Site Schedule
        if ($this->site && $this->site->schedule_group_id && $this->site->scheduleGroup) {
            $config = $this->site->scheduleGroup->schedule_config[$dayName] ?? null;
            if ($config) {
                if ($config === 'OFF' || (is_array($config) && ($config['is_rest_day'] ?? false))) return null;
                $schedId = is_array($config) ? ($config['id'] ?? null) : $config;
                return \App\Models\Schedule::find($schedId);
            }
        }

        // Fallback
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
