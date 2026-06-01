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
            
            @isset($unauthorized_ip)
                <div class="alert alert-danger shadow-sm border-0 py-4">
                    <h5 class="fw-bold text-uppercase mb-3"><i class="bi bi-shield-lock-fill me-2"></i> Access Denied</h5>
                    <p class="mb-2">This network is <strong>NOT AUTHORIZED</strong> for Web Bundy punches.</p>
                    <p class="small text-muted mb-0">Your IP: <code>{{ $unauthorized_ip }}</code></p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm px-4">Back to Login</a>
                </div>
            @else
            <div id="clock" class="digital-clock">00:00:00</div>
            <div id="date" class="date-display">Loading date...</div>

            @if(session('bundy_success'))
                <div class="alert alert-success small mb-4">{{ session('bundy_success') }}</div>
            @endif

            @if(session('bundy_error'))
                <div class="alert alert-danger small mb-4">{{ session('bundy_error') }}</div>
            @endif

            <form id="bundyForm" action="{{ route('bundy.punch') }}" method="POST">
                @csrf
                <!-- Default catch for Enter key -->
                <button type="submit" name="punch_type" value="none" style="display:none;"></button>

                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id_string" class="form-control form-control-lg" placeholder="e.g. EMP-001" required autofocus autocomplete="off">
                    <div id="employee_name" class="small fw-bold text-primary mt-1" style="display:none;"></div>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label small fw-bold">Bundy Passcode</label>
                    <input type="password" name="web_bundy_code" class="form-control form-control-lg text-center" maxlength="4" placeholder="••••" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <button type="submit" id="btn_am_in" name="punch_type" value="am_in" class="btn btn-success w-100 btn-punch shadow-sm">START SHIFT</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" id="btn_am_out" name="punch_type" value="am_out" class="btn btn-outline-success w-100 btn-punch text-nowrap">LUNCH OUT</button>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="submit" id="btn_pm_in" name="punch_type" value="pm_in" class="btn btn-outline-primary w-100 btn-punch text-nowrap">LUNCH IN</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" id="btn_pm_out" name="punch_type" value="pm_out" class="btn btn-primary w-100 btn-punch shadow-sm">END SHIFT</button>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('login') }}" class="text-decoration-none small text-muted">Back to Admin Login</a>
                </div>
            </form>
            @endisset
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

    // Employee Status Awareness
    const empInput = document.getElementById('employee_id');
    const empName = document.getElementById('employee_name');
    const btnIn = document.getElementById('btn_am_in');
    const btnLOut = document.getElementById('btn_am_out');
    const btnLIn = document.getElementById('btn_pm_in');
    const btnOut = document.getElementById('btn_pm_out');

    let debounceTimer;

    empInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const id = this.value.trim();
        
        if (id.length < 3) {
            resetButtons();
            empName.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/web-bundy/status/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resetButtons();
                        empName.textContent = 'Invalid ID';
                        empName.classList.replace('text-primary', 'text-danger');
                        empName.style.display = 'block';
                    } else {
                        empName.textContent = `Hello, ${data.full_name}`;
                        empName.classList.replace('text-danger', 'text-primary');
                        empName.style.display = 'block';

                        // Disable buttons based on status
                        btnIn.disabled = data.is_in;
                        btnLOut.disabled = !data.is_in || data.is_break1_out;
                        btnLIn.disabled = !data.is_break1_out || data.is_break1_in;
                        btnOut.disabled = !data.is_in || data.is_out;

                        // Add visual feedback
                        if (data.is_in) btnIn.innerHTML = 'ALREADY IN';
                        else btnIn.innerHTML = 'START SHIFT';

                        if (data.is_out) btnOut.innerHTML = 'ALREADY OUT';
                        else btnOut.innerHTML = 'END SHIFT';
                    }
                })
                .catch(() => {
                    resetButtons();
                });
        }, 500);
    });

    function resetButtons() {
        btnIn.disabled = false;
        btnLOut.disabled = false;
        btnLIn.disabled = false;
        btnOut.disabled = false;
        btnIn.innerHTML = 'START SHIFT';
        btnOut.innerHTML = 'END SHIFT';
    }

    // Stop Enter key from submitting if it's the "none" action
    document.getElementById('bundyForm').addEventListener('submit', function(e) {
        const action = e.submitter ? e.submitter.value : 'none';
        if (action === 'none') {
            e.preventDefault();
            alert('Please click a specific punch button (START, LUNCH, or END).');
            return;
        }

        // Disable all buttons to prevent double-click race conditions
        const buttons = this.querySelectorAll('.btn-punch');
        buttons.forEach(btn => {
            btn.disabled = true;
            if (btn === e.submitter) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> PROCESSING...';
            }
        });
    });
</script>

</body>
</html>
