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
            $table->string('title')->nullable()->after('web_bundy_code');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('name_extension')->nullable()->after('last_name');
            $table->date('birthday')->nullable()->after('name_extension');
            $table->string('gender')->nullable()->after('birthday');
            $table->string('civil_status')->nullable()->after('gender');
            $table->string('place_of_birth')->nullable()->after('civil_status');
            $table->string('blood_type')->nullable()->after('place_of_birth');
            $table->string('citizenship')->nullable()->after('blood_type');
            $table->string('religion')->nullable()->after('citizenship');
            $table->string('photo')->nullable()->after('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'middle_name', 'name_extension', 'birthday', 'gender', 
                'civil_status', 'place_of_birth', 'blood_type', 'citizenship', 
                'religion', 'photo'
            ]);
        });
    }
};
