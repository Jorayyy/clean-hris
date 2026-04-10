@extends('layouts.auth')

@section('content')
<div class="d-flex flex-column align-items-center w-100" style="gap: 1.5rem;">
    <!-- Bundy Section -->
    <div class="w-100" id="bundyCol">
        <div class="card shadow-none border-0 auth-card {{ session('bundy_success') || session('bundy_error') || $errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']) ? 'active-form' : 'minimized' }}" id="bundyCard">
            <div class="card-header border-0 bg-white bg-opacity-10 text-white text-center py-3 rounded-3 shadow-lg" onclick="toggleCard('bundy')" style="cursor: pointer; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1) !important;">
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
            <div class="card-body p-4 p-xl-5 bg-white bg-opacity-10 rounded-bottom-4 shadow-sm" style="backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-top: none;">
                @if (session('bundy_success'))
                    <div class="alert alert-success border-0 shadow-sm py-3 mb-4 small d-flex align-items-center rounded-3 bg-success bg-opacity-20 text-white">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div class="fw-600">{{ session('bundy_success') }}</div>
                    </div>
                @endif
                @if (session('bundy_error'))
                    <div class="alert alert-danger border-0 shadow-sm py-3 mb-4 small d-flex align-items-center rounded-3 bg-danger bg-opacity-20 text-white">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div class="fw-600">{{ session('bundy_error') }}</div>
                    </div>
                @endif

                <form action="{{ route('bundy.punch') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="employeeId" class="form-label text-white opacity-90 small fw-800 mb-1 ms-2">
                            <i class="bi bi-person-badge text-white me-1"></i> EMPLOYEE ID
                        </label>
                        <div class="border rounded-4 p-1 bg-white bg-opacity-10 shadow-sm overflow-hidden" style="border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(5px);">
                            <input type="text" name="employee_id_string" class="form-control border-0 bg-transparent py-2 ps-3 fw-600 text-white placeholder-white-50" id="employeeId" required style="box-shadow: none;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bundyCode" class="form-label text-white opacity-90 small fw-800 mb-1 ms-2">
                            <i class="bi bi-shield-lock text-white me-1"></i> BUNDY CODE
                        </label>
                        <div class="border rounded-4 p-1 bg-white bg-opacity-10 shadow-sm overflow-hidden" style="border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(5px);">
                            <input type="password" name="web_bundy_code" class="form-control border-0 bg-transparent py-2 ps-3 fw-600 text-white placeholder-white-50" id="bundyCode" required style="box-shadow: none;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white opacity-90 small fw-800 mb-2 ms-2 d-flex align-items-center">
                            PUNCH SELECTION: <span id="currentPunchText" class="ms-2 badge bg-white bg-opacity-20 text-white border border-white border-opacity-50 px-2 py-1" style="font-size: 0.65rem; font-weight: 900; letter-spacing: 0.5px;">AM IN (START)</span>
                            <span class="ms-auto"><i class="bi bi-broadcast text-white pulse-icon"></i></span>
                        </label>
                        
                        <!-- Tab-style Punch Selector for space saving -->
                        <div class="row g-1">
                            <!-- Main Shifts -->
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="am_in" value="am_in" checked autocomplete="off" onchange="updatePunchSelectionDisplay('AM IN (START)')">
                                <label class="btn btn-outline-info w-100 py-2 border-0 bg-white bg-opacity-10 shadow-sm d-flex flex-column align-items-center punch-type-btn rounded-3 text-info" for="am_in">
                                    <i class="bi bi-box-arrow-in-right fs-5 mb-0"></i>
                                    <span class="fw-800" style="font-size: 0.65rem;">IN</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="pm_out" value="pm_out" autocomplete="off" onchange="updatePunchSelectionDisplay('PM OUT (END)')">
                                <label class="btn btn-outline-danger w-100 py-2 border-0 bg-white bg-opacity-10 shadow-sm d-flex flex-column align-items-center punch-type-btn rounded-3 text-danger" for="pm_out">
                                    <i class="bi bi-box-arrow-right fs-5 mb-0"></i>
                                    <span class="fw-800" style="font-size: 0.65rem;">OUT</span>
                                </label>
                            </div>

                            <!-- Integrated Break/Lunch Toggle Grid -->
                            <div class="col-12 mt-1">
                                <div class="p-2 bg-white bg-opacity-10 rounded-4 d-flex gap-1 shadow-sm border border-white border-opacity-20" style="backdrop-filter: blur(10px);">
                                    <div class="flex-grow-1">
                                        <div class="dropdown w-100">
                                            <button class="btn btn-white bg-white bg-opacity-10 text-warning w-100 py-1 border-0 shadow-sm dropdown-toggle fw-800" type="button" data-bs-toggle="dropdown" id="breakDropdownBtn" style="font-size: 0.65rem;">
                                                <i class="bi bi-cup-hot me-1"></i> BREAKS
                                            </button>
                                            <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-4 bg-dark bg-opacity-95 text-white" style="backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1) !important;">
                                                <li>
                                                    <div class="d-flex gap-1 p-1">
                                                        <input type="radio" class="btn-check" name="punch_type" id="break1_out" value="break1_out" onchange="updatePunchSelectionDisplay('1st BREAK OUT')">
                                                        <label class="btn btn-outline-warning flex-grow-1 py-2 rounded-3 border-0 bg-white bg-opacity-10 text-warning" for="break1_out" style="font-size: 0.7rem;">1st OUT</label>
                                                        <input type="radio" class="btn-check" name="punch_type" id="break1_in" value="break1_in" onchange="updatePunchSelectionDisplay('1st BREAK IN')">
                                                        <label class="btn btn-outline-warning flex-grow-1 py-2 rounded-3 border-0 bg-white bg-opacity-10 text-warning" for="break1_in" style="font-size: 0.7rem;">1st IN</label>
                                                    </div>
                                                </li>
                                                <li><hr class="dropdown-divider border-white opacity-20"></li>
                                                <li>
                                                    <div class="d-flex gap-1 p-1">
                                                        <input type="radio" class="btn-check" name="punch_type" id="break2_out" value="break2_out" onchange="updatePunchSelectionDisplay('2nd BREAK OUT')">
                                                        <label class="btn btn-outline-success flex-grow-1 py-2 rounded-3 border-0 bg-white bg-opacity-10 text-success" for="break2_out" style="font-size: 0.7rem;">2nd OUT</label>
                                                        <input type="radio" class="btn-check" name="punch_type" id="break2_in" value="break2_in" onchange="updatePunchSelectionDisplay('2nd BREAK IN')">
                                                        <label class="btn btn-outline-success flex-grow-1 py-2 rounded-3 border-0 bg-white bg-opacity-10 text-success" for="break2_in" style="font-size: 0.7rem;">2nd IN</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="dropdown w-100">
                                            <button class="btn btn-white bg-white bg-opacity-10 text-danger w-100 py-1 border-0 shadow-sm dropdown-toggle fw-800" type="button" data-bs-toggle="dropdown" id="lunchDropdownBtn" style="font-size: 0.65rem;">
                                                <i class="bi bi-egg-fried me-1"></i> LUNCH
                                            </button>
                                            <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-4 bg-dark bg-opacity-95 text-white" style="backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1) !important;">
                                                <li>
                                                    <div class="d-flex gap-1 p-1">
                                                        <input type="radio" class="btn-check" name="punch_type" id="am_out" value="am_out" onchange="updatePunchSelectionDisplay('LUNCH OUT')">
                                                        <label class="btn btn-outline-danger flex-grow-1 py-2 rounded-3 border-0 bg-white bg-opacity-10 text-danger" for="am_out" style="font-size: 0.7rem;">OUT</label>
                                                        <input type="radio" class="btn-check" name="punch_type" id="pm_in" value="pm_in" onchange="updatePunchSelectionDisplay('LUNCH IN')">
                                                        <label class="btn btn-outline-danger flex-grow-1 py-2 rounded-3 border-0 bg-white bg-opacity-10 text-danger" for="pm_in" style="font-size: 0.7rem;">IN</label>
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
                        <button type="submit" class="btn btn-white btn-lg fw-900 py-3 rounded-4 shadow-lg text-uppercase tracking-widest border-0 text-dark bg-white shadow-lg">
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
            <div class="card-header border-0 bg-white bg-opacity-10 text-white text-center py-3 rounded-3 shadow-lg" onclick="toggleCard('login')" style="cursor: pointer; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1) !important;">
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
            <div class="card-body p-4 p-xl-5 bg-white bg-opacity-10 rounded-bottom-4 shadow-sm" style="backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-top: none;">
                @if ($errors->any() && !$errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']))
                    <div class="alert alert-danger border-0 shadow-sm py-3 mb-4 small d-flex align-items-center rounded-3 bg-danger bg-opacity-20 text-white">
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
                    <div class="mb-3">
                        <label for="email" class="form-label text-white opacity-90 small fw-800 mb-1 ms-2">
                            <i class="bi bi-envelope text-white me-1"></i> EMAIL ADDRESS
                        </label>
                        <div class="border rounded-4 p-1 bg-white bg-opacity-10 shadow-sm overflow-hidden" style="border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(5px);">
                            <input type="email" name="email" class="form-control border-0 bg-transparent py-2 ps-3 fw-600 text-white placeholder-white-50" id="email" required style="box-shadow: none;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label text-white opacity-90 small fw-800 mb-1 ms-2">
                            <i class="bi bi-lock text-white me-1"></i> PASSWORD
                        </label>
                        <div class="border rounded-4 p-1 bg-white bg-opacity-10 shadow-sm overflow-hidden position-relative" style="border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(5px);">
                            <input type="password" name="password" class="form-control border-0 bg-transparent py-2 ps-3 fw-600 text-white placeholder-white-50" id="password" required style="box-shadow: none;">
                            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-white opacity-50 pe-3" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4 px-2">
                        <div class="form-check form-switch p-0">
                            <input type="checkbox" name="remember" class="form-check-input ms-0 me-2" id="remember" style="cursor: pointer;">
                            <label class="form-check-label text-white opacity-75 small fw-700" for="remember" style="cursor: pointer;">Remember Device</label>
                        </div>
                    </div>

                    <div class="d-grid shadow-lg rounded-4 overflow-hidden">
                        <button type="submit" class="btn btn-white btn-lg fw-900 py-3 border-0 text-uppercase tracking-widest text-dark bg-white shadow-lg">
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
    /* Card Transition Logic */
    .auth-card .card-body {
        max-height: 1000px;
        opacity: 1;
        overflow: visible;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 1.5rem 3rem !important;
    }

    .auth-card.minimized .card-body {
        max-height: 0 !important;
        opacity: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        pointer-events: none;
        border: none !important;
    }

    .auth-card.minimized .card-header {
        border-radius: 1rem !important; /* Fully rounded when closed */
        margin-bottom: 0.5rem;
    }

    .auth-card.active-form .card-header {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .auth-card.minimized {
        cursor: pointer;
        transform: scale(0.98);
        opacity: 0.8;
    }

    .auth-card.minimized:hover {
        opacity: 1;
        transform: scale(1);
        background: rgba(255, 255, 255, 0.15);
    }

    .auth-card {
        transition: all 0.4s ease;
    }

    .active-form .d-active { display: block !important; }
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

    function updatePunchSelectionDisplay(text) {
        const display = document.getElementById('currentPunchText');
        const badge = display;
        
        display.innerText = text;
        
        // Dynamic color coding - Optimized for readability on glass
        badge.className = 'ms-2 badge border px-2 py-1';
        badge.style.fontWeight = '900';
        badge.style.letterSpacing = '0.5px';
        
        if (text.includes('IN')) {
            badge.classList.add('bg-info', 'text-white', 'border-info');
        } else if (text.includes('OUT')) {
            badge.classList.add('bg-danger', 'text-white', 'border-danger');
        } else if (text.includes('BREAK')) {
            badge.classList.add('bg-warning', 'text-dark', 'border-warning');
        } else if (text.includes('LUNCH')) {
            badge.classList.add('bg-danger', 'text-white', 'border-danger');
        }
    }

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
