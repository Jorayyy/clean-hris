@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center py-3">
                <h5 class="mb-0">Payroll System Login</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        @foreach ($errors->all() as $error)
                            <div class="small">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember Me</label>
                    </div>
                    <div class="d-grid shadow-sm">
                        <button type="submit" class="btn btn-primary py-2">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-2 text-muted small">
                Admin: admin@test.com / password<br>
                Employee: employee@test.com / password
            </div>
        </div>
    </div>
</div>
@endsection
