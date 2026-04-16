<?php

namespace App\Policies;

use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PayrollItem $payrollItem): bool
    {
        if ($user->role === 'admin' || $user->role === 'super-admin') {
            return true;
        }
        return $user->role === 'employee' && $user->employee_id === $payrollItem->employee_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PayrollItem $payrollItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PayrollItem $payrollItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PayrollItem $payrollItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PayrollItem $payrollItem): bool
    {
        return false;
    }
}
