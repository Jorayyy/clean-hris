@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow border-0 text-center p-4">
            <div class="mb-3">
                <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                    <span class="fs-1 fw-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
            </div>
            <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
            <div class="text-muted small text-uppercase fw-bold mb-3">{{ $user->role }}</div>
            
            @if($employee)
                <div class="bg-light rounded p-3 text-start mb-3">
                    <div class="row mb-2">
                        <div class="col-6 text-muted small">Employee ID:</div>
                        <div class="col-6 fw-bold small">{{ $employee->employee_id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted small">Position:</div>
                        <div class="col-6 fw-bold small">{{ $employee->position }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted small">Department:</div>
                        <div class="col-6 fw-bold small">{{ $employee->payrollGroup->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-muted small">Status:</div>
                        <div class="col-6 small">
                            <span class="badge bg-success">{{ $employee->status }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow border-0 rounded overflow-hidden">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2"></i>Account Settings</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Change Password <small class="text-muted">(Leave blank if not changing)</small></h6>

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($employee)
        <div class="card shadow border-0 rounded overflow-hidden mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Personal Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">First Name</label>
                        <div class="fw-bold">{{ $employee->first_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Last Name</label>
                        <div class="fw-bold">{{ $employee->last_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Birthday</label>
                        <div class="fw-bold">{{ $employee->birthday ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Mobile No.</label>
                        <div class="fw-bold">{{ $employee->mobile_no_1 ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted small">Current Address</label>
                        <div class="fw-bold">{{ $employee->present_address_brgy ? $employee->present_address_brgy . ', ' . $employee->present_address_province : 'Not set' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection