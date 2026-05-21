<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'name', 
        'location', 
        'schedule_config', 
        'is_special_1_hour', 
        'is_present_policy',
        'schedule_group_id'
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'is_special_1_hour' => 'boolean',
        'is_present_policy' => 'boolean',
    ];

    public function scheduleGroup()
    {
        return $this->belongsTo(ScheduleGroup::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
