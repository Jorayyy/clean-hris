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
        Schema::table('dtrs', function (Blueprint $table) {
            $table->decimal('total_night_diff_hours', 8, 2)->default(0)->after('total_overtime_hours');
            $table->decimal('total_holiday_hours', 8, 2)->default(0)->after('total_night_diff_hours');
            $table->decimal('incentives', 10, 2)->default(0)->after('total_holiday_hours')->comment('Solds/Spiffs');
            
            $table->boolean('is_nd_authorized')->default(false)->after('is_ot_authorized');
            $table->boolean('is_holiday_authorized')->default(false)->after('is_nd_authorized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtrs', function (Blueprint $table) {
            $table->dropColumn(['total_night_diff_hours', 'total_holiday_hours', 'incentives', 'is_nd_authorized', 'is_holiday_authorized']);
        });
    }
};
