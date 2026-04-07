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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('company')->nullable()->after('religion');
            $table->string('location')->nullable()->after('company');
            $table->string('employment_type')->nullable()->after('location');
            $table->string('classification')->nullable()->after('employment_type');
            $table->date('date_employed')->nullable()->after('classification');
            $table->string('tax_code')->nullable()->after('date_employed');
            $table->string('pay_type')->nullable()->after('tax_code');
            $table->string('report_to')->nullable()->after('pay_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'company', 'location', 'employment_type', 'classification',
                'date_employed', 'tax_code', 'pay_type', 'report_to'
            ]);
        });
    }
};
