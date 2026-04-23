<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $systemSettings->app_name ?? 'HRIS' }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
            position: relative;
        }

        /* Video Background */
        .video-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: #000; /* Fallback */
        }
        .video-bg iframe {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 56.25vw; /* 16:9 ratio */
            min-height: 100vh;
            min-width: 177.77vh; /* 16:9 ratio */
            transform: translate(-50%, -50%);
            object-fit: cover;
            pointer-events: none;
            opacity: 1; /* Reset to full visibility for the stripes */
            filter: contrast(1.2) brightness(0.8); /* Enhance the white lines */
        }
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.9) 100%);
            z-index: -1;
        }

        .login-container { 
            width: 100%; 
            max-width: 1100px; 
            padding: 20px;
            perspective: 1000px;
        }
        
        .auth-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            width: 100%;
            min-height: 750px;
            animation: cardEntrance 1s ease-out forwards;
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(50px) rotateX(-5deg); }
            to { opacity: 1; transform: translateY(0) rotateX(0); }
        }

        .animation-side {
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            color: white;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-side {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px);
        }

        .card { border: none; background: transparent; }
        
        .btn-primary { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
            transition: all 0.3s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3); }

        .btn-danger { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.2);
        }

        .auth-card.minimized { 
            height: 65px !important; 
            opacity: 0.5; 
            filter: grayscale(1);
            transition: all 0.4s;
        }
        .auth-card.minimized:hover { opacity: 0.8; filter: grayscale(0.5); }

        @media (max-width: 992px) {
            .auth-wrapper { grid-template-columns: 1fr; }
            .animation-side { display: none; }
            .login-container { max-width: 550px; }
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
    <div class="video-bg">
        <!-- New Background Video (10s Loop) -->
        <iframe src="https://www.youtube.com/embed/9vntypeV5QU?autoplay=1&mute=1&controls=0&loop=1&playlist=9vntypeV5QU&start=360&end=370&showinfo=0&rel=0&enablejsapi=1&origin={{ urlencode(url('/')) }}" frameborder="0" allow="autoplay; encrypted-media"></iframe>
    </div>
    <div class="video-overlay"></div>

    <div class="login-container">
        <div class="auth-wrapper">
            <!-- Left Side / Branding -->
            <div class="animation-side d-flex flex-column align-items-center justify-content-center text-center p-5">
                <div class="animate-bounce">
                    @if($systemSettings->app_logo)
                        <img src="{{ asset('storage/' . $systemSettings->app_logo) }}" alt="Logo" style="height: 120px; width: auto; max-width: 280px; object-fit: contain; filter: drop-shadow(0 0 30px rgba(255,255,255,0.5));" class="mb-4">
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
</html>