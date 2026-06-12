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

                <div class="mb-3">
                    <button type="submit" id="btn_am_in" name="punch_type" value="am_in" class="btn btn-primary w-100 btn-punch shadow-sm">AM IN (START)</button>
                </div>

                <div class="mb-3">
                    <h6 class="text-warning fw-bold small text-uppercase">1st Break</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="submit" id="btn_break1_out" name="punch_type" value="break1_out" class="btn btn-outline-warning w-100 btn-punch btn-sm">1st BREAK OUT</button>
                        </div>
                        <div class="col-6">
                            <button type="submit" id="btn_break1_in" name="punch_type" value="break1_in" class="btn btn-outline-warning w-100 btn-punch btn-sm">1st BREAK IN</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-danger fw-bold small text-uppercase">Lunch Break</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="submit" id="btn_lunch_out" name="punch_type" value="lunch_out" class="btn btn-outline-danger w-100 btn-punch btn-sm">LUNCH OUT</button>
                        </div>
                        <div class="col-6">
                            <button type="submit" id="btn_lunch_in" name="punch_type" value="lunch_in" class="btn btn-outline-danger w-100 btn-punch btn-sm">LUNCH IN</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-success fw-bold small text-uppercase">2nd Break</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="submit" id="btn_break2_out" name="punch_type" value="break2_out" class="btn btn-outline-success w-100 btn-punch btn-sm">2nd BREAK OUT</button>
                        </div>
                        <div class="col-6">
                            <button type="submit" id="btn_break2_in" name="punch_type" value="break2_in" class="btn btn-outline-success w-100 btn-punch btn-sm">2nd BREAK IN</button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="submit" id="btn_pm_out" name="punch_type" value="pm_out" class="btn btn-dark w-100 btn-punch shadow-sm">PM OUT (END)</button>
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
    const btnOut = document.getElementById('btn_pm_out');
    const btnLunchOut = document.getElementById('btn_lunch_out');
    const btnLunchIn = document.getElementById('btn_lunch_in');
    const btnB1Out = document.getElementById('btn_break1_out');
    const btnB1In = document.getElementById('btn_break1_in');
    const btnB2Out = document.getElementById('btn_break2_out');
    const btnB2In = document.getElementById('btn_break2_in');

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
                        btnOut.disabled = !data.is_in || data.is_out;
                        
                        btnLunchOut.disabled = !data.is_in || data.is_lunch_out;
                        btnLunchIn.disabled = !data.is_lunch_out || data.is_lunch_in;
                        
                        btnB1Out.disabled = !data.is_in || data.is_break1_out;
                        btnB1In.disabled = !data.is_break1_out || data.is_break1_in;
                        
                        btnB2Out.disabled = !data.is_in || data.is_break2_out;
                        btnB2In.disabled = !data.is_break2_out || data.is_break2_in;

                        // Add visual feedback
                        if (data.is_in) btnIn.innerHTML = 'ALREADY IN';
                        else btnIn.innerHTML = 'AM IN (START)';

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
        [btnIn, btnOut, btnLunchOut, btnLunchIn, btnB1Out, btnB1In, btnB2Out, btnB2In].forEach(btn => btn.disabled = false);
        btnIn.innerHTML = 'AM IN (START)';
        btnOut.innerHTML = 'PM OUT (END)';
        btnLunchOut.innerHTML = 'LUNCH OUT';
        btnLunchIn.innerHTML = 'LUNCH IN';
        btnB1Out.innerHTML = '1st BREAK OUT';
        btnB1In.innerHTML = '1st BREAK IN';
        btnB2Out.innerHTML = '2nd BREAK OUT';
        btnB2In.innerHTML = '2nd BREAK IN';
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
