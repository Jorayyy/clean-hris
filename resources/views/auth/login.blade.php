@extends('layouts.app')

@section('content')
<div class="row justify-content-center align-items-stretch g-4" style="min-height: 80vh;">
    <!-- Web Bundy Section -->
    <div class="col-md-5">
        <div class="card shadow-lg border-0 h-100 rounded-4 overflow-hidden">
            <div class="card-header border-0 bg-danger text-white text-center py-4">
                <i class="bi bi-clock-fill h2 mb-2 d-block"></i>
                <h4 class="mb-0 fw-bold tracking-wider">WEB BUNDY</h4>
                <p class="small opacity-75 mb-0 font-monospace" id="liveClock"></p>
            </div>
            <div class="card-body p-4 bg-white">
                @if (session('bundy_success'))
                    <div class="alert alert-success border-0 shadow-sm py-2 mb-3 small d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('bundy_success') }}
                    </div>
                @endif
                @if (session('bundy_error'))
                    <div class="alert alert-danger border-0 shadow-sm py-2 mb-3 small d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('bundy_error') }}
                    </div>
                @endif

                <form action="{{ route('bundy.punch') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" name="employee_id_string" class="form-control border-0 bg-light shadow-sm" id="employeeId" placeholder="Employee ID" required>
                        <label for="employeeId" class="text-muted small fw-bold"><i class="bi bi-person-fill text-danger me-1"></i>Employee ID</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="web_bundy_code" class="form-control border-0 bg-light shadow-sm" id="bundyCode" placeholder="Bundy Code" required>
                        <label for="bundyCode" class="text-muted small fw-bold"><i class="bi bi-shield-lock-fill text-danger me-1"></i>Web Bundy Code</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold mb-2 ms-1">Punch Type</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="am_in" value="am_in" checked autocomplete="off">
                                <label class="btn btn-outline-info w-100 py-2 fw-bold small" for="am_in">IN</label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="pm_out" value="pm_out" autocomplete="off">
                                <label class="btn btn-outline-info w-100 py-2 fw-bold small" for="pm_out">OUT</label>
                            </div>
                            
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break1_out" value="break1_out" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 py-2 fw-bold small" for="break1_out">1st BRK OUT</label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break1_in" value="break1_in" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 py-2 fw-bold small" for="break1_in">1st BRK IN</label>
                            </div>

                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="am_out" value="am_out" autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-2 fw-bold small" for="am_out">LUNCH OUT</label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="pm_in" value="pm_in" autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-2 fw-bold small" for="pm_in">LUNCH IN</label>
                            </div>

                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break2_out" value="break2_out" autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-2 fw-bold small" for="break2_out">2nd BRK OUT</label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break2_in" value="break2_in" autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-2 fw-bold small" for="break2_in">2nd BRK IN</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold py-3 shadow border-0 rounded-3">
                            PUNCH NOW
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Section -->
    <div class="col-md-5">
        <div class="card shadow-lg border-0 h-100 rounded-4 overflow-hidden">
            <div class="card-header border-0 bg-primary text-white text-center py-4">
                <i class="bi bi-person-workspace h2 mb-2 d-block"></i>
                <h4 class="mb-0 fw-bold tracking-wider">PORTAL LOGIN</h4>
                <p class="small opacity-75 mb-0">Sign in to your account</p>
            </div>
            <div class="card-body p-4 bg-white d-flex flex-column">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm py-2 mb-3 small d-flex align-items-center">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div class="fw-bold">{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control border-0 bg-light shadow-sm" id="email" placeholder="name@example.com" required>
                        <label for="email" class="text-muted small fw-bold">Email Address</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control border-0 bg-light shadow-sm" id="password" placeholder="Password" required>
                        <label for="password" class="text-muted small fw-bold">Password</label>
                    </div>

                    <div class="mb-3 form-check form-switch ms-1">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember Access</label>
                    </div>

                    <div class="d-grid shadow-sm mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 border-0 rounded-3">
                            SIGN IN
                        </button>
                    </div>
                </form>

                <div class="mt-auto pt-4 border-top">
                    <div class="bg-light p-3 rounded-3 text-center small text-muted">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong class="text-dark">Quick Access</strong><br>
                        Admin: admin@test.com | Employee: employee@test.com<br>
                        Password: <span class="badge bg-secondary">password</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('liveClock').innerText = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
