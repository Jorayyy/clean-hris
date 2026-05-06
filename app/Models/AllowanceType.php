<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowanceType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'default_amount',
        'type',
        'is_taxable',
        'is_active',
    ];
}
