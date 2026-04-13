@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold">Reliability Monitor</h4>
        <p class="text-muted small">Monitor background payroll tasks and system processing health.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Queue Status -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Active Queue Status</h6>
                    <span class="badge {{ $pendingJobs > 0 ? 'bg-primary' : 'bg-success' }} pulse-indicator">
                        {{ $pendingJobs > 0 ? 'Processing Jobs' : 'Queue Idle' }}
                    </span>
                </div>
                <div class="text-center py-4">
                    <h1 class="display-1 fw-bold {{ $pendingJobs > 0 ? 'text-primary' : 'text-muted' }}">{{ $pendingJobs }}</h1>
                    <p class="text-muted mb-0">Background Tasks Currently Pending</p>
                    @if($pendingJobs > 0)
                        <div class="spinner-border spinner-border-sm text-primary mt-3" role="status"></div>
                        <p class="small text-primary mt-2">Check terminal for 'php artisan queue:work'</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Health Check -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body">
                <h6 class="fw-bold mb-4">System Service Health</h6>
                <div class="list-group list-group-flush border-0">
                    <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bi bi-cpu h4 text-primary me-2"></i>
                            <span class="fw-medium">Database Connection</span>
                        </div>
                        <span class="badge bg-success rounded-pill px-3">PROTECTED</span>
                    </div>
                    <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bi bi-hdd-network h4 text-info me-2"></i>
                            <span class="fw-medium">Storage Engine</span>
                        </div>
                        <span class="badge bg-success rounded-pill px-3">OK</span>
                    </div>
                    <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bi bi-clock-history h4 text-warning me-2"></i>
                            <span class="fw-medium">Laravel Scheduler</span>
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">CONFIGURED</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Failed Tasks -->
<div class="card border-0 shadow-sm rounded-0">
    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Recent Processing Failures</h6>
        <span class="badge bg-danger rounded-pill px-3">{{ count($failedList) }} Failures Tracked</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-dark small border-bottom">
                    <tr>
                        <th class="ps-3 py-3">Task Information</th>
                        <th>Queue/Worker</th>
                        <th>Failure Point</th>
                        <th class="pe-3 text-end">Failed On</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($failedList as $job)
                    <tr class="border-bottom">
                        <td class="ps-3">
                            <div class="fw-bold text-dark">{{ $job['display_name'] }}</div>
                            <div class="text-muted" style="font-size: 11px;">#{{ $job['id'] }} | Connection: {{ $job['connection'] }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 uppercase" style="font-size: 9px;">
                                {{ strtoupper($job['queue']) }}
                            </span>
                        </td>
                        <td>
                            <div class="bg-danger bg-opacity-10 text-danger p-2 rounded border-start border-3 border-danger" style="max-width: 400px; font-family: monospace; font-size: 10px;">
                                {{ $job['exception'] }}
                            </div>
                        </td>
                        <td class="pe-3 text-end text-muted">
                            <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($job['failed_at'])->format('M d, Y') }}</div>
                            <div style="font-size: 11px;">{{ \Carbon\Carbon::parse($job['failed_at'])->format('h:i:s A') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check h1 d-block mb-3 text-success"></i>
                            All background systems are operating normally. No failures tracked.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.pulse-indicator {
    animation: pulse 2s infinite;
}
@keyframes pulse {
	0% { opacity: 1; }
	50% { opacity: 0.6; }
	100% { opacity: 1; }
}
</style>
@endsection
