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
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'details' => [
                'description' => $customDescription,
                'old' => $action === 'updated' || $action === 'approved' ? $model->getOriginal() : null,
                'new' => $action !== 'deleted' ? $model->getAttributes() : null,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
