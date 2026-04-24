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
        // 1. Queue Metrics
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->take(5)->get();
        
        $failedList = $failedJobs->map(function ($job) {
            $payload = json_decode($job->payload, true);
            return [
                'id' => $job->id,
                'queue' => $job->queue,
                'display_name' => str_replace('App\Jobs\\', '', $payload['displayName'] ?? 'Unknown'),
                'failed_at' => $job->failed_at,
                'exception' => substr($job->exception, 0, 100) . '...'
            ];
        });

        // 2. HRIS Specific Activity
        $recentActivity = AuditLog::with('user')->latest()->take(10)->get();
        
        // 3. System Stats
        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'dtr_today' => Dtr::whereDate('date', Carbon::today())->count(),
            'last_audit' => AuditLog::latest()->first()?->created_at?->diffForHumans() ?? 'Never',
        ];

        return view('admin.queue-monitor.index', compact('pendingJobs', 'failedList', 'recentActivity', 'stats'));
    }
}
