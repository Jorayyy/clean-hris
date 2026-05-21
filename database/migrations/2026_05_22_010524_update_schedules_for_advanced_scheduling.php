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
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('shift_id')->after('days')->nullable()->constrained('shifts')->onDelete('set null');
            $table->foreignId('custom_shift_id')->after('shift_id')->nullable()->constrained('custom_shifts')->onDelete('set null');
            $table->date('schedule_date')->after('custom_shift_id')->nullable();
            $table->foreignId('assigned_by')->after('schedule_date')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->after('assigned_by')->nullable();
            
            // Allow time fields to be nullable if using shift_id
            $table->time('time_in')->nullable()->change();
            $table->time('time_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['custom_shift_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['shift_id', 'custom_shift_id', 'schedule_date', 'assigned_by', 'remarks']);
            
            $table->time('time_in')->nullable(false)->change();
            $table->time('time_out')->nullable(false)->change();
        });
    }
};
