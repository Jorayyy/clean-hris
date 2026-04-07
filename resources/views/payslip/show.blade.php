<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $systemSettings->app_name }} - Payslip - {{ $item->employee->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payslip-border { border: 2px solid #333; padding: 20px; max-width: 800px; margin: 30px auto; background: #fff; }
        .section-header { border-bottom: 2px solid #eee; margin-bottom: 20px; padding-bottom: 10px; }
        .data-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .category-title { font-weight: bold; background: #f8f9fa; padding: 5px 10px; margin-top: 15px; display: block; }
        .logo-box { max-height: 80px; object-fit: contain; }
        @media print { .btn-print { display: none; } body { background: white; } .payslip-border { border: none; } }
    </style>
</head>
<body class="bg-light">
    <div class="container text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-print">Print Payslip</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-print">Back</a>
    </div>

    <div class="payslip-border shadow-sm mt-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-3">
                @if($systemSettings->app_logo)
                    <img src="{{ asset('storage/' . $systemSettings->app_logo) }}" alt="Logo" class="logo-box">
                @endif
            </div>
            <div class="col-md-9 text-end">
                <h4 class="mb-0 fw-bold">{{ $systemSettings->app_name }}</h4>
                <p class="text-muted small mb-0">Official Employee Payslip Record</p>
            </div>
        </div>

        <div class="row section-header text-center">
            <div class="col-12">
                <h4 class="mb-0 text-uppercase">EMPLOYEE PAYSLIP</h4>
                <p class="text-muted small">Period: {{ $item->payroll->start_date }} to {{ $item->payroll->end_date }}</p>
                <div class="small">Batch Code: {{ $item->payroll->payroll_code }} | Pay Date: {{ $item->payroll->pay_date }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 border-end">
                <span class="category-title text-primary">Employee Details</span>
                <div class="data-row"><span>Name:</span> <strong>{{ $item->employee->full_name }}</strong></div>
                <div class="data-row"><span>Employee ID:</span> <strong>{{ $item->employee->employee_id }}</strong></div>
                <div class="data-row"><span>Position:</span> <strong>{{ $item->employee->position }}</strong></div>
                <div class="data-row"><span>Daily Rate:</span> <strong>P{{ number_format($item->employee->daily_rate, 2) }}</strong></div>
            </div>
            <div class="col-md-6">
                <span class="category-title text-success">Work History</span>
                <div class="data-row"><span>Total Days Present:</span> <strong>{{ $item->total_days }}</strong></div>
                <div class="data-row"><span>Total Hours Rendered:</span> <strong>{{ number_format($item->total_hours, 2) }}</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 border-end">
                <span class="category-title text-success">EARNINGS (+)</span>
                <div class="data-row"><span>Basic Salary:</span> <strong>P{{ number_format($item->basic_pay, 2) }}</strong></div>
                <div class="data-row"><span>Overtime Pay:</span> <strong>P{{ number_format($item->overtime_pay, 2) }}</strong></div>
                <div class="data-row"><span>Bonus / Incentives:</span> <strong>P{{ number_format($item->bonuses, 2) }}</strong></div>
                <div class="data-row"><span>Night Differential:</span> <strong>P{{ number_format($item->night_diff, 2) }}</strong></div>
                <hr>
                <div class="data-row text-success"><span>GROSS PAY:</span> <strong>P{{ number_format($item->basic_pay + $item->overtime_pay + $item->bonuses + $item->night_diff, 2) }}</strong></div>
            </div>
            <div class="col-md-6">
                <span class="category-title text-danger">DEDUCTIONS (-)</span>
                <div class="data-row"><span>SSS:</span> <strong>P{{ number_format($item->deductions_sss, 2) }}</strong></div>
                <div class="data-row"><span>Pag-Ibig:</span> <strong>P{{ number_format($item->deductions_pagibig, 2) }}</strong></div>
                <div class="data-row"><span>PhilHealth:</span> <strong>P{{ number_format($item->deductions_philhealth, 2) }}</strong></div>
                <div class="data-row"><span>Other Deductions:</span> <strong>P{{ number_format($item->other_deductions, 2) }}</strong></div>
                <hr>
                <div class="data-row text-danger"><span>TOTAL DEDUCTIONS:</span> <strong>P{{ number_format($item->deductions_sss + $item->deductions_pagibig + $item->deductions_philhealth + $item->other_deductions, 2) }}</strong></div>
            </div>
        </div>

        <div class="row mt-4 pt-3 border-top bg-light">
            <div class="col-12 text-center py-2">
                <h3 class="text-primary mb-0">NET PAY: P{{ number_format($item->net_pay, 2) }}</h3>
                <p class="small text-muted italic">I hereby certify that the above figures are correct.</p>
            </div>
        </div>
    </div>
</body>
</html>
