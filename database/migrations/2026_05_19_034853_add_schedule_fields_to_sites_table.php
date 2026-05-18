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
        Schema::table('sites', function (Blueprint $table) {
            $table->json('schedule_config')->nullable(); // Stores day-to-day schedule selections
            $table->boolean('is_special_1_hour')->default(false);
            $table->boolean('is_present_policy')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['schedule_config', 'is_special_1_hour', 'is_present_policy']);
        });
    }
};
