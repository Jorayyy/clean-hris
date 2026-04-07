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
            // Account Information
            $table->string('bank_name')->nullable()->after('report_to');
            $table->string('account_no')->nullable()->after('bank_name');
            $table->string('tin_no')->nullable()->after('account_no');
            $table->string('sss_no')->nullable()->after('tin_no');
            $table->string('pagibig_no')->nullable()->after('sss_no');
            $table->string('philhealth_no')->nullable()->after('pagibig_no');

            // Contact Details
            $table->string('mobile_no_1')->nullable()->after('philhealth_no');
            $table->string('mobile_no_2')->nullable()->after('mobile_no_1');
            $table->string('tel_no_1')->nullable()->after('mobile_no_2');
            $table->string('tel_no_2')->nullable()->after('tel_no_1');
            $table->string('facebook_url')->nullable()->after('tel_no_2');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('instagram_url')->nullable()->after('twitter_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name', 'account_no', 'tin_no', 'sss_no', 'pagibig_no', 'philhealth_no',
                'mobile_no_1', 'mobile_no_2', 'tel_no_1', 'tel_no_2', 'facebook_url', 'twitter_url', 'instagram_url'
            ]);
        });
    }
};
