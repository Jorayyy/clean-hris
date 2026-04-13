@extends('layouts.auth')

@section('content')
<div class="h-100 d-flex flex-column align-items-center justify-content-center p-4">
    <div class="w-100" style="max-width: 450px;">
        <!-- Unified Portal Container -->
        <div class="mb-4 text-center">
            <h1 class="text-white fw-900 tracking-tighter mb-0" style="font-size: 2.5rem;">ACCESS PORTAL</h1>
            <p class="text-white opacity-50 small fw-bold tracking-widest text-uppercase">Centralized Management System</p>
        </div>

        <div class="d-flex flex-column align-items-center w-100" style="gap: 1.5rem;">
            <!-- Bundy Section -->
            <div class="w-100" id="bundyCol">
                <div class="card shadow-none border-0 auth-card {{ session('bundy_success') || session('bundy_error') || $errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']) ? 'active-form' : 'minimized' }}" id="bundyCard">
                    <div class="card-header border-0 bg-white bg-opacity-10 text-white text-center py-3 rounded-4 shadow-lg" onclick="toggleCard('bundy')" style="cursor: pointer; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1) !important;">
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
                                    PUNCH SELECTION: 
                                    <span class="ms-auto"><i class="bi bi-broadcast text-white pulse-icon"></i></span>
                                </label>
                                
                                <div class="border rounded-4 p-1 bg-white bg-opacity-10 shadow-sm overflow-hidden" style="border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(5px);">
                                    <select name="punch_type" class="form-select border-0 bg-transparent py-2 ps-3 fw-800 text-white placeholder-white-50" id="punchSelectionDropdown" required style="box-shadow: none; cursor: pointer; font-size: 0.85rem; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 fill=%22white%22 class=%22bi bi-chevron-down%22 viewBox=%220 0 16 16%22><path fill-rule=%22evenodd%22 d=%22M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z%22/></svg>'); background-repeat: no-repeat; background-position: calc(100% - 15px) center; padding-right: 40px;">
                                        <optgroup label="MAIN SHIFT" class="bg-dark text-info">
                                            <option value="am_in" class="bg-dark text-white" selected>AM IN (START)</option>
                                            <option value="pm_out" class="bg-dark text-white">PM OUT (END)</option>
                                        </optgroup>
                                        <optgroup label="LUNCH BREAK" class="bg-dark text-danger">
                                            <option value="am_out" class="bg-dark text-white">LUNCH OUT</option>
                                            <option value="pm_in" class="bg-dark text-white">LUNCH IN</option>
                                        </optgroup>
                                        <optgroup label="1st BREAK" class="bg-dark text-warning">
                                            <option value="break1_out" class="bg-dark text-white">1st BREAK OUT</option>
                                            <option value="break1_in" class="bg-dark text-white">1st BREAK IN</option>
                                        </optgroup>
                                        <optgroup label="2nd BREAK" class="bg-dark text-success">
                                            <option value="break2_out" class="bg-dark text-white">2nd BREAK OUT</option>
                                            <option value="break2_in" class="bg-dark text-white">2nd BREAK IN</option>
                                        </optgroup>
                                    </select>
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
                    <div class="card-header border-0 bg-white bg-opacity-10 text-white text-center py-3 rounded-4 shadow-lg" onclick="toggleCard('login')" style="cursor: pointer; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1) !important;">
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
