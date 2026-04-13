<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueMonitorController extends Controller
{
    public function index()
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->latest()->take(10)->get();
        
        // Detailed failed jobs with error info
        $failedList = $failedJobs->map(function ($job) {
            $payload = json_decode($job->payload, true);
            return [
                'id' => $job->id,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'display_name' => $payload['displayName'] ?? 'Unknown Job',
                'failed_at' => $job->failed_at,
                'exception' => substr($job->exception, 0, 150) . '...'
            ];
        });

        return view('admin.queue-monitor.index', compact('pendingJobs', 'failedList'));
    }
}
