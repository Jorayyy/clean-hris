@extends('layouts.auth')

@section('content')
<div class="d-flex flex-column align-items-center w-100" style="max-width: 600px; margin: 0 auto; gap: 1rem;">
    <!-- Web Bundy Section -->
    <div class="w-100" id="bundyCol">
        <div class="card shadow-sm rounded-4 border-0 auth-card {{ session('bundy_success') || session('bundy_error') ? '' : 'minimized' }}" id="bundyCard">
            <div class="card-header border-0 bg-danger text-white text-center py-4 rounded-top-4" onclick="toggleCard('bundy')">
                <div class="d-flex flex-column align-items-center header-content {{ session('bundy_success') || session('bundy_error') ? '' : 'w-100' }}">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 mb-3 shadow-sm icon-box" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-fingerprint fs-4 text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-800 tracking-wider">WEB BUNDY</h4>
                        <p class="small opacity-75 mb-0 font-monospace h5" id="liveClock"></p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-lg-5">
                @if (session('bundy_success'))
                    <div class="alert alert-success border-0 shadow-sm py-3 mb-4 small d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                        <div>{{ session('bundy_success') }}</div>
                    </div>
                @endif
                @if (session('bundy_error'))
                    <div class="alert alert-danger border-0 shadow-sm py-3 mb-4 small d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger"></i>
                        <div>{{ session('bundy_error') }}</div>
                    </div>
                @endif

                <form action="{{ route('bundy.punch') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-4 border rounded-3 p-1 bg-light">
                        <input type="text" name="employee_id_string" class="form-control border-0 bg-transparent h-auto py-2 ps-3" id="employeeId" placeholder="Employee ID" required style="box-shadow: none;">
                        <label for="employeeId" class="text-muted small fw-bold"><i class="bi bi-person-badge text-danger me-1"></i>Employee ID</label>
                    </div>

                    <div class="form-floating mb-4 border rounded-3 p-1 bg-light">
                        <input type="password" name="web_bundy_code" class="form-control border-0 bg-transparent h-auto py-2 ps-3" id="bundyCode" placeholder="Bundy Code" required style="box-shadow: none;">
                        <label for="bundyCode" class="text-muted small fw-bold"><i class="bi bi-shield-lock text-danger me-1"></i>Web Bundy Code</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold mb-3 ms-1 d-flex">
                            PUNCH TYPE <span class="ms-auto"><i class="bi bi-info-circle" data-bs-toggle="tooltip" title="Select your attendance action"></i></span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="am_in" value="am_in" checked autocomplete="off">
                                <label class="btn btn-outline-info w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="am_in">
                                    <i class="bi bi-box-arrow-in-right opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">IN</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="pm_out" value="pm_out" autocomplete="off">
                                <label class="btn btn-outline-info w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="pm_out">
                                    <i class="bi bi-box-arrow-right opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">OUT</span>
                                </label>
                            </div>
                            
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break1_out" value="break1_out" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="break1_out">
                                    <i class="bi bi-cup-hot opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">1st BRK OUT</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break1_in" value="break1_in" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="break1_in">
                                    <i class="bi bi-reply opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">1st BRK IN</span>
                                </label>
                            </div>

                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="am_out" value="am_out" autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="am_out">
                                    <i class="bi bi-egg-fried opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">LUNCH OUT</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="pm_in" value="pm_in" autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="pm_in">
                                    <i class="bi bi-reply-all opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">LUNCH IN</span>
                                </label>
                            </div>

                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break2_out" value="break2_out" autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="break2_out">
                                    <i class="bi bi-cup opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">2nd BRK OUT</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="punch_type" id="break2_in" value="break2_in" autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-3 border-0 bg-light shadow-sm h-100 d-flex flex-column align-items-center justify-content-center punch-type-btn" for="break2_in">
                                    <i class="bi bi-reply-all opacity-75 mb-1 fs-4"></i>
                                    <span class="small fw-800">2nd BRK IN</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid shadow-lg rounded-3 overflow-hidden mt-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-800 py-3 tracking-wider">
                            PUNCH NOW
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Section -->
    <div class="w-100" id="loginCol">
        <div class="card shadow-sm rounded-4 border-0 auth-card active-form" id="loginCard">
            <div class="card-header border-0 bg-primary text-white text-center py-4 rounded-top-4" onclick="toggleCard('login')">
                <div class="d-flex flex-column align-items-center header-content">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 mb-3 shadow-sm icon-box" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-lock-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-800 tracking-wider">PORTAL LOGIN</h4>
                        <p class="small opacity-75 mb-0">Sign in to your account</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-lg-5 d-flex flex-column">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm py-3 mb-4 small d-flex align-items-center">
                        <i class="bi bi-exclamation-octagon-fill me-2 fs-5 text-danger"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div class="fw-bold">{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-4 border rounded-3 p-1 bg-light">
                        <input type="email" name="email" class="form-control border-0 bg-transparent h-auto py-2 ps-3" id="email" placeholder="name@example.com" required style="box-shadow: none;">
                        <label for="email" class="text-muted small fw-bold">Email Address</label>
                    </div>
                    
                    <div class="form-floating mb-4 border rounded-3 p-1 bg-light position-relative">
                        <input type="password" name="password" class="form-control border-0 bg-transparent h-auto py-2 ps-3" id="password" placeholder="Password" required style="box-shadow: none;">
                        <label for="password" class="text-muted small fw-bold">Password</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted opacity-50 pe-3" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4 ms-1">
                        <div class="form-check form-switch p-0">
                            <input type="checkbox" name="remember" class="form-check-input ms-0 me-2" id="remember" style="cursor: pointer;">
                            <label class="form-check-label text-muted small fw-600" for="remember" style="cursor: pointer;">Keep me logged in</label>
                        </div>
                    </div>

                    <div class="d-grid shadow-lg rounded-3 overflow-hidden">
                        <button type="submit" class="btn btn-primary btn-lg fw-800 py-3 tracking-wider">
                            SIGN IN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleCard(type) {
        const bundyCol = document.getElementById('bundyCol');
        const loginCol = document.getElementById('loginCol');
        const bundyCard = document.getElementById('bundyCard');
        const loginCard = document.getElementById('loginCard');

        if (type === 'bundy') {
            // Move Bundy to the bottom (Active) and Login to the top (Inactive)
            bundyCol.parentElement.appendChild(bundyCol);
            
            bundyCard.classList.remove('minimized');
            bundyCard.classList.add('active-form');

            loginCard.classList.add('minimized');
            loginCard.classList.remove('active-form');
        } else {
            // Move Login to the bottom (Active) and Bundy to the top (Inactive)
            loginCol.parentElement.appendChild(loginCol);

            loginCard.classList.remove('minimized');
            loginCard.classList.add('active-form');

            bundyCard.classList.add('minimized');
            bundyCard.classList.remove('active-form');
        }
    }

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('liveClock').innerText = now.toLocaleDateString('en-US', options).replace(/,/g, '');
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

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush
@endsection
