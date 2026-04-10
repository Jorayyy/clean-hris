@extends('layouts.auth')

@section('content')
<div class="d-flex flex-column align-items-center w-100" style="gap: 1.5rem;">
    <!-- Bundy Section -->
    <div class="w-100" id="bundyCol">
        <div class="card shadow-none border-0 auth-card {{ session('bundy_success') || session('bundy_error') || $errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']) ? 'active-form' : 'minimized' }}" id="bundyCard">
            <div class="card-header border-0 bg-danger bg-gradient text-white text-center py-3 rounded-3 shadow-lg" onclick="toggleCard('bundy')" style="cursor: pointer;">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.4);">
                        <i class="bi bi-fingerprint fs-4 text-white"></i>
                    </div>
                    <div class="text-start">
                        <h5 class="mb-0 fw-800 tracking-wider">WEB BUNDY</h5>
                        <p class="small opacity-75 mb-0 d-none d-active" id="liveClock" style="font-family: 'JetBrains Mono', monospace !important; font-weight: 600;"></p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-xl-5 bg-white bg-opacity-80 rounded-bottom-4 shadow-sm" style="backdrop-filter: blur(10px);">
                @if (session('bundy_success'))
                    <div class="alert alert-success border-0 shadow-sm py-3 mb-4 small d-flex align-items-center rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div class="fw-600">{{ session('bundy_success') }}</div>
                    </div>
                @endif
                @if (session('bundy_error'))
                    <div class="alert alert-danger border-0 shadow-sm py-3 mb-4 small d-flex align-items-center rounded-3 bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div class="fw-600">{{ session('bundy_error') }}</div>
                    </div>
                @endif

                <form action="{{ route('bundy.punch') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3 border-0 rounded-4 p-1 bg-white shadow-sm overflow-hidden" style="border: 1px solid #eef2f7 !important;">
                        <input type="text" name="employee_id_string" class="form-control border-0 bg-transparent h-auto py-2 ps-3 fw-600 text-dark" id="employeeId" placeholder="Employee ID" required style="box-shadow: none;">
                        <label for="employeeId" class="text-muted small fw-bold mt-1 ps-3"><i class="bi bi-person-badge text-danger me-2"></i>Employee ID</label>
                    </div>

                    <div class="form-floating mb-3 border-0 rounded-4 p-1 bg-white shadow-sm overflow-hidden" style="border: 1px solid #eef2f7 !important;">
                        <input type="password" name="web_bundy_code" class="form-control border-0 bg-transparent h-auto py-2 ps-3 fw-600 text-dark" id="bundyCode" placeholder="Bundy Code" required style="box-shadow: none;">
                        <label for="bundyCode" class="text-muted small fw-bold mt-1 ps-3"><i class="bi bi-shield-lock text-danger me-2"></i>Web Bundy Code</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark opacity-75 small fw-800 mb-2 ms-2 d-flex align-items-center">
                            PUNCH SELECTION: <span id="currentPunchText" class="ms-2 badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 0.65rem;">AM IN (START)</span>
                            <span class="ms-auto"><i class="bi bi-broadcast text-danger pulse-icon"></i></span>
                        </label>
                        
                        <!-- Tab-style Punch Selector for space saving -->
                        <div class="row g-1">
                            <!-- Main Shifts -->
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="am_in" value="am_in" checked autocomplete="off" onchange="updatePunchSelectionDisplay('AM IN (START)')">
                                <label class="btn btn-outline-info w-100 py-2 border-0 bg-white shadow-sm d-flex flex-column align-items-center punch-type-btn rounded-3" for="am_in">
                                    <i class="bi bi-box-arrow-in-right fs-5 mb-0"></i>
                                    <span class="fw-800" style="font-size: 0.65rem;">IN</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="pm_out" value="pm_out" autocomplete="off" onchange="updatePunchSelectionDisplay('PM OUT (END)')">
                                <label class="btn btn-outline-info w-100 py-2 border-0 bg-white shadow-sm d-flex flex-column align-items-center punch-type-btn rounded-3" for="pm_out">
                                    <i class="bi bi-box-arrow-right fs-5 mb-0"></i>
                                    <span class="fw-800" style="font-size: 0.65rem;">OUT</span>
                                </label>
                            </div>

                            <!-- Integrated Break/Lunch Toggle Grid -->
                            <div class="col-12 mt-1">
                                <div class="p-2 bg-light rounded-4 d-flex gap-1 shadow-sm border" style="background: rgba(0,0,0,0.03) !important;">
                                    <div class="flex-grow-1">
                                        <div class="dropdown w-100">
                                            <button class="btn btn-white bg-white w-100 py-1 border-0 shadow-sm dropdown-toggle fw-800" type="button" data-bs-toggle="dropdown" id="breakDropdownBtn" style="font-size: 0.65rem;">
                                                <i class="bi bi-cup-hot text-warning me-1"></i> BREAKS
                                            </button>
                                            <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-4">
                                                <li>
                                                    <div class="d-flex gap-1 p-1">
                                                        <input type="radio" class="btn-check" name="punch_type" id="break1_out" value="break1_out" onchange="updatePunchSelectionDisplay('1st BREAK OUT')">
                                                        <label class="btn btn-outline-warning flex-grow-1 py-2 rounded-3 border-0 bg-light" for="break1_out" style="font-size: 0.7rem;">1st OUT</label>
                                                        <input type="radio" class="btn-check" name="punch_type" id="break1_in" value="break1_in" onchange="updatePunchSelectionDisplay('1st BREAK IN')">
                                                        <label class="btn btn-outline-warning flex-grow-1 py-2 rounded-3 border-0 bg-light" for="break1_in" style="font-size: 0.7rem;">1st IN</label>
                                                    </div>
                                                </li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li>
                                                    <div class="d-flex gap-1 p-1">
                                                        <input type="radio" class="btn-check" name="punch_type" id="break2_out" value="break2_out" onchange="updatePunchSelectionDisplay('2nd BREAK OUT')">
                                                        <label class="btn btn-outline-success flex-grow-1 py-2 rounded-3 border-0 bg-light" for="break2_out" style="font-size: 0.7rem;">2nd OUT</label>
                                                        <input type="radio" class="btn-check" name="punch_type" id="break2_in" value="break2_in" onchange="updatePunchSelectionDisplay('2nd BREAK IN')">
                                                        <label class="btn btn-outline-success flex-grow-1 py-2 rounded-3 border-0 bg-light" for="break2_in" style="font-size: 0.7rem;">2nd IN</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="dropdown w-100">
                                            <button class="btn btn-white bg-white w-100 py-1 border-0 shadow-sm dropdown-toggle fw-800" type="button" data-bs-toggle="dropdown" id="lunchDropdownBtn" style="font-size: 0.65rem;">
                                                <i class="bi bi-egg-fried text-danger me-1"></i> LUNCH
                                            </button>
                                            <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-4">
                                                <li>
                                                    <div class="d-flex gap-1 p-1">
                                                        <input type="radio" class="btn-check" name="punch_type" id="am_out" value="am_out" onchange="updatePunchSelectionDisplay('LUNCH OUT')">
                                                        <label class="btn btn-outline-danger flex-grow-1 py-2 rounded-3 border-0 bg-light" for="am_out" style="font-size: 0.7rem;">OUT</label>
                                                        <input type="radio" class="btn-check" name="punch_type" id="pm_in" value="pm_in" onchange="updatePunchSelectionDisplay('LUNCH IN')">
                                                        <label class="btn btn-outline-danger flex-grow-1 py-2 rounded-3 border-0 bg-light" for="pm_in" style="font-size: 0.7rem;">IN</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger btn-lg fw-900 py-3 rounded-4 shadow-lg text-uppercase tracking-widest border-0">
                            PUNCH NOW
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Section -->
    <div class="w-100" id="loginCol">
        <div class="card shadow-none border-0 auth-card {{ !session('bundy_success') && !session('bundy_error') && !$errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']) ? 'active-form' : 'minimized' }}" id="loginCard">
            <div class="card-header border-0 bg-primary bg-gradient text-white text-center py-3 rounded-3 shadow-lg" onclick="toggleCard('login')" style="cursor: pointer;">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.4);">
                        <i class="bi bi-shield-lock-fill fs-4 text-white"></i>
                    </div>
                    <div class="text-start">
                        <h5 class="mb-0 fw-800 tracking-wider">PORTAL LOGIN</h5>
                        <p class="small opacity-75 mb-0 d-none d-active">Secure Staff Dashboard</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-xl-5 bg-white bg-opacity-80 rounded-bottom-4 shadow-sm" style="backdrop-filter: blur(10px);">
                @if ($errors->any() && !$errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']))
                    <div class="alert alert-danger border-0 shadow-sm py-3 mb-4 small d-flex align-items-center rounded-3 bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div class="fw-bold">{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3 border-0 rounded-4 p-1 bg-white shadow-sm overflow-hidden" style="border: 1px solid #eef2f7 !important;">
                        <input type="email" name="email" class="form-control border-0 bg-transparent h-auto py-2 ps-3 fw-600 text-dark" id="email" placeholder="name@example.com" required style="box-shadow: none;">
                        <label for="email" class="text-muted small fw-bold mt-1 ps-3">Email Address</label>
                    </div>
                    
                    <div class="form-floating mb-3 border-0 rounded-4 p-1 bg-white shadow-sm overflow-hidden position-relative" style="border: 1px solid #eef2f7 !important;">
                        <input type="password" name="password" class="form-control border-0 bg-transparent h-auto py-2 ps-3 fw-600 text-dark" id="password" placeholder="Password" required style="box-shadow: none;">
                        <label for="password" class="text-muted small fw-bold mt-1 ps-3">Password</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted opacity-50 pe-3" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4 px-2">
                        <div class="form-check form-switch p-0">
                            <input type="checkbox" name="remember" class="form-check-input ms-0 me-2" id="remember" style="cursor: pointer;">
                            <label class="form-check-label text-dark opacity-75 small fw-700" for="remember" style="cursor: pointer;">Remember Device</label>
                        </div>
                    </div>

                    <div class="d-grid shadow-lg rounded-4 overflow-hidden">
                        <button type="submit" class="btn btn-primary btn-lg fw-900 py-3 border-0 text-uppercase tracking-widest">
                            ACCESS PORTAL
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .active-form .d-active { display: block !important; }
    .auth-card.minimized { cursor: pointer; }
    .auth-card.minimized:hover { opacity: 0.9; }
</style>
<script>
    function toggleCard(type) {
        const bundyCol = document.getElementById('bundyCol');
        const loginCol = document.getElementById('loginCol');
        const bundyCard = document.getElementById('bundyCard');
        const loginCard = document.getElementById('loginCard');

        if (type === 'bundy') {
            bundyCard.classList.remove('minimized');
            bundyCard.classList.add('active-form');
            loginCard.classList.add('minimized');
            loginCard.classList.remove('active-form');
            // Move Bundy top
            bundyCol.parentElement.prepend(bundyCol);
        } else {
            loginCard.classList.remove('minimized');
            loginCard.classList.add('active-form');
            bundyCard.classList.add('minimized');
            bundyCard.classList.remove('active-form');
            // Move Login top
            loginCol.parentElement.prepend(loginCol);
        }
    }

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.innerText = now.toLocaleDateString('en-US', options).replace(/,/g, '');
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }
</script>
@endpush
@endsection
