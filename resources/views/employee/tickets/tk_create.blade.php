@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold text-dark mb-0">TIME KEEPING (TK) COMPLAINT</h4>
                <a href="{{ route('employee.tickets.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">Note: <span class="text-danger">Asterisk shows mandatory fields.</span></p>

                <form action="{{ route('employee.tickets.store') }}" method="POST">
                    @csrf
                    {{-- Hidden fields to maintain compatibility with SupportTicket model --}}
                    <input type="hidden" name="type" value="DTR Correction">
                    <input type="hidden" name="priority" value="normal">
                    <input type="hidden" name="subject" value="TK Complaint">

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Covered Date <span class="text-danger">*</span></label>
                        <input type="date" name="covered_date" class="form-control border shadow-none rounded-3 px-3 py-2" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Time IN <span class="text-danger">* (military time)</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <select name="time_in_hh" class="form-select border shadow-none rounded-3" required>
                                    @foreach(range(0, 23) as $h)
                                        <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                    @endforeach
                                </select>
                                <span class="fw-bold">:</span>
                                <select name="time_in_mm" class="form-select border shadow-none rounded-3" required>
                                    @foreach(range(0, 59) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Time OUT <span class="text-danger">* (military time)</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <select name="time_out_hh" class="form-select border shadow-none rounded-3" required>
                                    @foreach(range(0, 23) as $h)
                                        <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                    @endforeach
                                </select>
                                <span class="fw-bold">:</span>
                                <select name="time_out_mm" class="form-select border shadow-none rounded-3" required>
                                    @foreach(range(0, 59) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Time IN Date <span class="text-danger">*</span></label>
                            <input type="date" name="time_in_date" class="form-control border shadow-none rounded-3 px-3 py-2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Time OUT Date <span class="text-danger">*</span></label>
                            <input type="date" name="time_out_date" class="form-control border shadow-none rounded-3 px-3 py-2" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Reason <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control border shadow-none rounded-3 px-3 py-2" rows="4" placeholder="Please state your reason..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Warnings</label>
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 p-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> No Warnings Found.
                        </div>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-success btn-lg rounded-3 fw-bold shadow-sm">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
