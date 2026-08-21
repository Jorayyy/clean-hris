<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContributionBracket extends Model
{
    protected $fillable = [
        'type',
        'min_salary',
        'max_salary',
        'employee_rate',
        'fixed_amount',
        'rate_applies_to',
        'effective_year',
    ];

    protected $casts = [
        'min_salary' => 'float',
        'max_salary' => 'float',
        'employee_rate' => 'float',
        'fixed_amount' => 'float',
    ];

    /**
     * Bracket-table lookup for a statutory deduction (§2.2). Falls back to
     * null when no brackets are seeded so callers can degrade to legacy
     * flat-rate settings instead of producing zero deductions.
     */
    public static function findFor(string $type, float $salary): ?self
    {
        return static::query()
            ->where('type', $type)
            ->where('min_salary', '<=', $salary)
            ->where(fn ($q) => $q->where('max_salary', '>=', $salary)->orWhereNull('max_salary'))
            ->orderByDesc('min_salary')
            ->first();
    }

    public function compute(float $salary): float
    {
        $base = $this->rate_applies_to === 'excess'
            ? max(0, $salary - $this->min_salary)
            : $salary;

        return round($this->fixed_amount + ($base * $this->employee_rate), 2);
    }
}
