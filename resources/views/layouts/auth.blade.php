<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $systemSettings->app_name ?? 'HRIS' }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --apple-blue: #0071e3;
            --apple-ease: cubic-bezier(0.25, 0.1, 0.25, 1);
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display",
                "Helvetica Neue", "Segoe UI", Roboto, Arial, sans-serif;
            background: #060607;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            overflow-y: auto;
            margin: 0;
            position: relative;
            color: #f5f5f7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.011em;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }
        body::before {
            width: 620px;
            height: 620px;
            top: -220px;
            left: -180px;
            background: radial-gradient(circle, rgba(0, 113, 227, 0.16) 0%, transparent 70%);
        }
        body::after {
            width: 520px;
            height: 520px;
            bottom: -200px;
            right: -140px;
            background: radial-gradient(circle, rgba(94, 92, 230, 0.12) 0%, transparent 70%);
        }

        ::selection {
            background: rgba(0, 113, 227, 0.35);
        }

        ::-webkit-scrollbar { width: 9px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 99px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .login-container {
            width: 100%;
            max-width: 1100px;
            padding: 20px;
            perspective: 1000px;
            position: relative;
            z-index: 1;
        }

        .auth-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            background: rgba(24, 24, 27, 0.55);
            backdrop-filter: blur(40px) saturate(160%);
            -webkit-backdrop-filter: blur(40px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 36px;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.06);
            overflow: hidden;
            width: 100%;
            min-height: 750px;
            animation: cardEntrance 0.8s var(--apple-ease) forwards;
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(28px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animation-side {
            background: rgba(255, 255, 255, 0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            color: white;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-side {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.02);
        }

        .card { border: none; background: transparent; }

        .btn {
            --bs-btn-border-radius: 980px;
            --bs-btn-font-weight: 500;
            letter-spacing: -0.006em;
            transition: background-color 0.2s var(--apple-ease), box-shadow 0.2s var(--apple-ease),
                transform 0.12s var(--apple-ease);
        }
        .btn:active { transform: scale(0.97); }

        .btn-primary {
            --bs-btn-bg: var(--apple-blue);
            --bs-btn-border-color: var(--apple-blue);
            --bs-btn-hover-bg: #0077ed;
            --bs-btn-hover-border-color: #0077ed;
            --bs-btn-active-bg: #0068d1;
            --bs-btn-active-border-color: #0068d1;
            --bs-btn-focus-shadow-rgb: 0, 113, 227;
            --bs-btn-focus-box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.3);
        }

        .btn-danger {
            --bs-btn-bg: #ff453a;
            --bs-btn-border-color: #ff453a;
            --bs-btn-hover-bg: #e03d33;
            --bs-btn-hover-border-color: #e03d33;
            --bs-btn-active-bg: #cc372e;
            --bs-btn-active-border-color: #cc372e;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #f5f5f7;
            border-radius: 12px;
            transition: border-color 0.2s var(--apple-ease), box-shadow 0.2s var(--apple-ease),
                background-color 0.2s var(--apple-ease);
        }
        .form-control::placeholder { color: rgba(235, 235, 245, 0.4); }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--apple-blue);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.28);
        }

        .auth-card.minimized {
            height: 65px !important;
            opacity: 0.5;
            filter: grayscale(1);
            transition: all 0.4s;
        }
        .auth-card.minimized:hover { opacity: 0.8; filter: grayscale(0.5); }

        @media (max-width: 992px) {
            body {
                align-items: flex-start;
                justify-content: flex-start;
            }

            .auth-wrapper { grid-template-columns: 1fr; }
            .animation-side { display: none; }
            .login-container { max-width: 550px; }
            .auth-wrapper {
                min-height: auto;
                border-radius: 28px;
            }
            .form-side {
                padding: 24px;
            }
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 12px;
            }

            .auth-wrapper {
                border-radius: 24px;
            }

            .form-side {
                padding: 18px 16px;
            }

            .auth-card .card-body {
                padding: 1rem 1rem !important;
            }

            .auth-card .card-header h5 {
                font-size: 0.95rem;
            }
        }
    </style>
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        @if(session('bundy_success'))
            <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>
                            <strong class="d-block">PUNCH SUCCESSFUL</strong>
                            <span class="small opacity-90">{{ session('bundy_success') }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('bundy_error'))
            <div id="errorToast" class="toast align-items-center text-white bg-danger border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            <strong class="d-block">PUNCH FAILED</strong>
                            <span class="small opacity-90">{{ session('bundy_error') }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
</head>
<body>
    <div class="login-container">
        <div class="auth-wrapper">
            <!-- Left Side / Branding -->
            <div class="animation-side d-flex flex-column align-items-center justify-content-center text-center p-5">
                <div class="animate-bounce">
                    @if($systemSettings->app_logo)
                        @php 
                            $logoPath = str_starts_with($systemSettings->app_logo, 'logos/') 
                                        ? $systemSettings->app_logo 
                                        : 'logos/' . $systemSettings->app_logo;
                        @endphp
                        <img src="/{{ $logoPath }}" alt="Logo" style="height: 120px; width: auto; max-width: 280px; object-fit: contain; filter: drop-shadow(0 0 30px rgba(255,255,255,0.35));" class="mb-4">
                    @endif
                    <h1 class="fw-900 tracking-tighter text-white mb-0" style="font-size: 3.5rem; letter-spacing: -3px; line-height: 1;">MEBS HIYAS</h1>
                </div>
            </div>

            <!-- Right Side / Form -->
            <div class="form-side">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCard(type) {
            const bundyCard = document.getElementById('bundyCard');
            const loginCard = document.getElementById('loginCard');
            if (!bundyCard || !loginCard) return;

            if (type === 'bundy') {
                bundyCard.classList.remove('minimized');
                bundyCard.classList.add('active-form');
                loginCard.classList.add('minimized');
                loginCard.classList.remove('active-form');
            } else if (type === 'login') {
                loginCard.classList.remove('minimized');
                loginCard.classList.add('active-form');
                bundyCard.classList.add('minimized');
                bundyCard.classList.remove('active-form');
            }
        }

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true, 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const clockEl = document.getElementById('liveClock');
            if (clockEl) clockEl.textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function updatePunchSelectionDisplay(text) {
            const displayEl = document.getElementById('currentPunchText');
            if (displayEl) {
                displayEl.textContent = text;
                // Add a small animation to grab attention
                displayEl.style.animation = 'none';
                displayEl.offsetHeight; // trigger reflow
                displayEl.style.animation = 'pulse-lite 0.5s ease-out';
            }
        }
    </script>
    <style>
        @keyframes pulse-lite {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successToastEl = document.getElementById('successToast');
            const errorToastEl = document.getElementById('errorToast');
            
            if (successToastEl) {
                const toast = new bootstrap.Toast(successToastEl, { delay: 5000 });
                toast.show();
            }
            if (errorToastEl) {
                const toast = new bootstrap.Toast(errorToastEl, { delay: 5000 });
                toast.show();
            }
        });
    </script>
</body>
</html>
