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
            $table->decimal('sss_rate', 5, 4)->default(0.0450);
            $table->decimal('pagibig_rate', 5, 4)->default(0.0200);
            $table->decimal('philhealth_rate', 5, 4)->default(0.0500);
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['sss_rate', 'pagibig_rate', 'philhealth_rate']);
        });
    }
};
