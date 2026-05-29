@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-10">
        {{-- Breadcrumb/Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Ticket #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Support Center</a></li>
                        <li class="breadcrumb-item active">{{ $ticket->type }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i> Back to List
            </a>
        </div>

        <div class="row g-4">
            {{-- Left Column: Ticket Details & Message --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-person-fill fs-3"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h5 class="fw-bold mb-0 text-dark">{{ $ticket->employee->full_name }}</h5>
                                <span class="text-muted small">Employee ID: {{ $ticket->employee->employee_id }} • {{ $ticket->employee->position }}</span>
                            </div>
                            <div class="ms-auto">
                                @php
                                    $priorityColors = ['low' => 'success', 'normal' => 'primary', 'high' => 'danger'];
                                    $pColor = $priorityColors[$ticket->priority] ?? 'primary';
                                @endphp
                                <span class="badge bg-{{ $pColor }} rounded-pill px-3">{{ strtoupper($ticket->priority) }} PRIORITY</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-muted text-uppercase small mb-2">Subject</h6>
                            <p class="fs-5 fw-bold text-dark">{{ $ticket->subject }}</p>
                        </div>

                        <div class="bg-light rounded-4 p-4 mb-4">
                            <h6 class="fw-bold text-muted text-uppercase small mb-3">Employee Message</h6>
                            <p class="mb-0 text-dark lead" style="white-space: pre-line;">{{ $ticket->description }}</p>
                        </div>

                        @if($ticket->type === 'DTR Correction' && $ticket->correction_date)
                        <div class="border rounded-4 overflow-hidden mb-4">
                            <div class="bg-info bg-opacity-10 p-3 border-bottom">
                                <h6 class="fw-bold mb-0 text-info"><i class="bi bi-clock-history me-2"></i> Requested DTR Change</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Metric</th>
                                            <th>Current Records</th>
                                            <th>Correction Request</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4 text-muted small">Target Date</td>
                                            <td colspan="2" class="fw-bold">{{ \Carbon\Carbon::parse($ticket->correction_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 text-muted small">Time In</td>
                                            <td>
                                                @if($currentAttendance && $currentAttendance->time_in)
                                                    <span class="badge bg-light text-dark fw-normal">{{ \Carbon\Carbon::parse($currentAttendance->time_in)->format('H:i') }}</span>
                                                @else
                                                    <span class="text-muted small italic">No Punch</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success fw-bold px-3">
                                                    {{ \Carbon\Carbon::parse($ticket->correction_time_in)->format('H:i') }}
                                                    <small class="d-block text-muted" style="font-size: 0.7rem">
                                                        {{ \Carbon\Carbon::parse($ticket->correction_time_in)->format('M d') }}
                                                    </small>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 text-muted small">Time Out</td>
                                            <td>
                                                @if($currentAttendance && $currentAttendance->time_out)
                                                    <span class="badge bg-light text-dark fw-normal">{{ \Carbon\Carbon::parse($currentAttendance->time_out)->format('H:i') }}</span>
                                                @else
                                                    <span class="text-muted small italic">No Punch</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger fw-bold px-3">
                                                    {{ \Carbon\Carbon::parse($ticket->correction_time_out)->format('H:i') }}
                                                    <small class="d-block text-muted" style="font-size: 0.7rem">
                                                        {{ \Carbon\Carbon::parse($ticket->correction_time_out)->format('M d') }}
                                                    </small>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Admin Response & Actions --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4">Resolution Action</h5>
                        
                        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" id="ticketForm">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Update Ticket Status</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="status" id="status_pending" value="pending" {{ $ticket->status == 'pending' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning w-100 rounded-3 py-2" for="status_pending">Pending</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="status" id="status_ongoing" value="ongoing" {{ $ticket->status == 'ongoing' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary w-100 rounded-3 py-2" for="status_ongoing">Investigate</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="status" id="status_resolved" value="resolved" {{ $ticket->status == 'resolved' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success w-100 rounded-3 py-2" for="status_resolved">Approve</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="status" id="status_closed" value="closed" {{ $ticket->status == 'closed' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger w-100 rounded-3 py-2" for="status_closed">Deny/Close</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Admin Remarks / Reply</label>
                                <textarea name="admin_reply" class="form-control border shadow-none rounded-4 p-3" rows="8" placeholder="Type your response here..." required>{{ $ticket->admin_reply }}</textarea>
                            </div>

                            @if($ticket->status !== 'resolved' && $ticket->type === 'DTR Correction')
                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark small rounded-4 p-3 mb-4">
                                <i class="bi bi-info-circle-fill me-2 text-warning"></i>
                                Clicking <strong>Approve</strong> will automatically update the attendance logs for {{ \Carbon\Carbon::parse($ticket->correction_date)->format('M d') }}.
                            </div>
                            @endif

                            <button type="submit" class="btn btn-dark btn-lg w-100 rounded-pill shadow-sm py-3">
                                Save Resolution Details
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Quick Links Sidebar --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3 small text-uppercase">Resolution Tools</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('attendance.index', ['employee' => $ticket->employee_id]) }}" class="btn btn-light rounded-3 text-start border-0 py-2">
                                <i class="bi bi-calendar-check me-2 text-primary"></i> View Full Attendance Logs
                            </a>
                            <a href="{{ route('employees.show', $ticket->employee_id) }}" class="btn btn-light rounded-3 text-start border-0 py-2">
                                <i class="bi bi-person-badge me-2 text-info"></i> Employee Profile
                            </a>
                            <a href="{{ route('dtr.show', $ticket->employee_id) }}" class="btn btn-light rounded-3 text-start border-0 py-2">
                                <i class="bi bi-file-earmark-text me-2 text-success"></i> View Current Month DTR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .breadcrumb-item + .breadcrumb-item::before {
        content: "•";
    }
    .btn-check:checked + .btn-outline-success {
        background-color: #198754;
        color: white;
    }
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        color: white;
    }
    .btn-check:checked + .btn-outline-warning {
        background-color: #ffc107;
        color: white;
    }
    .btn-check:checked + .btn-outline-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection
