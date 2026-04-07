<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
