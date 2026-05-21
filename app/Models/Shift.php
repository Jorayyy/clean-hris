<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'time_in',
        'time_out',
        'break_minutes',
        'grace_period',
        'color',
        'type',
        'is_active',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
