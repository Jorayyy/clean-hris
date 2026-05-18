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
        Schema::create('other_addition_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('allowance_type_id')->constrained('allowance_types')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('payroll_period_start')->nullable();
            $table->date('payroll_period_end')->nullable();
            $table->string('status')->default('pending'); // pending, applied
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_addition_enrollments');
    }
};
