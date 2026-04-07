@extends('layouts.app')

@section('content')
<div class="row mt-5 justify-content-center">
    <!-- Web Bundy Section -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-lg border-0 h-100">
            <div class="card-header border-top border-4 border-danger bg-white text-center py-4 rounded-top">
                <h4 class="mb-0 fw-bold text-danger">WEB BUNDY</h4>
            </div>
            <div class="card-body p-4">
                <div class="text-end mb-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Circle-icons-clock.svg/2048px-Circle-icons-clock.svg.png" width="80" class="mb-2 shadow-sm rounded-circle">
                    <div id="liveClock" class="fw-bold text-dark h5 mb-0"></div>
                </div>

                @if (session('bundy_success'))
                    <div class="alert alert-success shadow-sm py-2 mb-3 small">{{ session('bundy_success') }}</div>
                @endif
                @if (session('bundy_error'))
                    <div class="alert alert-danger shadow-sm py-2 mb-3 small">{{ session('bundy_error') }}</div>
                @endif

                <form action="{{ route('bundy.punch') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Employee ID <i class="bi bi-person-fill text-danger"></i></label>
                        <input type="text" name="employee_id_string" class="form-control form-control-lg border-0 bg-light shadow-sm" placeholder="Enter Employee ID" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Web Bundy Code <i class="bi bi-shield-lock text-danger"></i></label>
                        <input type="password" name="web_bundy_code" class="form-control form-control-lg border-0 bg-light shadow-sm" placeholder="Enter Bundy Code" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold mb-2">Punch Type <i class="bi bi-key-fill text-danger"></i></label>
                        <div class="list-group shadow-sm rounded overflow-hidden">
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #4a90e2; color: white;">
                                <span class="small fw-bold">IN</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="am_in" checked>
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #fff9e6;">
                                <span class="small fw-bold text-warning-emphasis">1st BREAK OUT</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="break1_out">
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #fff9e6;">
                                <span class="small fw-bold text-warning-emphasis">1st BREAK IN</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="break1_in">
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #fdf2f2;">
                                <span class="small fw-bold text-danger-emphasis">LUNCH BREAK OUT</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="am_out">
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #fdf2f2;">
                                <span class="small fw-bold text-danger-emphasis">LUNCH BREAK IN</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="pm_in">
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #f0fdf4;">
                                <span class="small fw-bold text-success-emphasis">2nd BREAK OUT</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="break2_out">
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0 border-bottom" style="background: #f0fdf4;">
                                <span class="small fw-bold text-success-emphasis">2nd BREAK IN</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="break2_in">
                            </label>
                            <label class="list-group-item d-flex justify-content-between align-items-center list-group-item-action py-2 border-0" style="background: #4a90e2; color: white;">
                                <span class="small fw-bold">OUT</span>
                                <input class="form-check-input" type="radio" name="punch_type" value="pm_out">
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold py-3 shadow-sm border-0">
                            PUNCH
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Section -->
    <div class="col-md-4 offset-md-1">
        <div class="card shadow-lg border-0 h-100">
            <div class="card-header border-top border-4 border-primary bg-white text-center py-4 rounded-top">
                <h4 class="mb-0 fw-bold text-primary">PORTAL LOGIN</h4>
            </div>
            <div class="card-body p-4 d-flex flex-column">
                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm py-2 mb-3 small">
                        @foreach ($errors->all() as $error)
                            <div class="small fw-bold">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg border-0 bg-light shadow-sm" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg border-0 bg-light shadow-sm" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember Me</label>
                    </div>
                    <div class="d-grid shadow-sm mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 border-0">
                            LOGIN
                        </button>
                    </div>
                </form>

                <div class="mt-auto pt-4 border-top">
                    <div class="bg-light p-3 rounded text-center small text-muted">
                        <strong class="text-dark">Quick Creds</strong><br>
                        Admin: admin@test.com<br>
                        Employee: employee@test.com<br>
                        Pass: password
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
