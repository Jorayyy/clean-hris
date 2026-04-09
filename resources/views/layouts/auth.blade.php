<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $systemSettings->app_name ?? 'HRIS' }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(239, 68, 68, 0.05) 0px, transparent 50%);
        }
        .login-container { width: 100%; max-width: 1000px; padding: 20px; }
        .card { border: none; transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important; }
        .btn-check:checked + .btn { border-width: 2px; border-color: inherit !important; background-color: rgba(var(--bs-primary-rgb), 0.05); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .punch-type-btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid transparent !important; }
        .punch-type-btn:hover { transform: translateY(-5px); background-color: #fff !important; box-shadow: 0 8px 20px rgba(0,0,0,0.1); border-color: currentColor !important; }
        .punch-type-btn i { transition: transform 0.3s; }
        .punch-type-btn:hover i { transform: scale(1.2); }
        .form-floating > .form-control { padding-top: 1.625rem; padding-bottom: 0.625rem; height: calc(3.5rem + 2px) !important; line-height: 1.25; }
        .form-floating > label { padding: 1rem 0.75rem; transition: opacity .1s ease-in-out,transform .1s ease-in-out; }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.1); border-color: #3b82f6; }
        .tracking-wider { letter-spacing: 0.1em; }
        .glass-dark { background: rgba(30, 41, 59, 0.95); backdrop-filter: blur(10px); }
        .logo-section { margin-bottom: 2rem; }
        .logo-img { height: 60px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
        
        /* Minimizing logic */
        .auth-card { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .auth-card.minimized { 
            height: auto !important; 
            max-height: 80px;
            overflow: hidden; 
            opacity: 0.9; 
            margin-bottom: 1.5rem !important;
            border-radius: 12px !important;
        }
        .auth-card.minimized:hover { opacity: 1; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .auth-card.minimized .card-body { display: none !important; }
        .auth-card.minimized .card-header { 
            border-radius: 12px !important; 
            min-height: 80px; 
            height: auto !important;
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            padding: 1rem 0 !important; 
        }
        /* Hide subtext in minimized mode to keep it button-like */
        .auth-card.minimized .card-header p { display: none !important; }
        .auth-card.minimized .card-header .icon-box { 
            margin-bottom: 0.25rem !important; 
            margin-right: 0 !important; 
            padding: 4px !important; 
            width: 36px !important; 
            height: 36px !important; 
        }
        .auth-card.minimized .card-header i { font-size: 1rem !important; }
        .auth-card.minimized .card-header h4 { margin-bottom: 0 !important; font-size: 1.1rem; line-height: 1.2; display: block !important; padding-bottom: 5px; }
        
        .active-form { 
            z-index: 10; 
        }
        .active-form .card-header {
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
        }
        @media (max-width: 768px) {
            .login-container { max-width: 500px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center logo-section">
            @if($systemSettings->app_logo)
                <img src="{{ asset('storage/' . $systemSettings->app_logo) }}" alt="Logo" class="logo-img mb-3">
            @endif
            <h2 class="fw-800 text-dark mb-1">{{ $systemSettings->app_name }}</h2>
            <p class="text-muted small">Human Resource & Payroll Management System</p>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>