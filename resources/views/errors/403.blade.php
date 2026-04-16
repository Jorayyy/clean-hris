@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="display-1 text-danger mb-4">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="display-5 fw-bold text-gray-800">No Permission</h1>
            <p class="lead text-gray-600 mb-5">
                Sorry, you don't have enough authority to access this page. This section is restricted to Super Administrators only.
            </p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
