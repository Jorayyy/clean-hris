<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce at the DB layer what app code previously enforced alone:
     * one attendance row per employee per day, and one payroll item per
     * employee per payroll batch. Existing duplicates are collapsed to the
     * newest row before the constraints are added.
     */
    public function up(): void
    {
        $this->collapseDuplicates('attendances', ['employee_id', 'date']);
        $this->collapseDuplicates('payroll_items', ['payroll_id', 'employee_id']);

        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasIndex('attendances', 'attendances_employee_date_unique')) {
                $table->unique(['employee_id', 'date'], 'attendances_employee_date_unique');
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            if (!Schema::hasIndex('payroll_items', 'payroll_items_payroll_employee_unique')) {
                $table->unique(['payroll_id', 'employee_id'], 'payroll_items_payroll_employee_unique');
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            if (Schema::hasIndex('payroll_items', 'payroll_items_payroll_id_employee_id_index')) {
                $table->dropIndex('payroll_items_payroll_id_employee_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasIndex('attendances', 'attendances_employee_date_unique')) {
                $table->dropUnique('attendances_employee_date_unique');
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            if (Schema::hasIndex('payroll_items', 'payroll_items_payroll_employee_unique')) {
                $table->dropUnique('payroll_items_payroll_employee_unique');
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            if (!Schema::hasIndex('payroll_items', 'payroll_items_payroll_id_employee_id_index')) {
                $table->index(['payroll_id', 'employee_id']);
            }
        });
    }

    /**
     * Driver-agnostic dedup: keeps the highest id per key pair.
     */
    private function collapseDuplicates(string $table, array $keys): void
    {
        $duplicates = DB::table($table)
            ->select(array_merge($keys, [DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total')]))
            ->groupBy($keys)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $group) {
            $query = DB::table($table);

            foreach ($keys as $key) {
                $query->where($key, $group->{$key});
            }

            $ids = $query->where('id', '<', $group->keep_id)->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table($table)->whereIn('id', $ids)->delete();
            }
        }
    }
};
