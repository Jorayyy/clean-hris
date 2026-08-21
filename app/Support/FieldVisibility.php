<?php

namespace App\Support;

use App\Models\User;

/**
 * Permission-driven field masking (§1.3). One choke point for scrubbing
 * sensitive attributes instead of scattering can() checks through views.
 * Permissions are data: "view {table}.{field}" / "edit {table}.{field}",
 * seeded per role by RoleAndPermissionSeeder.
 */
class FieldVisibility
{
    protected static array $sensitiveFields = [
        'employees' => ['daily_rate', 'bank_name', 'account_no', 'tin_no', 'sss_no', 'pagibig_no', 'philhealth_no'],
        'payroll_items' => ['basic_pay', 'net_pay', 'deductions_json'],
    ];

    public static function fieldsFor(string $table): array
    {
        return static::$sensitiveFields[$table] ?? [];
    }

    public static function scrub(string $table, array $attributes, User $user): array
    {
        foreach (static::fieldsFor($table) as $field) {
            if (! static::canView($table, $field, $user)) {
                unset($attributes[$field]);
            }
        }

        return $attributes;
    }

    public static function canView(string $table, string $field, User $user): bool
    {
        if (! in_array($field, static::fieldsFor($table), true)) {
            return true;
        }

        return $user->hasPermissionTo("view {$table}.{$field}");
    }

    public static function canEdit(string $table, string $field, User $user): bool
    {
        if (! in_array($field, static::fieldsFor($table), true)) {
            return true;
        }

        return $user->hasPermissionTo("edit {$table}.{$field}");
    }
}
