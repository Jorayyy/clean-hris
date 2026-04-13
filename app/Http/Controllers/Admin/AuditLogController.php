<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $action = $request->action;
            $query->where(function($q) use ($action) {
                $q->where('action', $action);
                
                // Smart mapping for legacy or specific actions
                if ($action === 'deleted') {
                    $q->orWhere('action', 'like', '%DELETION%');
                }
                if ($action === 'updated') {
                    $q->orWhere('action', 'like', '%UPDATE%');
                }
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model_type', 'like', "%$search%")
                  ->orWhere('details', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%");
                  });
            });
        }

        $logs = $query->paginate(20)->withQueryString();
        return view('admin.audit-logs.index', compact('logs'));
    }
}
