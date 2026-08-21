<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Night differential becomes a configurable rate instead of a hardcoded
     * zero, and audit_logs gets DB-level immutability insurance (MySQL only;
     * other drivers rely on application discipline).
     */
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->decimal('night_diff_rate', 5, 4)->default(0.10)->after('philhealth_rate');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER audit_logs_no_update BEFORE UPDATE ON audit_logs
                FOR EACH ROW BEGIN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only';
                END;

                CREATE TRIGGER audit_logs_no_delete BEFORE DELETE ON audit_logs
                FOR EACH ROW BEGIN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only';
                END;
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_no_update; DROP TRIGGER IF EXISTS audit_logs_no_delete;');
        }

        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn('night_diff_rate');
        });
    }
};
