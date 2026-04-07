@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold">My Support Tickets</h3>
            <p class="text-muted small">Submit and track your payroll or DTR concerns.</p>
        </div>
        <a href="{{ route('employee.tickets.create') }}" class="btn btn-primary d-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> Submit New Ticket
        </a>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket ID</th>
                                <th>Subject/Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td class="fw-bold">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $ticket->subject }}</div>
                                        <small class="text-muted text-uppercase fw-bold">{{ $ticket->type }}</small>
                                    </td>
                                    <td>
                                        @if($ticket->priority == 'high') <span class="badge bg-danger">HIGH</span>
                                        @elseif($ticket->priority == 'normal') <span class="badge bg-info">NORMAL</span>
                                        @else <span class="badge bg-secondary">LOW</span> @endif
                                    </td>
                                    <td>
                                        @if($ticket->status == 'pending') <span class="badge rounded-pill bg-warning text-dark">PENDING</span>
                                        @elseif($ticket->status == 'ongoing') <span class="badge rounded-pill bg-primary">ONGOING</span>
                                        @elseif($ticket->status == 'resolved') <span class="badge rounded-pill bg-success">RESOLVED</span>
                                        @else <span class="badge rounded-pill bg-secondary text-white">CLOSED</span> @endif
                                    </td>
                                    <td class="small">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('employee.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-dark">View Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No tickets found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
