<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $systemSettings->app_name }} - Payslip - {{ $item->employee->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Courier New', Courier, monospace; }
        .payslip-container { 
            width: 80mm; 
            margin: 20px auto; 
            background: #fff; 
            padding: 10mm 5mm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border: 1px dashed #ccc;
        }
        .ticket-header { text-align: center; border-bottom: 1px dashed #333; padding-bottom: 10px; margin-bottom: 10px; }
        .ticket-logo { max-width: 50px; margin-bottom: 5px; }
        .ticket-title { font-size: 16px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .ticket-info { font-size: 11px; margin-bottom: 5px; }
        
        .section { margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #eee; }
        .section-title { font-size: 12px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; display: block; }
        
        .data-row { display: flex; justify-content: space-between; font-size: 11px; line-height: 1.4; }
        .data-label { flex: 1; }
        .data-value { font-weight: bold; text-align: right; }
        
        .total-row { border-top: 1px solid #333; margin-top: 5px; padding-top: 5px; font-weight: bold; }
        .net-pay-box { 
            text-align: center; 
            margin-top: 15px; 
            padding: 10px; 
            border: 2px solid #000;
            background: #fdfdfd;
        }
        .net-pay-amt { font-size: 18px; display: block; }
        
        .footer-note { font-size: 9px; text-align: center; margin-top: 15px; font-style: italic; color: #666; }

        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none; }
            .payslip-container { 
                width: 80mm; 
                margin: 0; 
                box-shadow: none; 
                border: none;
                padding: 5mm;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-print"><i class="bi bi-printer"></i> Print Ticket</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-print">Back</a>
    </div>

    <div class="payslip-container">
        <div class="ticket-header">
            @if($systemSettings->app_logo)
                <img src="{{ asset('storage/' . $systemSettings->app_logo) }}" alt="Logo" class="ticket-logo">
            @endif
            <h1 class="ticket-title">{{ $systemSettings->app_name }}</h1>
            <div class="ticket-info">OFFICIAL PAYSLIP TICKET</div>
            <div class="ticket-info" style="font-weight: bold;">{{ $item->payroll->payroll_code }}</div>
        </div>

        <div class="section">
            <div class="data-row">
                <span class="data-label">Period:</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($item->payroll->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($item->payroll->end_date)->format('M d, Y') }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Pay Date:</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($item->payroll->pay_date)->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="section">
            <span class="section-title">EMPLOYEE</span>
            <div class="data-row">
                <span class="data-label">Name:</span>
                <span class="data-value">{{ $item->employee->full_name }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">ID:</span>
                <span class="data-value">{{ $item->employee->employee_id }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Position:</span>
                <span class="data-value">{{ $item->employee->position }}</span>
            </div>
        </div>

        <div class="section">
            <span class="section-title">EARNINGS</span>
            <div class="data-row">
                <span class="data-label">Basic Pay:</span>
                <span class="data-value">{{ number_format($item->basic_pay, 2) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Overtime:</span>
                <span class="data-value">{{ number_format($item->overtime_pay, 2) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Night Diff:</span>
                <span class="data-value">{{ number_format($item->night_diff, 2) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Bonuses:</span>
                <span class="data-value">{{ number_format($item->bonuses, 2) }}</span>
            </div>
            <div class="data-row total-row">
                <span class="data-label">GROSS PAY:</span>
                <span class="data-value">{{ number_format($item->basic_pay + $item->overtime_pay + $item->bonuses + $item->night_diff, 2) }}</span>
            </div>
        </div>

        <div class="section">
            <span class="section-title">DEDUCTIONS</span>
            <div class="data-row">
                <span class="data-label">SSS:</span>
                <span class="data-value">{{ number_format($item->deductions_sss, 2) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Pag-Ibig:</span>
                <span class="data-value">{{ number_format($item->deductions_pagibig, 2) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">PhilHealth:</span>
                <span class="data-value">{{ number_format($item->deductions_philhealth, 2) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Other:</span>
                <span class="data-value">{{ number_format($item->other_deductions, 2) }}</span>
            </div>
            <div class="data-row total-row">
                <span class="data-label">TOTAL DED:</span>
                <span class="data-value">{{ number_format($item->deductions_sss + $item->deductions_pagibig + $item->deductions_philhealth + $item->other_deductions, 2) }}</span>
            </div>
        </div>

        <div class="net-pay-box">
            <span style="font-size: 10px; font-weight: bold;">NET TAKE HOME PAY</span>
            <span class="net-pay-amt">PHP {{ number_format($item->net_pay, 2) }}</span>
        </div>

        <div class="footer-note">
            *** This is a computer generated document ***<br>
            {{ date('Y-m-d H:i:s') }}
        </div>
    </div>
</body>
</body>
</html>
