<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleGroup extends Model
{
    protected $fillable = ['name', 'description', 'schedule_config', 'created_by', 'status'];

    protected $casts = [
        'schedule_config' => 'array'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sites()
    {
        return $this->hasMany(Site::class, 'schedule_group_id');
    }
}
