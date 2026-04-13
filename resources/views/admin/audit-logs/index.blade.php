@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold">System Audit Logs</h4>
        <p class="text-muted small">Track every administrative action, update, and deletion in real-time.</p>
    </div>
    <div class="col-md-4 text-end">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm no-print">
            <i class="bi bi-printer me-1"></i>Print Report
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-0">
    <div class="card-header bg-dark text-white py-3">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by user or description..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="approved" {{ request('action') == 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-dark small border-bottom">
                    <tr>
                        <th class="ps-3 py-3" style="width: 180px;">Date & Time</th>
                        <th style="width: 200px;">Administrator</th>
                        <th style="width: 120px;">Action Taken</th>
                        <th style="width: 150px;">Target Module</th>
                        <th>Specific Details</th>
                        <th class="text-end pe-3" style="width: 120px;">Origin IP</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($logs as $log)
                    <tr class="border-bottom-0">
                        <td class="ps-3 text-muted">
                            <div class="fw-bold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                            <div style="font-size: 11px;">{{ $log->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary text-white rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                                    {{ strtoupper(substr($log->user->name ?? 'SY', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $log->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match(strtolower($log->action)) {
                                    'created' => 'bg-success-subtle text-success border-success',
                                    'updated' => 'bg-info-subtle text-info border-info',
                                    'deleted' => 'bg-danger-subtle text-danger border-danger',
                                    'approved' => 'bg-primary-subtle text-primary border-primary',
                                    'error' => 'bg-warning-subtle text-warning border-warning',
                                    default => 'bg-light text-dark border-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} border px-2 py-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box-seam me-2 text-muted"></i>
                                <span class="fw-medium">{{ str_replace('App\\Models\\', '', $log->model_type) ?: 'System' }}</span>
                            </div>
                        </td>
                        <td class="text-wrap" style="max-width: 300px;">
                            <div class="text-dark bg-light p-2 rounded border-start border-4 {{ str_contains(strtolower($log->action), 'delete') ? 'border-danger' : (str_contains(strtolower($log->action), 'approve') ? 'border-primary' : 'border-secondary') }}">
                                @if(is_array($log->details))
                                    @if(isset($log->details['description']))
                                        {{ $log->details['description'] }}
                                    @elseif($log->action == 'updated')
                                        Updated {{ count($log->details['new'] ?? []) }} fields
                                    @elseif($log->action == 'created')
                                        Created new record
                                    @else
                                        Action performed on {{ str_replace('App\\Models\\', '', $log->model_type) }}
                                    @endif
                                @else
                                    {{ $log->details }}
                                @endif

                                @if($log->model_id)
                                    <span class="badge bg-white text-dark border ms-1" style="font-size: 9px;">ID: #{{ $log->model_id }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end pe-3 monospace small text-muted">
                            <i class="bi bi-geo-alt small me-1"></i>{{ $log->ip_address }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-slash h1 d-block mb-3"></i>
                            No audit logs found for the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
