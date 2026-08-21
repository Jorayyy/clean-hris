@extends('layouts.auth')

@section('content')
<div class="h-100 d-flex flex-column align-items-center justify-content-center p-2 p-md-4">
    <div class="w-100 auth-stack" style="max-width: 460px;">
        <div class="mb-4 text-center">
            <p class="overline mb-2">Centralized Management System</p>
            <h1 class="fw-bold mb-1" style="font-size: 2rem; letter-spacing: -0.03em; color: #1d1d1f;">Welcome back</h1>
            <p class="mb-0" style="font-size: 0.9rem; color: #6e6e73;">Punch your time or sign in to the portal.</p>
        </div>

        <div class="d-flex flex-column align-items-center w-100" style="gap: 1rem;">
            <div class="w-100" id="bundyCol">
                <div class="card shadow-none border-0 auth-card {{ session('bundy_success') || session('bundy_error') || $errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']) ? 'active-form' : 'minimized' }}" id="bundyCard">
                    <div class="card-header border-0 text-start py-3 px-4" onclick="toggleCard('bundy')" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="header-chip">
                                <i class="bi bi-fingerprint"></i>
                            </div>
                            <div class="min-width-0">
                                <h5 class="mb-0 fw-semibold" style="font-size: 0.95rem; letter-spacing: -0.01em;">Web Bundy</h5>
                                <p class="small mb-0 d-none d-active" id="liveClock" style="color: rgba(60,60,67,0.5); font-size: 0.78rem; font-variant-numeric: tabular-nums;"></p>
                            </div>
                            <i class="bi bi-chevron-down ms-auto section-chevron"></i>
                        </div>
                    </div>
                    <div class="card-body p-4 p-xl-5">
                        <form action="{{ route('bundy.punch') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="employeeId" class="form-label">Employee ID</label>
                                <input type="text" name="employee_id_string" class="form-control" id="employeeId" placeholder="e.g. 01234" required autocomplete="off">
                            </div>

                            <div class="mb-3">
                                <label for="bundyCode" class="form-label">Bundy Code</label>
                                <input type="password" name="web_bundy_code" class="form-control" id="bundyCode" placeholder="Your personal code" required>
                            </div>

                            <div class="mb-4">
                                <label for="punchSelectionDropdown" class="form-label d-flex align-items-center">
                                    Punch Type
                                    <span class="ms-auto"><i class="bi bi-broadcast pulse-icon" style="color: #30d158; font-size: 0.8rem;"></i></span>
                                </label>
                                <select name="punch_type" class="form-select punch-select" id="punchSelectionDropdown" required>
                                    <optgroup label="Main Shift">
                                        <option value="am_in" selected>AM In (Start)</option>
                                        <option value="pm_out">PM Out (End)</option>
                                    </optgroup>
                                    <optgroup label="Lunch Break">
                                        <option value="am_out">Lunch Out</option>
                                        <option value="pm_in">Lunch In</option>
                                    </optgroup>
                                    <optgroup label="1st Break">
                                        <option value="break1_out">1st Break Out</option>
                                        <option value="break1_in">1st Break In</option>
                                    </optgroup>
                                    <optgroup label="2nd Break">
                                        <option value="break2_out">2nd Break Out</option>
                                        <option value="break2_in">2nd Break In</option>
                                    </optgroup>
                                </select>
                                <div class="punch-preview mt-2" id="punchPreview"></div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">Punch Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="w-100" id="loginCol">
                <div class="card shadow-none border-0 auth-card {{ !session('bundy_success') && !session('bundy_error') && !$errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']) ? 'active-form' : 'minimized' }}" id="loginCard">
                    <div class="card-header border-0 text-start py-3 px-4" onclick="toggleCard('login')" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="header-chip">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div class="min-width-0">
                                <h5 class="mb-0 fw-semibold" style="font-size: 0.95rem; letter-spacing: -0.01em;">Portal Login</h5>
                                <p class="small mb-0 d-none d-active" style="color: rgba(60,60,67,0.5); font-size: 0.78rem;">Secure staff dashboard</p>
                            </div>
                            <i class="bi bi-chevron-down ms-auto section-chevron"></i>
                        </div>
                    </div>
                    <div class="card-body p-4 p-xl-5">
                        @if ($errors->any() && !$errors->hasAny(['employee_id_string', 'web_bundy_code', 'punch_type']))
                            <div class="alert alert-danger py-3 mb-4 small d-flex align-items-start rounded-3">
                                <i class="bi bi-exclamation-octagon-fill me-2 mt-1"></i>
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="name@company.com" required autocomplete="username">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="position-relative">
                                    <input type="password" name="password" class="form-control pe-5" id="password" placeholder="Enter your password" required autocomplete="current-password">
                                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" id="togglePassword" tabindex="-1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check form-switch p-0 m-0">
                                    <input type="checkbox" name="remember" class="form-check-input ms-0 me-2 ios-switch" id="remember">
                                    <label class="form-check-label small" for="remember" style="color: rgba(60,60,67,0.65); cursor: pointer;">Remember this device</label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">Sign In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="https://github.com/Jorayyy" target="_blank" class="text-decoration-none d-inline-flex align-items-center gap-2 signature-link">
                <i class="bi bi-github"></i>
                <span>Jorayyy</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .auth-stack { display: flex; flex-direction: column; }

    .overline {
        font-size: 0.68rem; font-weight: 600;
        letter-spacing: 0.14em; text-transform: uppercase;
        color: rgba(60,60,67,0.45);
    }

    .header-chip {
        width: 38px; height: 38px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 12px; font-size: 1.05rem; color: #fff;
        background: linear-gradient(135deg, rgba(0,113,227,0.9), rgba(0,88,176,0.9));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 2px 8px rgba(0,113,227,0.25);
    }
    #bundyCard .header-chip {
        background: linear-gradient(135deg, rgba(52,199,89,0.85), rgba(24,138,56,0.9));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 2px 8px rgba(52,199,89,0.25);
    }

    .section-chevron {
        color: rgba(0,0,0,0.28); font-size: 0.8rem;
        transition: transform 0.3s cubic-bezier(0.25,0.1,0.25,1);
    }

    .form-label {
        font-size: 0.78rem; font-weight: 500;
        color: rgba(60,60,67,0.55);
        margin-bottom: 0.4rem; padding-left: 0.15rem;
    }

    .punch-select {
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        cursor: pointer; padding-right: 40px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 fill=%22rgba%2860,60,67,0.55%29%22 viewBox=%220 0 16 16%22><path fill-rule=%22evenodd%22 d=%22M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z%22/></svg>');
        background-repeat: no-repeat;
        background-position: calc(100% - 15px) center;
    }
    .punch-select optgroup { color: rgba(60,60,67,0.5); background: #fff; }
    .punch-select option { color: #1d1d1f; background: #fff; }

    .punch-select optgroup[label="Main Shift"] { color: #0062cc; }
    .punch-select optgroup[label="Lunch Break"] { color: #d70015; }
    .punch-select optgroup[label="1st Break"] { color: #995f00; }
    .punch-select optgroup[label="2nd Break"] { color: #248a3d; }

    .punch-preview {
        display: inline-flex; align-items: center; gap: 0.45rem;
        font-size: 0.75rem; font-weight: 500;
        padding: 0.3rem 0.7rem; border-radius: 980px;
        transition: background-color 0.25s ease, color 0.25s ease;
    }
    .punch-preview .pv-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 8px currentColor;
    }
    .punch-preview.pv-blue { background: rgba(0,113,227,0.10); color: #0062cc; }
    .punch-preview.pv-red { background: rgba(255,59,48,0.10); color: #d70015; }
    .punch-preview.pv-amber { background: rgba(255,159,10,0.14); color: #995f00; }
    .punch-preview.pv-green { background: rgba(52,199,89,0.12); color: #248a3d; }

    .ios-switch { cursor: pointer; }
    .ios-switch:checked { background-color: #34c759; border-color: #34c759; }

    #togglePassword {
        color: rgba(60,60,67,0.4); text-decoration: none;
        padding: 0.375rem 0.9rem;
    }
    #togglePassword:hover { color: rgba(60,60,67,0.75); }

    .signature-link {
        gap: 0.4rem; opacity: 0.55; color: #86868b;
        font-size: 0.68rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase;
        transition: opacity 0.2s ease, color 0.2s ease;
    }
    .signature-link:hover { opacity: 1; color: #1d1d1f; }

    .pulse-icon { animation: pulse-lite 2s ease-in-out infinite; }

    .auth-card {
        transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), opacity 0.4s ease;
        will-change: transform, opacity;
        backface-visibility: hidden;
        position: relative;
    }

    .auth-stack > * {
        animation: rise-in 0.65s cubic-bezier(0.25, 0.1, 0.25, 1) backwards;
    }
    .auth-stack > *:nth-child(1) { animation-delay: 0.02s; }
    .auth-stack > *:nth-child(2) { animation-delay: 0.12s; }
    .auth-stack > *:nth-child(3) { animation-delay: 0.22s; }

    @keyframes rise-in {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .auth-card.active-form::before {
        content: "";
        position: absolute;
        inset: -3px;
        border-radius: 24px;
        z-index: -1;
        background: linear-gradient(135deg, rgba(0, 113, 227, 0.35), rgba(94, 92, 230, 0.22));
        filter: blur(18px);
        opacity: 0.5;
        animation: breathe 7s ease-in-out infinite;
        pointer-events: none;
    }

    #bundyCard.active-form::before {
        background: linear-gradient(135deg, rgba(52, 199, 89, 0.30), rgba(0, 113, 227, 0.20));
    }

    @keyframes breathe {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.65; }
    }

    .auth-card.active-form .header-chip {
        animation: chip-glow-blue 3.5s ease-in-out infinite;
    }

    #bundyCard.active-form .header-chip {
        animation-name: chip-glow-green;
    }

    @keyframes chip-glow-blue {
        0%, 100% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 2px 8px rgba(0,113,227,0.25); }
        50% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 2px 8px rgba(0,113,227,0.25), 0 0 22px 4px rgba(0,113,227,0.30); }
    }

    @keyframes chip-glow-green {
        0%, 100% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 2px 8px rgba(52,199,89,0.25); }
        50% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 2px 8px rgba(52,199,89,0.25), 0 0 22px 4px rgba(52,199,89,0.28); }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-stack > *,
        .auth-card.active-form::before,
        .auth-card.active-form .header-chip,
        #bundyCard.active-form .header-chip,
        .pulse-icon { animation: none !important; }
    }

    .auth-card .card-body {
        max-height: 1000px;
        opacity: 1;
        overflow: visible;
        transition: max-height 0.4s ease-out, opacity 0.3s ease, padding 0.4s ease;
    }

    .auth-card.minimized {
        cursor: pointer;
        opacity: 0.65;
        transform: scale(0.985);
    }
    .auth-card.minimized:hover { opacity: 0.9; transform: scale(0.99); }

    .auth-card.minimized .card-body {
        max-height: 0 !important;
        opacity: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        pointer-events: none;
        border: none !important;
        transition: max-height 0.3s ease-in, opacity 0.2s ease, padding 0.3s ease;
    }

    .auth-card.minimized .card-header {
        margin-bottom: 0;
    }
    .auth-card.minimized .section-chevron { transform: rotate(-90deg); }

    .auth-card.active-form { z-index: 2; transform: scale(1); }
    .auth-card.active-form .card-header {
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(0,0,0,0.07);
        border-radius: 20px 20px 0 0 !important;
    }
    .auth-card.active-form .card-body {
        background: rgba(255, 255, 255, 0.45);
        border: 1px solid rgba(0,0,0,0.07);
        border-top: none;
        border-radius: 0 0 20px 20px;
    }
    .active-form .d-active { display: block !important; }
    .active-form .section-chevron { transform: rotate(180deg); }

    .min-width-0 { min-width: 0; }

    @media (max-width: 768px) {
        .auth-stack { gap: 0.75rem; }

        #bundyCol { order: 2; }
        #loginCol { order: 1; }

        #bundyCard.minimized,
        #loginCard.minimized { display: none !important; }

        #loginCard .card-body,
        #bundyCard .card-body { padding: 1rem !important; }

        #loginCard .card-header,
        #bundyCard .card-header { padding: 0.85rem 1rem !important; }
    }

    @media (max-width: 576px) {
        #loginCard .form-control,
        #bundyCard .form-control,
        #bundyCard .form-select { font-size: 16px; }

        #loginCard .btn,
        #bundyCard .btn { min-height: 48px; }
    }
</style>
<script>
    function setMobileAuthDefaults() {
        if (window.innerWidth <= 768) {
            const bundyCard = document.getElementById('bundyCard');
            const loginCard = document.getElementById('loginCard');
            if (bundyCard && loginCard && !bundyCard.classList.contains('active-form') && !loginCard.classList.contains('active-form')) {
                loginCard.classList.add('active-form');
                loginCard.classList.remove('minimized');
                bundyCard.classList.add('minimized');
                bundyCard.classList.remove('active-form');
            }
        }
    }

    function toggleCard(type) {
        const bundyCol = document.getElementById('bundyCol');
        const loginCol = document.getElementById('loginCol');
        const bundyCard = document.getElementById('bundyCard');
        const loginCard = document.getElementById('loginCard');

        if (type === 'bundy' && bundyCard.classList.contains('active-form')) return;
        if (type === 'login' && loginCard.classList.contains('active-form')) return;

        if (type === 'bundy') {
            bundyCard.classList.remove('minimized');
            bundyCard.classList.add('active-form');
            loginCard.classList.add('minimized');
            loginCard.classList.remove('active-form');
            bundyCol.parentElement.prepend(bundyCol);
        } else {
            loginCard.classList.remove('minimized');
            loginCard.classList.add('active-form');
            bundyCard.classList.add('minimized');
            bundyCard.classList.remove('active-form');
            loginCol.parentElement.prepend(loginCol);
        }
    }

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit' };
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

    const punchGroups = {
        am_in:      { label: 'Main Shift · AM In',  cls: 'pv-blue' },
        pm_out:     { label: 'Main Shift · PM Out', cls: 'pv-blue' },
        am_out:     { label: 'Lunch Break · Out',   cls: 'pv-red' },
        pm_in:      { label: 'Lunch Break · In',    cls: 'pv-red' },
        break1_out: { label: '1st Break · Out',     cls: 'pv-amber' },
        break1_in:  { label: '1st Break · In',      cls: 'pv-amber' },
        break2_out: { label: '2nd Break · Out',     cls: 'pv-green' },
        break2_in:  { label: '2nd Break · In',      cls: 'pv-green' }
    };

    function updatePunchPreview() {
        const select = document.getElementById('punchSelectionDropdown');
        const preview = document.getElementById('punchPreview');
        if (!select || !preview) return;
        const info = punchGroups[select.value] || { label: select.value, cls: 'pv-blue' };
        preview.className = 'punch-preview mt-2 ' + info.cls;
        preview.innerHTML = '<span class="pv-dot"></span>' + info.label;
        if (typeof window.updatePunchSelectionDisplay === 'function') {
            window.updatePunchSelectionDisplay(info.label);
        }
    }
    const punchSelect = document.getElementById('punchSelectionDropdown');
    if (punchSelect) {
        punchSelect.addEventListener('change', updatePunchPreview);
        updatePunchPreview();
    }

    window.addEventListener('resize', setMobileAuthDefaults);
    setMobileAuthDefaults();
</script>
@endpush
@endsection
