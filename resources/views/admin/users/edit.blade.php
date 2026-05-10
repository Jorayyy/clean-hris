@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 mt-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0">Edit User: {{ $user->name }}</h4>
                <p class="text-muted small mb-0">Manage account details.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control border shadow-none rounded-3 px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control border shadow-none rounded-3 px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted text-danger">Update Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control border shadow-none rounded-3 px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">Confirm Password Update</label>
                <input type="password" name="password_confirmation" class="form-control border shadow-none rounded-3 px-3 py-2">
            </div>

            <div class="d-grid shadow-sm rounded-3 overflow-hidden mt-4">
                <button type="submit" class="btn btn-primary py-3 fw-bold">UPDATE USER ACCOUNT</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
