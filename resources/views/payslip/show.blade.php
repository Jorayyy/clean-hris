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
        
        @media print { 
            .btn-print, .no-print { display: none !important; } 
            body { background: white !important; margin: 0 !important; padding: 0 !important; } 
            
            /* High-Performance Ticket Printing (80mm) */
            @page {
                size: 80mm auto;
                margin: 0;
            }
            
            .payslip-border { 
                border: none !important; 
                width: 76mm !important; /* Slightly narrower to account for printer margins */
                margin: 0 auto !important;
                padding: 2mm !important;
                box-shadow: none !important;
                font-size: 10pt !important;
            }
            
            .row { display: block !important; width: 100% !important; margin: 0 !important; }
            .col-md-3, .col-md-9, .col-md-6, .col-12 { 
                width: 100% !important; 
                float: none !important; 
                text-align: center !important; 
                border: none !important;
                padding: 0 !important;
            }
            
            .text-end { text-align: center !important; }
            .section-header { border-bottom: 1px dashed #333 !important; }
            .category-title { background: #eee !important; -webkit-print-color-adjust: exact; margin-top: 5px !important; font-size: 9pt !important; }
            .data-row { font-size: 9pt !important; border-bottom: 1px dotted #eee; margin-bottom: 2px !important; }
            .border-end { border: none !important; }
            
            /* Compact the totals for the ticket */
            .net-pay-box, .bg-light { 
                background: #f8f9fa !important; 
                -webkit-print-color-adjust: exact;
                border: 1px solid #000 !important;
                margin-top: 10px !important;
            }
            h3 { font-size: 14pt !important; margin: 5px 0 !important; }
            h4 { font-size: 12pt !important; }
            p.small { font-size: 8pt !important; }
            hr { margin: 5px 0 !important; border-top: 1px dashed #333 !important; }
        }
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
                <p class="text-muted small">Period: {{ \Carbon\Carbon::parse($item->payroll->start_date)->format('M d') }} to {{ \Carbon\Carbon::parse($item->payroll->end_date)->format('M d, Y') }}</p>
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
            <div class="col-md-6 text-center">
                <span class="category-title text-danger">DEDUCTIONS (-)</span>
                <div id="deductions-container">
                    @php 
                        $total_deductions = 0;
                        $types = \App\Models\DeductionType::pluck('name', 'code')->toArray();
                    @endphp
                    
                    @if($item->deductions_json && count($item->deductions_json) > 0)
                        @foreach($item->deductions_json as $d)
                            <div class="data-row">
                                <span>{{ $types[$d['type']] ?? $d['type'] }}:</span> 
                                <strong>P{{ number_format($d['amount'], 2) }}</strong>
                            </div>
                            @php $total_deductions += $d['amount']; @endphp
                        @endforeach
                    @else
                        <!-- Fallback for legacy records or missing JSON -->
                        @if($item->deductions_sss > 0) <div class="data-row"><span>SSS:</span> <strong>P{{ number_format($item->deductions_sss, 2) }}</strong></div> @endif
                        @if($item->deductions_pagibig > 0) <div class="data-row"><span>Pag-Ibig:</span> <strong>P{{ number_format($item->deductions_pagibig, 2) }}</strong></div> @endif
                        @if($item->deductions_philhealth > 0) <div class="data-row"><span>PhilHealth:</span> <strong>P{{ number_format($item->deductions_philhealth, 2) }}</strong></div> @endif
                        @if($item->other_deductions > 0) 
                            <div class="data-row">
                                <span>{{ $otherLabel }} Deductions:</span> 
                                <strong>P{{ number_format($item->other_deductions, 2) }}</strong>
                            </div> 
                        @endif
                        @php $total_deductions = $item->deductions_sss + $item->deductions_pagibig + $item->deductions_philhealth + $item->other_deductions; @endphp
                    @endif
                </div>
                <hr>
                <div class="data-row text-danger"><span>TOTAL DEDUCTIONS:</span> <strong>P{{ number_format($total_deductions, 2) }}</strong></div>
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
</body>
</body>
</html>
