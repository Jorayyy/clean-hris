@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0 rounded overflow-hidden">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i>Admin Profile</h5>
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

                    <hr class="my-4">
                    <div class="bg-light p-3 rounded border">
                        <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2 text-danger"></i>Daily Time Record (DTR) Security</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">DTR Authorization Password</label>
                            <input type="password" name="dtr_password" class="form-control" value="{{ $user->dtr_password }}" placeholder="Leave blank to use your primary login password">
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i> This secondary password will be required for sensitive operations like <strong>Verifying/Finalizing DTRs</strong> and <strong>Processing Payroll</strong>.
                            </small>
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
    </div>
</div>
@endsection