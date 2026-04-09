@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Respond to Ticket: #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back to Tickets</a>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6 border-end">
                        <small class="text-muted fw-bold d-block mb-1">Employee Name</small>
                        <h5 class="fw-bold mb-3">{{ $ticket->employee->full_name }} ({{ $ticket->employee->employee_id }})</h5>
                        <small class="text-muted fw-bold d-block mb-1">Concern Type</small>
                        <span class="badge bg-light text-dark shadow-sm px-3 mb-3">{{ $ticket->type }}</span>
                        
                        @php
                            $resolutionLink = null;
                            $linkText = "";
                            $icon = "bi-box-arrow-up-right";

                            switch($ticket->type) {
                                case 'DTR Correction':
                                case 'Forgot Punch':
                                    $resolutionLink = route('attendance.index', ['employee' => $ticket->employee_id]);
                                    $linkText = "Go to Attendance Logs";
                                    $icon = "bi-calendar-check";
                                    break;
                                case 'Salary Discrepancy':
                                case 'Payslip Issue':
                                case '13th Month/Bonus':
                                    $resolutionLink = route('payroll.index');
                                    $linkText = "Go to Payroll Management";
                                    $icon = "bi-cash-stack";
                                    break;
                                case 'Leave Application':
                                    // Assuming leave is handled under employees or a specific route if exists
                                    $resolutionLink = route('employees.show', $ticket->employee_id);
                                    $linkText = "View Employee Profile";
                                    $icon = "bi-person-badge";
                                    break;
                                case 'Technical Support':
                                    $resolutionLink = route('admin.settings.index');
                                    $linkText = "System Settings";
                                    $icon = "bi-gear";
                                    break;
                                default:
                                    $resolutionLink = route('employees.show', $ticket->employee_id);
                                    $linkText = "View Employee Profile";
                                    $icon = "bi-person";
                            }
                        @endphp

                        @if($resolutionLink)
                        <div class="mt-2">
                            <a href="{{ $resolutionLink }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="bi {{ $icon }} me-1"></i> Resolve: {{ $linkText }}
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6 ps-4">
                         <small class="text-muted fw-bold d-block mb-1">Priority</small>
                        <span class="badge {{ $ticket->priority == 'high' ? 'bg-danger' : 'bg-info' }}">{{ strtoupper($ticket->priority) }}</span>
                        <div class="mt-3">
                             <small class="text-muted fw-bold d-block mb-1">Original Date Submitted</small>
                            <span class="small">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-4 shadow-sm border-start border-4 border-primary">
                    <small class="text-muted fw-bold mb-2 d-block">Employee's Message:</small>
                    <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $ticket->description }}</p>
                </div>

                <h5 class="fw-bold mb-3">Post a Reply / Change Status</h5>
                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Update Status</label>
                        <select name="status" class="form-select border-0 shadow-sm bg-light" required>
                            <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending (Needs Attention)</option>
                            <option value="ongoing" {{ $ticket->status == 'ongoing' ? 'selected' : '' }}>Ongoing (Currently Reviewing)</option>
                            <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved (Problem Solved)</option>
                            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed (Archived)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Admin Reply / Resolution Notes</label>
                        <textarea name="admin_reply" class="form-control border-0 shadow-sm bg-light" rows="6" placeholder="Provide explanation or steps taken to resolve the issue..." required>{{ $ticket->admin_reply }}</textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">Update Ticket Resolution</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
