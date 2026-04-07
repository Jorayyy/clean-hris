@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold">Employee Support Ticketing</h3>
            <p class="text-muted small">Manage and respond to employee concerns and payroll inquiries.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tickets.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm shadow-sm d-flex align-items-center"><i class="bi bi-clock me-1"></i> Pending</a>
            <a href="{{ route('admin.tickets.index', ['status' => 'ongoing']) }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center"><i class="bi bi-person me-1"></i> Ongoing</a>
            <a href="{{ route('admin.tickets.index', ['status' => 'resolved']) }}" class="btn btn-success btn-sm shadow-sm d-flex align-items-center"><i class="bi bi-check-circle me-1"></i> Resolved</a>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold border-bottom pb-2">{{ ucfirst($status) }} Tickets List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket ID</th>
                                <th>Employee Name</th>
                                <th>Subject</th>
                                <th>Priority</th>
                                <th>Date Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td class="fw-bold">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $ticket->employee->full_name }}</div>
                                        <small class="text-muted text-uppercase fw-bold">{{ $ticket->id }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $ticket->subject }}</div>
                                        <small class="text-muted text-uppercase fw-bold">{{ $ticket->type }}</small>
                                    </td>
                                    <td>
                                        @if($ticket->priority == 'high') <span class="badge bg-danger">HIGH</span>
                                        @elseif($ticket->priority == 'normal') <span class="badge bg-info">NORMAL</span>
                                        @else <span class="badge bg-secondary">LOW</span> @endif
                                    </td>
                                    <td class="small">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-dark">Respond to Ticket</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No tickets found for currently selected status.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $tickets->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
