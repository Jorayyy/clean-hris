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
        Schema::create('dtrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_late_minutes', 8, 2)->default(0);
            $table->decimal('total_undertime_minutes', 8, 2)->default(0);
            $table->decimal('total_overtime_hours', 8, 2)->default(0);
            $table->decimal('total_regular_hours', 8, 2)->default(0);
            $table->decimal('total_absent_days', 8, 2)->default(0);
            $table->enum('status', ['draft', 'verified', 'finalized'])->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->foreignId('finalized_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtrs');
    }
};
