<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    /**
     * Global defaults. Models extend coverage by declaring a public static
     * $maskedAttributes array, which is merged in per event.
     */
    protected array $sensitiveFields = [
        'password',
        'remember_token',
        'web_bundy_code',
        'plain_password',
        'otp_secret',
        'dtr_password',
        'dtr_edit_password',
        'tin_no',
        'sss_no',
        'pagibig_no',
        'philhealth_no',
        'account_no',
        'rcbc_no',
        'palawan_pay_no',
        'bank_name',
    ];

    public function created($model)
    {
        $this->log($model, 'created');
    }

    public function updated($model)
    {
        $action = 'updated';
        $description = null;

        if ($model instanceof Payroll && $model->wasChanged('status') && $model->status === 'approved') {
            $action = 'approved';
            $description = "Approved payroll batch: {$model->payroll_code}";
        }

        $this->log($model, $action, $description);
    }

    public function deleted($model)
    {
        $this->log($model, 'deleted');
    }

    protected function log($model, $action, $customDescription = null)
    {
        $oldData = $action === 'updated' || $action === 'approved' ? $model->getOriginal() : null;
        $newData = $action !== 'deleted' ? $model->getAttributes() : null;

        // Security: mask sensitive fields (global defaults + model opt-in)
        $sensitiveFields = array_merge(
            $this->sensitiveFields,
            property_exists($model, 'maskedAttributes') ? ($model::$maskedAttributes ?? []) : []
        );

        if ($oldData) {
            foreach ($sensitiveFields as $field) {
                if (isset($oldData[$field])) {
                    $oldData[$field] = '********';
                }
            }
        }

        if ($newData) {
            foreach ($sensitiveFields as $field) {
                if (isset($newData[$field])) {
                    $newData[$field] = '********';
                }
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'details' => [
                'description' => $customDescription,
                'old' => $oldData,
                'new' => $newData,
                'payroll' => $this->payrollSnapshot($model),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Snapshot payroll financials into the audit entry itself so a full
     * "who changed net pay and when" trail survives later recalculation
     * of the mutable payroll_items rows.
     */
    protected function payrollSnapshot($model): ?array
    {
        if ($model instanceof PayrollItem) {
            return [
                'payroll_item_id' => $model->id,
                'payroll_id' => $model->payroll_id,
                'employee_id' => $model->employee_id,
                'basic_pay' => $model->basic_pay,
                'overtime_pay' => $model->overtime_pay,
                'night_diff' => $model->night_diff,
                'bonuses' => $model->bonuses,
                'withholding_tax' => $model->withholding_tax,
                'net_pay' => $model->net_pay,
            ];
        }

        if ($model instanceof Payroll) {
            return [
                'payroll_id' => $model->id,
                'status' => $model->status,
                'item_ids' => $model->items()->pluck('id')->all(),
            ];
        }

        return null;
    }
}
