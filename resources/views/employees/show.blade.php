@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-primary">
                    <i class="bi bi-person-badge me-2"></i>Employee Details: {{ $employee->first_name }} {{ $employee->last_name }}
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Profile Photo & Summary -->
                    <div class="col-md-3 text-center border-end">
                        <div class="mb-3 position-relative d-inline-block">
                            @if($employee->photo)
                                <img src="{{ asset('storage/' . $employee->photo) }}" class="rounded-circle img-thumbnail shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border shadow-sm mx-auto" style="width: 150px; height: 150px;">
                                    <i class="bi bi-person text-secondary" style="font-size: 5rem;"></i>
                                </div>
                            @endif
                        </div>
                        <h4 class="mb-1">{{ $employee->full_name }}</h4>
                        <p class="text-muted small mb-3">{{ $employee->position ?? 'No Position' }}</p>
                        <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>

                    <!-- Details Tab Content -->
                    <div class="col-md-9">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">Personal Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-0 d-block">Employee ID</label>
                                        <span class="fw-bold">{{ $employee->employee_id }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-0 d-block">Birth Date</label>
                                        <span class="fw-bold">{{ $employee->birthday ? \Carbon\Carbon::parse($employee->birthday)->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-0 d-block">Gender</label>
                                        <span class="fw-bold text-capitalize">{{ $employee->gender }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">Contact Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-muted mb-0 d-block">Email Address</label>
                                        <span class="fw-bold"><i class="bi bi-envelope me-1"></i>{{ $employee->email }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted mb-0 d-block">Mobile Numbers</label>
                                        <span class="fw-bold d-block"><i class="bi bi-phone me-1"></i>+63 {{ $employee->mobile_no_1 }}</span>
                                        @if($employee->mobile_no_2)
                                            <span class="fw-bold d-block small mt-1"><i class="bi bi-phone me-1"></i>+63 {{ $employee->mobile_no_2 }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-bold border-bottom pb-2 mb-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">Employment & Payroll Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-0 d-block">Payroll Group</label>
                                        <span class="fw-bold text-primary">{{ $employee->payrollGroup->name ?? 'None' }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-0 d-block">Main Bank Account</label>
                                        <span class="fw-bold text-primary">{{ $employee->bank_name ?? 'N/A' }}</span>
                                        @if($employee->account_no)
                                            <span class="d-block small text-muted">{{ $employee->account_no }}</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <!-- Other employment details could go here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-light p-3">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('attendance.employee.show', ['employee' => $employee->id]) }}" class="btn btn-outline-indigo btn-sm shadow-sm">
                        <i class="bi bi-calendar-check me-1"></i> View Detailed Attendance History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
