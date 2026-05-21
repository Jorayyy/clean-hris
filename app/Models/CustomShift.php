<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomShift extends Model
{
    protected $fillable = [
        'title',
        'date',
        'start_time',
        'end_time',
        'notes',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
