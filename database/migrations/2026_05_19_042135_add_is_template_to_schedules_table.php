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
        Schema::table('schedules', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('days');
            $table->string('days')->nullable()->change();
            $table->foreignId('payroll_group_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('is_template');
        });
    }
};
