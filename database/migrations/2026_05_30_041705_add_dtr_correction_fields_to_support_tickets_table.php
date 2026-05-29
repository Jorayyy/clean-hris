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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->date('correction_date')->nullable()->after('type');
            $table->dateTime('correction_time_in')->nullable()->after('correction_date');
            $table->dateTime('correction_time_out')->nullable()->after('correction_time_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['correction_date', 'correction_time_in', 'correction_time_out']);
        });
    }
};
