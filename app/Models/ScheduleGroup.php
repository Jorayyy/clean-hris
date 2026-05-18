<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleGroup extends Model
{
    protected $fillable = ['name', 'description', 'schedule_config'];

    protected $casts = [
        'schedule_config' => 'array'
    ];

    public function sites()
    {
        return $this->hasMany(Site::class, 'schedule_group_id');
    }
}
