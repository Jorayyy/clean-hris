<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    public function created($model)
    {
        $this->log($model, 'created');
    }

    public function updated($model)
    {
        $action = 'updated';
        $description = null;

        if ($model instanceof \App\Models\Payroll && $model->wasChanged('status') && $model->status === 'approved') {
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

        // Security: Mask sensitive fields
        $sensitiveFields = ['password', 'remember_token', 'web_bundy_code', 'plain_password', 'otp_secret'];
        
        if ($oldData) {
            foreach ($sensitiveFields as $field) {
                if (isset($oldData[$field])) $oldData[$field] = '********';
            }
        }
        
        if ($newData) {
            foreach ($sensitiveFields as $field) {
                if (isset($newData[$field])) $newData[$field] = '********';
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
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
