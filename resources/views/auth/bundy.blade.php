<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Bundy - HRIS Payroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .bundy-card { width: 100%; max-width: 450px; border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .digital-clock { font-size: 3rem; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .date-display { font-size: 1.1rem; color: #7f8c8d; margin-bottom: 25px; }
        .btn-punch { padding: 12px; font-weight: bold; font-size: 0.9rem; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="container">
    <div class="card bundy-card mx-auto">
        <div class="card-body p-4 text-center">
            <h4 class="fw-bold mb-4">WEB BUNDY SYSTEM</h4>
            
            <div id="clock" class="digital-clock">00:00:00</div>
            <div id="date" class="date-display">Loading date...</div>

            @if(session('bundy_success'))
                <div class="alert alert-success small mb-4">{{ session('bundy_success') }}</div>
            @endif

            @if(session('bundy_error'))
                <div class="alert alert-danger small mb-4">{{ session('bundy_error') }}</div>
            @endif

            <form action="{{ route('bundy.punch') }}" method="POST">
                @csrf
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Employee ID</label>
                    <input type="text" name="employee_id_string" class="form-control form-control-lg" placeholder="e.g. EMP-001" required autofocus>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label small fw-bold">Bundy Passcode</label>
                    <input type="password" name="web_bundy_code" class="form-control form-control-lg text-center" maxlength="4" placeholder="••••" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <button type="submit" name="punch_type" value="am_in" class="btn btn-success w-100 btn-punch shadow-sm">AM IN</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" name="punch_type" value="am_out" class="btn btn-outline-success w-100 btn-punch">Break Out</button>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="submit" name="punch_type" value="pm_in" class="btn btn-outline-primary w-100 btn-punch">Break In</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" name="punch_type" value="pm_out" class="btn btn-primary w-100 btn-punch shadow-sm">PM OUT</button>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('login') }}" class="text-decoration-none small text-muted">Back to Admin Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateTime() {
        const now = new Date();
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        
        document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', timeOptions);
        document.getElementById('date').textContent = now.toLocaleDateString('en-US', dateOptions);
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>

</body>
</html>
