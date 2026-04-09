@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Header Section -->
            <div class="mb-4 text-center">
                <h3 class="fw-bold text-dark">Edit Attendance Record</h3>
                <p class="text-muted small">Update the punch times or date for this specific attendance log.</p>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-primary fs-5"></i>
                        <h5 class="card-title fw-bold mb-0">Record Details</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Employee Selection (View Only) -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Employee</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control bg-light border-start-0" 
                                       value="{{ $attendance->employee->full_name }}" disabled>
                                <input type="hidden" name="employee_id" value="{{ $attendance->employee_id }}">
                            </div>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Attendance Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                                <input type="date" name="date" class="form-control bg-white border-start-0 @error('date') is-invalid @enderror" 
                                       value="{{ old('date', $attendance->date) }}" required>
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
                                    <input type="time" name="time_in" class="form-control bg-white border-start-0 @error('time_in') is-invalid @enderror" 
                                           value="{{ old('time_in', \Carbon\Carbon::parse($attendance->time_in)->format('H:i')) }}" required>
                                    @error('time_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Time Out</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-danger">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </span>
                                    <input type="time" name="time_out" class="form-control bg-white border-start-0 @error('time_out') is-invalid @enderror" 
                                           value="{{ old('time_out', $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '') }}" required>
                                    @error('time_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold flex-grow-1 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i> Update Record
                            </button>
                            <a href="{{ route('attendance.show', $attendance->employee_id) }}" class="btn btn-light px-4 py-2 text-secondary border">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            @if($attendance->created_at)
            <div class="mt-3 text-center">
                <p class="text-muted extra-small">
                    Record created on {{ $attendance->created_at->format('M d, Y h:i A') }} 
                    @if($attendance->updated_at != $attendance->created_at)
                    <br>Last modified: {{ $attendance->updated_at->format('M d, Y h:i A') }}
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .extra-small { font-size: 0.75rem; }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.6;
    }
</style>
@endsection
