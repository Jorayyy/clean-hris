<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('payroll_group_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('payroll_group_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['payroll_group_id']);
            $table->dropColumn('payroll_group_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['payroll_group_id']);
            $table->dropColumn('payroll_group_id');
        });

        Schema::dropIfExists('payroll_groups');
    }
};
