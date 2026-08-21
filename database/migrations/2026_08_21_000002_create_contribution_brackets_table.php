<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bracket tables for statutory deductions (SSS / PhilHealth / Pag-IBIG)
     * and progressive withholding tax. Replaces the flat-rate settings so
     * statutory table changes are data updates, not code deploys.
     */
    public function up(): void
    {
        Schema::create('contribution_brackets', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['sss', 'philhealth', 'pagibig', 'withholding']);
            $table->decimal('min_salary', 12, 2)->default(0);
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->decimal('employee_rate', 8, 6)->default(0);
            $table->decimal('fixed_amount', 12, 2)->default(0);
            // 'full'  => fixed + rate * salary
            // 'excess'=> fixed + rate * (salary - min_salary)  [marginal/progressive]
            $table->enum('rate_applies_to', ['full', 'excess'])->default('full');
            $table->unsignedSmallInteger('effective_year')->nullable();
            $table->timestamps();

            $table->index(['type', 'min_salary'], 'contribution_brackets_type_min_salary_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_brackets');
    }
};
