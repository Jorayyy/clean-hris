<?php

namespace Tests\Unit;

use App\Models\ContributionBracket;
use App\Models\Holiday;
use App\Services\PayrollService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayrollRateLogicTest extends TestCase
{
    use RefreshDatabase;

    protected PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollService();
    }

    public function test_default_day_uses_standard_multipliers(): void
    {
        $multipliers = $this->service->resolvePayMultiplier('2026-08-04', false);

        $this->assertSame(['basic' => 1.0, 'ot' => 1.25], $multipliers);
    }

    public function test_regular_holiday_doubles_basic_pay(): void
    {
        $holiday = new Holiday(['type' => 'regular']);
        $multipliers = $this->service->resolvePayMultiplier('2026-08-21', false, $holiday);

        $this->assertSame(['basic' => 2.0, 'ot' => 2.6], $multipliers);
    }

    public function test_regular_holiday_on_rest_day_matches_blueprint_matrix(): void
    {
        $holiday = new Holiday(['type' => 'regular']);
        $multipliers = $this->service->resolvePayMultiplier('2026-08-23', true, $holiday);

        $this->assertSame(['basic' => 2.0, 'ot' => 2.6], $multipliers);
    }

    public function test_special_holiday_and_rest_day_tiers(): void
    {
        $special = new Holiday(['type' => 'special']);

        $this->assertSame(['basic' => 1.3, 'ot' => 1.69], $this->service->resolvePayMultiplier('2026-11-01', false, $special));
        $this->assertSame(['basic' => 1.5, 'ot' => 1.69], $this->service->resolvePayMultiplier('2026-11-01', true, $special));
        $this->assertSame(['basic' => 1.3, 'ot' => 1.69], $this->service->resolvePayMultiplier('2026-08-23', true));
    }

    public function test_night_diff_minutes_for_day_shift_is_zero(): void
    {
        $in = Carbon::parse('2026-08-20 08:00:00');
        $out = Carbon::parse('2026-08-20 17:00:00');

        $this->assertSame(0, $this->service->computeNightDiffMinutes($in, $out));
    }

    public function test_night_diff_minutes_for_graveyard_shift(): void
    {
        // 21:00 -> 05:00 crosses into the window at 22:00
        $in = Carbon::parse('2026-08-20 21:00:00');
        $out = Carbon::parse('2026-08-21 05:00:00');

        $this->assertSame(7 * 60, $this->service->computeNightDiffMinutes($in, $out));
    }

    public function test_night_diff_minutes_partial_overlap(): void
    {
        // Overtime ending at 23:30 earns 90 minutes inside the window
        $in = Carbon::parse('2026-08-20 08:00:00');
        $out = Carbon::parse('2026-08-20 23:30:00');

        $this->assertSame(90, $this->service->computeNightDiffMinutes($in, $out));
    }

    public function test_full_rate_bracket_applies_rate_to_whole_salary(): void
    {
        $bracket = new ContributionBracket([
            'min_salary' => 10000,
            'max_salary' => 100000,
            'employee_rate' => 0.05,
            'fixed_amount' => 0,
            'rate_applies_to' => 'full',
        ]);

        $this->assertEquals(750.0, $bracket->compute(15000));
    }

    public function test_excess_rate_bracket_applies_marginal_tax(): void
    {
        $bracket = new ContributionBracket([
            'min_salary' => 12500,
            'max_salary' => 22500,
            'employee_rate' => 0.40,
            'fixed_amount' => 3430,
            'rate_applies_to' => 'excess',
        ]);

        // 3430 + 40% of (15000 - 12500)
        $this->assertEquals(4430.0, $bracket->compute(15000));
    }

    public function test_seeded_sss_table_caps_at_top_bracket(): void
    {
        $this->seed(\Database\Seeders\ContributionBracketSeeder::class);

        $low = ContributionBracket::findFor('sss', 4000);
        $top = ContributionBracket::findFor('sss', 50000);

        $this->assertEquals(180.0, $low->compute(4000));
        $this->assertEquals(1350.0, $top->compute(50000));
    }
}
