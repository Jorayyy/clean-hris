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
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('snapshot_daily_rate', 10, 2)->after('employee_id')->nullable();
            $table->string('snapshot_position')->after('snapshot_daily_rate')->nullable();
            $table->string('snapshot_group')->after('snapshot_position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['snapshot_daily_rate', 'snapshot_position', 'snapshot_group']);
        });
    }
};
