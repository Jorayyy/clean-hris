@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ticket Details: #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <a href="{{ route('employee.tickets.index') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back to Tickets</a>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6 border-end">
                        <small class="text-muted fw-bold d-block mb-1">Subject</small>
                        <h5 class="fw-bold mb-3">{{ $ticket->subject }}</h5>
                        <small class="text-muted fw-bold d-block mb-1">Concern Type</small>
                        <span class="badge bg-light text-dark shadow-sm px-3 mb-3">{{ $ticket->type }}</span>
                    </div>
                    <div class="col-md-6 ps-4">
                        <div class="mb-3">
                            <small class="text-muted fw-bold d-block mb-1">Priority</small>
                            <span class="badge {{ $ticket->priority == 'high' ? 'bg-danger' : 'bg-info' }}">{{ strtoupper($ticket->priority) }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted fw-bold d-block mb-1">Current Status</small>
                            <span class="badge {{ $ticket->status == 'resolved' ? 'bg-success' : ($ticket->status == 'pending' ? 'bg-warning text-dark' : 'bg-primary') }} text-uppercase fw-bold">{{ $ticket->status }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-4 shadow-sm border-start border-4 border-primary">
                    <small class="text-muted fw-bold mb-2 d-block">Request Description:</small>
                    <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $ticket->description }}</p>
                </div>

                @if($ticket->admin_reply)
                    <div class="bg-white p-3 rounded border shadow-sm mt-5 pt-4">
                        <div class="d-flex align-items-center mb-3 text-success">
                            <i class="bi bi-chat-dots-fill-fill me-2 h4"></i>
                            <h5 class="fw-bold mb-0">Management's Response</h5>
                        </div>
                        <div class="bg-light p-3 rounded border-start border-4 border-success">
                             <p class="mb-0 text-dark fst-italic" style="white-space: pre-line;">{{ $ticket->admin_reply }}</p>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info py-3 mt-4">
                        <i class="bi bi-info-circle me-2"></i> Our management team is currently reviewing your ticket. You will receive a notification once a reply is posted.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
