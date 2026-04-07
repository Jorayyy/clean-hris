<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('position');
            $table->decimal('daily_rate', 10, 2);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time_in');
            $table->time('time_out');
            $table->decimal('total_hours', 5, 2);
            $table->integer('late_minutes');
            $table->integer('undertime_minutes');
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_code')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('total_days');
            $table->decimal('total_hours', 8, 2);
            $table->decimal('basic_pay', 10, 2);
            $table->decimal('overtime_pay', 10, 2);
            $table->decimal('night_diff', 10, 2);
            $table->decimal('bonuses', 10, 2);
            $table->decimal('deductions_sss', 10, 2);
            $table->decimal('deductions_pagibig', 10, 2);
            $table->decimal('deductions_philhealth', 10, 2);
            $table->decimal('other_deductions', 10, 2);
            $table->decimal('net_pay', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
