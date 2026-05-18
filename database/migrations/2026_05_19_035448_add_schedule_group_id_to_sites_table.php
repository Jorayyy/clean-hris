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
        Schema::table('sites', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->foreignId('schedule_group_id')->nullable()->constrained('schedule_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropForeign(['schedule_group_id']);
            $table->dropColumn('schedule_group_id');
        });
    }
};
