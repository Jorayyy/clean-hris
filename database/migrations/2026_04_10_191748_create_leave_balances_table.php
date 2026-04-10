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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('sick_leave_total', 5, 2)->default(10);
            $table->decimal('sick_leave_used', 5, 2)->default(0);
            $table->decimal('vacation_leave_total', 5, 2)->default(12);
            $table->decimal('vacation_leave_used', 5, 2)->default(0);
            $table->decimal('sil_total', 5, 2)->default(5);
            $table->decimal('sil_used', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
