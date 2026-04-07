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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // e.g., "Regular Shift", "Night Shift"
            $table->time('time_in');
            $table->time('time_out');
            $table->string('days')->default('["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]'); // JSON for days
            
            // Link to either a Group or Individual
            $table->foreignId('payroll_group_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
