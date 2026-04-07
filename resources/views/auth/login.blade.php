@extends('layouts.app')

@section('content')
<div class="row mt-5">
    <!-- Web Bundy Section -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-lg border-0" style="border-top: 5px solid #dc3545 !important;">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger fw-bold">Web Bundy</h5>
                <div id="liveClock" class="fw-bold text-dark h5 mb-0"></div>
            </div>
            <div class="card-body p-4">
                @if (session('bundy_success'))
                    <div class="alert alert-success shadow-sm">{{ session('bundy_success') }}</div>
                @endif
                @if (session('bundy_error'))
                    <div class="alert alert-danger shadow-sm">{{ session('bundy_error') }}</div>
                @endif

                <form action="{{ route('bundy.punch') }}" method="POST">
                    @csrf
                    <div class="mb-4 text-center">
                         <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Circle-icons-clock.svg/2048px-Circle-icons-clock.svg.png" width="120" class="mb-3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Employee ID <i class="bi bi-person-fill text-danger"></i></label>
                        <input type="text" name="employee_id_string" class="form-control form-control-lg bg-light" placeholder="Enter Employee ID" required>
                    </div>

                    <div class="list-group mb-4 shadow-sm border rounded">
                        <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-3 mb-0" style="background: #007bff; color: white; cursor: pointer;">
                            <span>IN (AM)</span>
                            <input class="form-check-input ms-2" type="radio" name="punch_type" value="am_in" checked required>
                        </label>
                        <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-3 mb-0" style="background: #fff3cd; cursor: pointer;">
                            <span>LUNCH BREAK OUT</span>
                            <input class="form-check-input ms-2" type="radio" name="punch_type" value="am_out">
                        </label>
                        <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-3 mb-0" style="background: #f8d7da; cursor: pointer;">
                            <span>LUNCH BREAK IN</span>
                            <input class="form-check-input ms-2" type="radio" name="punch_type" value="pm_in">
                        </label>
                        <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-3 mb-0" style="background: #007bff; color: white; cursor: pointer;">
                            <span>OUT (PM)</span>
                            <input class="form-check-input ms-2" type="radio" name="punch_type" value="pm_out">
                        </label>
                    </div>

                    <div class="d-grid shadow">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold py-3 text-uppercase">
                            PUNCH
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Section -->
    <div class="col-md-5 offset-md-1">
        <div class="card shadow-lg border-0 h-100">
            <div class="card-header bg-dark text-white text-center py-4 rounded-top">
                <h4 class="mb-0 fw-bold">Sign In Portal</h4>
            </div>
            <div class="card-body p-5">
                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        @foreach ($errors->all() as $error)
                            <div class="small fw-bold">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg border-0 bg-light shadow-sm" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg border-0 bg-light shadow-sm" required>
                    </div>
                    <div class="mb-4 form-check d-flex justify-content-between">
                        <div>
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label text-muted small" for="remember">Remember Me</label>
                        </div>
                    </div>
                    <div class="d-grid shadow-sm">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold py-3">
                            LOGIN
                        </button>
                    </div>
                </form>

                <div class="mt-5 pt-4 border-top">
                    <div class="bg-light p-3 rounded text-center small text-muted">
                        <strong>Test Credentials</strong><br>
                        Admin: admin@test.com / password<br>
                        Employee: employee@test.com / password
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').innerText = now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
