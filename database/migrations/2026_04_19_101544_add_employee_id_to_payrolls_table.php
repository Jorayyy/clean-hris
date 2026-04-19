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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained()->after('payroll_group_id');
            $table->foreignId('payroll_group_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
            $table->foreignId('payroll_group_id')->nullable(false)->change();
        });
    }
};
