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
            // Permanent Address
            $table->text('permanent_address_brgy')->nullable()->after('instagram_url');
            $table->string('permanent_address_province')->nullable()->after('permanent_address_brgy');
            
            // Present Address
            $table->text('present_address_brgy')->nullable()->after('permanent_address_province');
            $table->string('present_address_province')->nullable()->after('present_address_brgy');
            
            // Other Information
            $table->text('other_information')->nullable()->after('present_address_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'permanent_address_brgy', 'permanent_address_province',
                'present_address_brgy', 'present_address_province',
                'other_information'
            ]);
        });
    }
};
