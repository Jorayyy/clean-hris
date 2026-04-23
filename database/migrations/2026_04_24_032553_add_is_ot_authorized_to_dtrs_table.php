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
        Schema::table('dtrs', function (Blueprint $table) {
            $table->boolean('is_ot_authorized')->default(false)->after('total_overtime_hours');
            $table->unsignedBigInteger('ot_authorized_by')->nullable()->after('is_ot_authorized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtrs', function (Blueprint $table) {
            $table->dropColumn(['is_ot_authorized', 'ot_authorized_by']);
        });
    }
};
