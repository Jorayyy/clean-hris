<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Dtr;
use Carbon\Carbon;

class QueueMonitorController extends Controller
{
    public function index()
    {
        // 1. Storage & Database Metrics
        $dbName = DB::connection()->getDatabaseName();
        $dbSize = 'N/A';
        try {
            if (config('database.default') === 'mysql') {
                $res = DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
                $dbSize = number_format($res[0]->size ?? 0, 2) . ' MB';
            }
        } catch (\Exception $e) {}

        // 2. Security Overview (Recent Failures or Logins)
        $recentActivity = AuditLog::with('user')->latest()->take(15)->get();
        
        // 3. User Statistics
        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'dtr_today' => Dtr::whereDate('created_at', Carbon::today())->count(),
            'last_audit' => AuditLog::latest()->first()?->created_at?->diffForHumans() ?? 'Never',
            'db_size' => $dbSize,
            'app_env' => ucfirst(config('app.env')),
            'php_ver' => PHP_VERSION,
        ];

        return view('admin.queue-monitor.index', compact('recentActivity', 'stats'));
    }
}
