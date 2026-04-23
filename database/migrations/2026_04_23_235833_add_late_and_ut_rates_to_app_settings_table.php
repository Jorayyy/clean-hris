<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->decimal('late_rate', 8, 4)->default(1.0000)->after('philhealth_rate')->comment('Multiplier for late deductions. 1.0 = 100% of hourly rate');
            $table->decimal('undertime_rate', 8, 4)->default(1.0000)->after('late_rate')->comment('Multiplier for undertime deductions. 1.0 = 100% of hourly rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['late_rate', 'undertime_rate']);
        });
    }
};
