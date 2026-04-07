@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Header Section -->
            <div class="mb-4 text-center">
                <h3 class="fw-bold text-dark">Manual Attendance Entry</h3>
                <p class="text-muted small">Log attendance records for employees who missed their punch or for administrative corrections.</p>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-plus text-primary fs-5"></i>
                        <h5 class="card-title fw-bold mb-0">Record Details</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        
                        <!-- Employee Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Select Employee</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person"></i>
                                </span>
                                <select name="employee_id" class="form-select bg-light border-start-0 @error('employee_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Choose an employee...</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} ({{ $emp->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Attendance Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                                <input type="date" name="date" class="form-control bg-light border-start-0 @error('date') is-invalid @enderror" 
                                       value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Time In/Out Grid -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Time In</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-success">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </span>
                                    <input type="time" name="time_in" class="form-control bg-light border-start-0 @error('time_in') is-invalid @enderror" 
                                           value="{{ old('time_in', '08:00') }}" required>
                                    @error('time_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted mt-1 d-block italic">Typical: 08:00 AM</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Time Out</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-danger">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </span>
                                    <input type="time" name="time_out" class="form-control bg-light border-start-0 @error('time_out') is-invalid @enderror" 
                                           value="{{ old('time_out', '17:00') }}" required>
                                    @error('time_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted mt-1 d-block italic">Typical: 05:00 PM</small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold flex-grow-1 shadow-sm">
                                <i class="bi bi-save me-2"></i> Save Record
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn btn-light px-4 py-2 text-secondary border">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light border-0 py-3 text-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i> 
                        Late and undertime will be automatically calculated based on the employee's assigned schedule.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
