@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold text-dark mb-0">CREATE SUPPORT TICKET</h4>
                <a href="{{ route('employee.tickets.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('employee.tickets.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Category/Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select border shadow-none rounded-3 px-3 py-2" required>
                            <option value="" selected disabled>Select Category</option>
                            <option value="DTR">DTR Concern</option>
                            <option value="HR">HR Inquiry</option>
                            <option value="Payroll">Payroll Question</option>
                            <option value="IT">IT Support</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select border shadow-none rounded-3 px-3 py-2" required>
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control border shadow-none rounded-3 px-3 py-2" placeholder="Brief summary of your concern" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control border shadow-none rounded-3 px-3 py-2" rows="6" placeholder="Please provide detailed information about your concern..." required></textarea>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold shadow-sm">
                            Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

