@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Management Transactions</h4>
            <p class="text-muted small mb-0">Review Employee Complaints, Time-Keeping (TK) issues, and Payroll inquiries.</p>
        </div>
        <div class="btn-group shadow-sm rounded-pill overflow-hidden">
            <a href="{{ route('admin.tickets.index', ['status' => 'pending']) }}" class="btn {{ $status == 'pending' ? 'btn-warning' : 'btn-light border' }} btn-sm px-3">
                <i class="bi bi-clock me-1"></i> Pending
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'ongoing']) }}" class="btn {{ $status == 'ongoing' ? 'btn-primary' : 'btn-light border' }} btn-sm px-3">
                <i class="bi bi-play-circle me-1"></i> Ongoing
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'resolved']) }}" class="btn {{ $status == 'resolved' ? 'btn-success' : 'btn-light border' }} btn-sm px-3">
                <i class="bi bi-check-circle me-1"></i> Resolved
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'audit']) }}" class="btn {{ $status == 'audit' ? 'btn-dark' : 'btn-light border' }} btn-sm px-3">
                <i class="bi bi-shield-lock me-1"></i> Security Logs
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex align-items-center">
                <h5 class="card-title fw-bold mb-0 text-uppercase small text-muted"><i class="bi bi-list-ul me-2"></i>{{ strtoupper($status) }} RECORDS</h5>
            </div>
        </div>
        <div class="card-body p-0">
            @if($status === 'audit')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4">Admin</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark small">{{ $log->user->name ?? 'System' }}</div>
                                    <small class="text-muted x-small text-uppercase">{{ $log->user->role ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $log->action == 'DTR_DELETION' ? 'bg-danger' : 'bg-secondary' }} bg-opacity-10 {{ $log->action == 'DTR_DELETION' ? 'text-danger' : 'text-secondary' }} border x-small">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->action == 'DTR_DELETION' && is_array($log->details))
                                        <div class="text-dark small fw-bold">DTR Deleted: {{ $log->details['employee'] ?? 'Unknown' }}</div>
                                        <div class="text-muted x-small">Period: {{ $log->details['period'] ?? 'N/A' }}</div>
                                    @else
                                        <span class="text-muted small">Generic activity recorded</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $log->ip_address }}</td>
                                <td class="text-muted small">
                                    {{ $log->created_at->format('M d, Y') }}<br>
                                    <span class="x-small">{{ $log->created_at->format('h:i:s A') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No security logs recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4">Ticket</th>
                                <th>Employee</th>
                                <th>Subject & Category</th>
                                <th>Priority</th>
                                <th>Date Submitted</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-dark rounded-pill px-2 font-monospace">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold small" style="width:32px; height:32px; font-size: 10px;">
                                            {{ substr($ticket->employee->first_name, 0, 1) }}{{ substr($ticket->employee->last_name, 0, 1) }}
                                        </div>
                                        <div class="fw-bold text-dark small">{{ $ticket->employee->full_name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small mb-0">{{ $ticket->subject }}</div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 x-small text-uppercase">
                                        {{ $ticket->type }}
                                    </span>
                                </td>
                                <td>
                                    @if($ticket->priority == 'high') <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 x-small">HIGH</span>
                                    @elseif($ticket->priority == 'normal') <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 x-small">NORMAL</span>
                                    @else <span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 rounded-pill px-2 x-small">LOW</span> @endif
                                </td>
                                <td class="text-muted small">{{ $ticket->created_at->format('M d, Y') }}<br><span class="x-small">{{ $ticket->created_at->format('h:i A') }}</span></td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3">
                                        Open Case <i class="bi bi-chevron-right ms-1 x-small"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-secondary opacity-50">
                                        <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                                        <p class="mb-0">No {{ $status }} transactions found.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @php
            $paginationItems = ($status === 'audit') ? $logs : $tickets;
        @endphp
        @if($paginationItems->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $paginationItems->appends(request()->all())->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
    .font-monospace { letter-spacing: -0.5px; }
    .btn-white { background-color: #fff; color: #495057; }
    .btn-white:hover { background-color: #f8f9fa; color: #212529; }
    .table td { border-bottom: 1px solid #f8f9fa; }
</style>
@endsection
