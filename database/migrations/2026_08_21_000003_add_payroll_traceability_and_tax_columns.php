<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Closes the traceability gap from the ERD review: each payroll line now
     * snapshots which DTR fed it, so a later DTR correction leaves a
     * traceable link. Also adds the missing withholding tax line.
     */
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->foreignId('dtr_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('dtrs')
                ->nullOnDelete();
            $table->decimal('withholding_tax', 10, 2)->default(0)->after('deductions_philhealth');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dtr_id');
            $table->dropColumn('withholding_tax');
        });
    }
};
