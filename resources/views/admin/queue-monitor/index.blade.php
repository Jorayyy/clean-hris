@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold text-dark">System Health & Activity</h4>
        <p class="text-muted small">Real-time overview of system environment, database size, and administrative activity.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Environment Info -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Environment</h6>
                <h3 class="fw-bold mb-1">{{ $stats['app_env'] }}</h3>
                <p class="text-muted small mb-0">PHP {{ $stats['php_ver'] }}</p>
            </div>
        </div>
    </div>
    
    <!-- DB Size -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Database Size</h6>
                <h3 class="fw-bold mb-1">{{ $stats['db_size'] }}</h3>
                <p class="text-muted small mb-0">Storage Weight</p>
            </div>
        </div>
    </div>

    <!-- Active Force Quick -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Active Force</h6>
                <h3 class="fw-bold mb-1">{{ $stats['active_employees'] }}</h3>
                <p class="text-muted small mb-0">Total: {{ $stats['total_employees'] }}</p>
            </div>
        </div>
    </div>

    <!-- Audit Heartbeat -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body">
                <h6 class="text-muted small uppercase fw-bold">Last Activity</h6>
                <h3 class="fw-bold mb-1" style="font-size: 1.25rem;">{{ $stats['last_audit'] }}</h3>
                <p class="text-muted small mb-0">Audit Log Pulse</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Activity Logs -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>System-wide Audit Trail</h6>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-light border text-primary">View Full Logs</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-4">Action By</th>
                                <th>Operation</th>
                                <th>Affected Module</th>
                                <th>Changes</th>
                                <th class="text-end pe-4">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $log)
                            <tr>
                                <td class="ps-4 border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 11px;">
                                            {{ strtoupper(substr($log->user->name ?? 'SYS', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $log->user->name ?? 'System' }}</div>
                                            <div class="text-muted x-small" style="font-size: 11px;">IP: {{ $log->ip_address }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="border-0">
                                    <span class="badge @if($log->action == 'created') bg-success @elseif($log->action == 'deleted') bg-danger @else bg-info @endif bg-opacity-10 @if($log->action == 'created') text-success @elseif($log->action == 'deleted') text-danger @else text-info @endif px-2 py-1">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>
                                <td class="border-0">
                                    <span class="text-dark fw-medium">{{ str_replace('App\Models\\', '', $log->model_type) }}</span>
                                    <small class="text-muted">#{{ $log->model_id }}</small>
                                </td>
                                <td class="border-0">
                                    <div class="text-muted text-truncate" style="max-width: 300px;">
                                        @php
                                            $changes = is_array($log->changes) ? $log->changes : json_decode($log->changes ?? '[]', true);
                                            $keys = isset($changes['after']) ? array_keys($changes['after']) : [];
                                        @endphp
                                        @if($log->action == 'updated' && count($keys) > 0)
                                            Modified: {{ implode(', ', $keys) }}
                                        @elseif($log->action == 'created')
                                            New entry created
                                        @elseif($log->action == 'deleted')
                                            Record removed
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end pe-4 border-0 text-muted small">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No recent activity detected.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
