@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Submit New Support Ticket</h5>
                <a href="{{ route('employee.tickets.index') }}" class="btn btn-sm btn-outline-light d-flex align-items-center"><i class="bi bi-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('employee.tickets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Concern Type</label>
                        <select name="type" class="form-select border-0 shadow-sm bg-light" required>
                            <option value="">Select Concern Type...</option>
                            <option value="DTR Issue">DTR / Attendance Correction</option>
                            <option value="Salary Concern">Salary / Payroll Discrepancy</option>
                            <option value="Leaves">Leave Request Support</option>
                            <option value="Technical Support">Portal Technical Issue</option>
                            <option value="Personal">Personal Concerns</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Subject</label>
                        <input type="text" name="subject" class="form-control border-0 shadow-sm bg-light" placeholder="Briefly summarize your concern..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Priority Level</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="priority" id="p_low" value="low">
                                <label class="form-check-label text-secondary small" for="p_low">Low</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="priority" id="p_normal" value="normal" checked>
                                <label class="form-check-label text-info small fw-bold" for="p_normal">Normal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="priority" id="p_high" value="high">
                                <label class="form-check-label text-danger small fw-bold" for="p_high">High (Urgent)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Detailed Description</label>
                        <textarea name="description" class="form-control border-0 shadow-sm bg-light" rows="6" placeholder="Please provide all necessary details including dates if relevant..." required></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">Submit Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
