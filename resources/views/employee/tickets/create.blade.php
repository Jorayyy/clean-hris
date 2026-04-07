@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Create New Transaction / Request</h5>
                <a href="{{ route('employee.tickets.index') }}" class="btn btn-sm btn-outline-light d-flex align-items-center"><i class="bi bi-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('employee.tickets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Request Category</label>
                        <select name="type" class="form-select border-0 shadow-sm bg-light" required>
                            <option value="">-- Choose Category --</option>
                            <optgroup label="Timekeeping (TK)">
                                <option value="DTR Correction">DTR / Attendance Correction</option>
                                <option value="Forgot Punch">Forgot to Punch (In/Out)</option>
                                <option value="OT Authorization">Overtime Authorization Request</option>
                            </optgroup>
                            <optgroup label="Payroll & Compensation">
                                <option value="Salary Discrepancy">Salary / Payroll Discrepancy</option>
                                <option value="Payslip Issue">Payslip Access Issue</option>
                                <option value="13th Month/Bonus">Bonus / 13th Month Inquiries</option>
                            </optgroup>
                            <optgroup label="Leaves & Benefits">
                                <option value="Leave Application">Leave Application Support</option>
                                <option value="Health/HMO">Health / HMO Benefits Concern</option>
                                <option value="SSS/Philhealth">SSS / Philhealth / Pag-ibig Concern</option>
                            </optgroup>
                            <optgroup label="General Admin">
                                <option value="COE Request">Request for COE / Documents</option>
                                <option value="Technical Support">Portal Technical Issue</option>
                                <option value="Personal">Other Personal Concerns</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Subject / Purpose</label>
                        <input type="text" name="subject" class="form-control border-0 shadow-sm bg-light" placeholder="e.g. DTR Correction for April 10" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Priority Level</label>
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
                        <label class="form-label fw-bold text-muted small text-uppercase">Request Details</label>
                        <textarea name="description" class="form-control border-0 shadow-sm bg-light" rows="6" placeholder="Please provide all necessary details such as dates, times, and specific reasons for your request..." required></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
