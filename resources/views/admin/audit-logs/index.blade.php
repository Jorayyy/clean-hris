@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold">System Audit Logs</h4>
        <p class="text-muted small">Track every administrative action, update, and deletion in real-time.</p>
    </div>
    <div class="col-md-4 text-end">
        @if(auth()->user()->is_super_admin)
            <form action="{{ route('admin.audit-logs.prune') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to prune logs older than 30 days? This action cannot be undone.')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm no-print me-1">
                    <i class="bi bi-trash me-1"></i>Prune Old Logs
                </button>
            </form>
        @endif
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
                        <td class="text-wrap">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-start border-4 {{ str_contains(strtolower($log->action), 'delete') ? 'border-danger' : (str_contains(strtolower($log->action), 'approve') ? 'border-primary' : 'border-secondary') }} w-100 text-start d-flex justify-content-between align-items-center shadow-sm py-2 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <span class="text-truncate me-2">
                                        @if(is_array($log->details))
                                            @if(isset($log->details['description']))
                                                {{ $log->details['description'] }}
                                            @elseif($log->action == 'updated')
                                                <i class="bi bi-pencil-square me-1"></i> Updated {{ count($log->details['new'] ?? []) }} fields
                                            @elseif($log->action == 'created')
                                                <i class="bi bi-plus-circle me-1"></i> Created new record
                                            @else
                                                Action performed on {{ str_replace('App\\Models\\', '', $log->model_type) }}
                                            @endif
                                        @else
                                            {{ $log->details }}
                                        @endif
                                        @if($log->model_id)
                                            <span class="badge bg-white text-dark border ms-1" style="font-size: 9px;">ID: #{{ $log->model_id }}</span>
                                        @endif
                                    </span>
                                    <i class="bi bi-chevron-down small text-muted"></i>
                                </button>
                                <div class="dropdown-menu shadow-lg border-0 p-3 mt-1" style="min-width: 400px; max-height: 400px; overflow-y: auto;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold small text-uppercase">Full Change Details</h6>
                                        <span class="badge bg-light text-dark border small fw-normal">ID #{{ $log->model_id ?? 'N/A' }}</span>
                                    </div>
                                    <hr class="my-2 opacity-25">
                                    
                                    @if(is_array($log->details))
                                        @if(isset($log->details['old']) || isset($log->details['new']))
                                            @php
                                                $newKeys = array_keys($log->details['new'] ?? []);
                                                $oldKeys = array_keys($log->details['old'] ?? []);
                                                $allKeys = array_unique(array_merge($newKeys, $oldKeys));
                                            @endphp
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0" style="font-size: 11px;">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Field</th>
                                                            <th class="text-danger">Old Value</th>
                                                            <th class="text-success">New Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($allKeys as $key)
                                                            @php
                                                                $old = $log->details['old'][$key] ?? null;
                                                                $new = $log->details['new'][$key] ?? null;
                                                                // Skip sensitive fields like passwords if they exist in logs
                                                                if(in_array($key, ['password', 'remember_token'])) continue;
                                                            @endphp
                                                            <tr>
                                                                <td class="fw-bold bg-light py-1">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                                <td class="text-muted py-1" style="word-break: break-all;">{{ is_array($old) ? json_encode($old) : ($old ?: '-') }}</td>
                                                                <td class="fw-medium py-1" style="word-break: break-all;">{{ is_array($new) ? json_encode($new) : ($new ?: '-') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <pre class="bg-light p-2 rounded small mb-0" style="font-size: 10px; white-space: pre-wrap;">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    @else
                                        <p class="small mb-0">{{ $log->details }}</p>
                                    @endif

                                    <div class="mt-3 text-end">
                                        <small class="text-muted italic" style="font-size: 10px;">Log generated via {{ $log->ip_address }}</small>
                                    </div>
                                </div>
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
