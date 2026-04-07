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
            $table->string('web_bundy_code')->nullable()->after('app_name');
        });

        // Seed initial code if none exists
        DB::table('app_settings')->where('id', 1)->update(['web_bundy_code' => '1234']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn('web_bundy_code');
        });
    }
};
