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
            $table->json('deductions_json')->nullable()->after('bonuses');
            
            // Setting existing specific columns to nullable if they aren't already
            $table->decimal('deductions_sss', 10, 2)->nullable()->change();
            $table->decimal('deductions_pagibig', 10, 2)->nullable()->change();
            $table->decimal('deductions_philhealth', 10, 2)->nullable()->change();
            $table->decimal('other_deductions', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn('deductions_json');
        });
    }
};
