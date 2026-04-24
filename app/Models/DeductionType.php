<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];
}
