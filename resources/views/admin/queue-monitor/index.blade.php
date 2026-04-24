@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold text-dark">System Operations Monitor</h4>
        <p class="text-muted small">Real-time overview of background tasks, HR activity, and system health.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- HRIS Quick Stats -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Active Force</h6>
                <h3 class="fw-bold mb-1">{{ $stats['active_employees'] }} / {{ $stats['total_employees'] }}</h3>
                <p class="text-muted small mb-0">Total Employees</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">DTR Logs Today</h6>
                <h3 class="fw-bold mb-1">{{ $stats['dtr_today'] }}</h3>
                <p class="text-muted small mb-0">Attendance Entries</p>
            </div>
        </div>
    </div>

    <!-- Active Background Tasks -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Queue Engine</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-1 me-2">{{ $pendingJobs }}</h3>
                    @if($pendingJobs > 0)
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    @endif
                </div>
                <p class="text-muted small mb-0">Pending Tasks</p>
            </div>
        </div>
    </div>

    <!-- Security/Audit Last Entry -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Last Activity</h6>
                <h3 class="fw-bold mb-1" style="font-size: 1.25rem;">{{ $stats['last_audit'] }}</h3>
                <p class="text-muted small mb-0">Audit Log Heartbeat</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent System Activity (Audit Logs) -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>Recent System Activity</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-3">User</th>
                                <th>Action</th>
                                <th>Object</th>
                                <th class="text-end pe-3">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $log)
                            <tr>
                                <td class="ps-3 border-0">
                                    <div class="fw-bold">{{ $log->user->name ?? 'System' }}</div>
                                </td>
                                <td class="border-0">
                                    <span class="badge @if($log->action == 'created') bg-success @elseif($log->action == 'deleted') bg-danger @else bg-info @endif bg-opacity-10 @if($log->action == 'created') text-success @elseif($log->action == 'deleted') text-danger @else text-info @endif px-2 py-1">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>
                                <td class="border-0 text-muted">
                                    {{ str_replace('App\Models\\', '', $log->model_type) }}
                                </td>
                                <td class="text-end pe-3 border-0 text-muted small">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No recent activity recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 text-center py-3">
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-light px-3">View Full Audit Log</a>
            </div>
        </div>
    </div>

    <!-- Failed Jobs & Service Health -->
    <div class="col-md-5">
        <!-- Service Monitor -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Service Health</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="bi bi-database me-2"></i>MySQL Database</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Online</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="bi bi-folder-check me-2"></i>Storage Engine</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Writable</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-cpu-fill me-2"></i>Queue Worker</span>
                    <span class="badge bg-{{ $pendingJobs > 10 ? 'warning' : 'info' }} bg-opacity-10 text-{{ $pendingJobs > 10 ? 'warning' : 'info' }} rounded-pill px-3">
                        @if($pendingJobs > 0) Processing @else Standing By @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Failed Tasks -->
        <div class="card border-0 shadow-sm border-top border-4 border-danger">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-exclamation-octagon me-2"></i>Queue Failures</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($failedList as $job)
                    <div class="list-group-item border-0 px-3 py-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark small">{{ $job['display_name'] }}</span>
                            <span class="text-muted smaller" style="font-size: 10px;">{{ \Carbon\Carbon::parse($job['failed_at'])->diffForHumans() }}</span>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded small text-truncate" style="font-family: monospace; font-size: 10px;">
                            {{ $job['exception'] }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check2-circle h4 d-block text-success"></i>
                        <span class="small">No active failures</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.animate-pulse {
    animation: fadePulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.spin-slow {
    animation: spin 3s linear infinite;
    display: inline-block;
}

@keyframes fadePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .7; }
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

