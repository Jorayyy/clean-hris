@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Header with Announcements -->
    <div class="col-md-9 mb-4">
        <div class="card shadow-sm rounded-4 border-0 bg-dark text-white overflow-hidden" style="min-height: 180px;">
            <div class="card-body p-4 position-relative d-flex flex-column justify-content-center">
                <div class="position-relative z-1">
                    <h2 class="fw-800 mb-1 tracking-tight">Worker Dashboard</h2>
                    <p class="mb-0 text-white-50 opacity-75">Welcome back, <strong>{{ Auth::user()->name }}</strong>. Track your progress below.</p>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('employee.profile') }}" class="btn btn-light btn-sm fw-bold px-3 rounded-pill">
                        <i class="bi bi-person-circle me-1"></i> Edit Profile
                    </a>
                    <button class="btn btn-primary btn-sm fw-bold px-3 rounded-pill border-0 shadow-sm" style="background: rgba(59, 130, 246, 0.5); backdrop-filter: blur(5px);">
                        <i class="bi bi-megaphone-fill me-1"></i> {{ count($announcements) }} News
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements Slider/List -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100 bg-light">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-bold small text-muted mb-0 tracking-wider text-uppercase">Announcements</h6>
            </div>
            <div class="card-body p-3">
                @forelse($announcements as $news)
                    <div class="p-2 mb-2 rounded-3 bg-white border-start border-{{ $news->type }} border-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-0 fw-bold small text-{{ $news->type }}">{{ $news->title }}</h6>
                            <span class="badge bg-light text-muted fw-normal" style="font-size: 0.6rem;">{{ $news->created_at->format('Y-m-d') }}</span>
                        </div>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem; line-height: 1.2;">{{ $news->content }}</p>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="bi bi-megaphone text-muted opacity-25 fs-1 d-block mb-2"></i>
                        <p class="text-muted small mb-0">No active announcements</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Stats and Shift Status -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm rounded-4 border-0 border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <h6 class="text-muted fw-bold small mb-2 text-uppercase tracking-wider">Month Hours</h6>
                        <div class="d-flex align-items-baseline">
                            <h3 class="fw-800 mb-0 me-2 text-primary">{{ number_format($totalHoursThisMonth, 1) }}</h3>
                            <span class="text-muted small">Hrs</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ min(($totalHoursThisMonth/160)*100, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm rounded-4 border-0 border-start border-warning border-4 h-100">
                    <div class="card-body">
                        <h6 class="text-muted fw-bold small mb-2 text-uppercase tracking-wider">Support Tasks</h6>
                        <div class="d-flex align-items-center">
                            <h3 class="fw-800 mb-0 me-2 text-warning">{{ $pendingTickets }}</h3>
                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill small">Active</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm rounded-4 border-0 border-start border-success border-4 h-100">
                    <div class="card-body">
                        <h6 class="text-muted fw-bold small mb-2 text-uppercase tracking-wider">Latest Net Pay</h6>
                        <div class="d-flex align-items-baseline">
                            <h3 class="fw-800 mb-0 text-success">
                                {{ $latestSalary ? '₱'.number_format($latestSalary->net_pay, 2) : 'N/A' }}
                            </h3>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-graph-up text-success me-1"></i> Stable trend
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Summary -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                        <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                            <i class="bi bi-calendar-check-fill fs-5"></i>
                        </div>
                        <h6 class="mb-0 fw-800">Recent Attendance Recap</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-muted text-uppercase tracking-wider font-monospace">
                                        <th class="ps-4">Date</th>
                                        <th>Log In</th>
                                        <th>Log Out</th>
                                        <th>Total Hrs</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendance as $att)
                                        <tr>
                                            <td class="ps-4 text-dark fw-bold small">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y (D)') }}</td>
                                            <td class="font-monospace small text-primary fw-bold">{{ $att->time_in ?? '--:--' }}</td>
                                            <td class="font-monospace small text-secondary fw-bold">{{ $att->time_out ?? '--:--' }}</td>
                                            <td class="fw-bold">{{ number_format($att->total_hours, 1) }}</td>
                                            <td>
                                                @if($att->time_in)
                                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1">Recorded</span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">Missing</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No attendance logs in the last 5 shifts.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Shift Status & Balances -->
    <div class="col-md-4">
        <!-- Today's Shift Status -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 bg-primary text-white p-2">
            <div class="card-body p-3">
                <h6 class="fw-bold small text-white-50 mb-3 tracking-wider">CURRENT SHIFT STATUS</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px;">
                        <i class="bi bi-stopwatch-fill fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="fw-800 mb-0 font-monospace">
                            {{ $todayAttendance && $todayAttendance->time_in ? $todayAttendance->time_in : '--:--' }}
                        </h4>
                        <span class="small opacity-75">Clock In Time Today</span>
                    </div>
                </div>
                <div class="alert bg-white bg-opacity-10 border-0 text-white mb-0 p-3 rounded-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small fw-bold">Daily Progress</span>
                        @php
                            $progHours = ($todayAttendance && $todayAttendance->time_in && $todayAttendance->time_out) 
                                ? (float)$todayAttendance->total_hours 
                                : 0;
                        @endphp
                        <span class="small fw-bold">{{ number_format($progHours, 1) }} / 8.0h</span>
                    </div>
                    <div class="progress bg-dark bg-opacity-25" style="height: 8px;">
                        <div class="progress-bar bg-white border-0" role="progressbar" style="width: {{ min(($progHours/8)*100, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits/Balances Summary (Visual) -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold small text-muted mb-4 tracking-wider text-uppercase font-monospace text-center">Benefit Balances</h6>
                <div class="d-flex justify-content-between text-center gap-2">
                    <div class="flex-grow-1">
                        <div class="position-relative mb-2 mx-auto d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <svg viewBox="0 0 36 36" class="circular-chart text-primary" style="width: 60px; height: 60px;">
                                <path class="circle-bg" stroke="#f1f5f9" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle" stroke-dasharray="{{ ($leaveBalance->sick_leave_used / max($leaveBalance->sick_leave_total, 1)) * 100 }}, 100" stroke-linecap="round" stroke="#3b82f6" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="position-absolute fw-800 small text-dark">{{ (int)$leaveBalance->sick_leave_used }}/{{ (int)$leaveBalance->sick_leave_total }}</span>
                        </div>
                        <span class="small text-muted fw-bold">Sick Leave</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="position-relative mb-2 mx-auto d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <svg viewBox="0 0 36 36" class="circular-chart text-info" style="width: 60px; height: 60px;">
                                <path class="circle-bg" stroke="#f1f5f9" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle" stroke-dasharray="{{ ($leaveBalance->vacation_leave_used / max($leaveBalance->vacation_leave_total, 1)) * 100 }}, 100" stroke-linecap="round" stroke="#0ea5e9" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="position-absolute fw-800 small text-dark">{{ (int)$leaveBalance->vacation_leave_used }}/{{ (int)$leaveBalance->vacation_leave_total }}</span>
                        </div>
                        <span class="small text-muted fw-bold">Vacation</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="position-relative mb-2 mx-auto d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <svg viewBox="0 0 36 36" class="circular-chart text-warning" style="width: 60px; height: 60px;">
                                <path class="circle-bg" stroke="#f1f5f9" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle" stroke-dasharray="{{ ($leaveBalance->sil_used / max($leaveBalance->sil_total, 1)) * 100 }}, 100" stroke-linecap="round" stroke="#f59e0b" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="position-absolute fw-800 small text-dark">{{ (int)$leaveBalance->sil_used }}/{{ (int)$leaveBalance->sil_total }}</span>
                        </div>
                        <span class="small text-muted fw-bold">SIL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll History Table -->
    <div class="col-md-12">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-success-subtle text-success rounded-circle p-2 me-3">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                    <h6 class="mb-0 fw-800">My Payslip History</h6>
                </div>
                <form action="{{ route('employee.dashboard') }}" method="GET" class="d-flex align-items-center">
                    <select name="payroll_id" class="form-select form-select-sm border-0 shadow-sm bg-light fw-bold rounded-pill ps-3 pe-5" onchange="this.form.submit()" style="width: auto; min-width: 250px;">
                        <option value="">ALL PAY PERIODS</option>
                        @foreach($payrollPeriods as $period)
                            <option value="{{ $period->id }}" {{ request('payroll_id') == $period->id ? 'selected' : '' }}>
                                {{ strtoupper($period->title) }} ({{ $period->start_date }} - {{ $period->end_date }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light font-monospace small text-muted text-uppercase tracking-wider">
                            <tr>
                                <th class="ps-4">Period</th>
                                <th>Basic Pay</th>
                                <th>Overtime</th>
                                <th>Total Deductions</th>
                                <th>Net Pay</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salaries as $salary)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $salary->payroll->title }}</div>
                                        <small class="text-muted">{{ $salary->payroll->start_date }} to {{ $salary->payroll->end_date }}</small>
                                    </td>
                                    <td class="fw-medium font-monospace">₱{{ number_format($salary->basic_pay, 2) }}</td>
                                    <td class="fw-medium font-monospace text-primary">+₱{{ number_format($salary->overtime_pay, 2) }}</td>
                                    <td class="fw-medium font-monospace text-danger">-₱{{ number_format($salary->deductions_sss + $salary->deductions_pagibig + $salary->deductions_philhealth + $salary->other_deductions, 2) }}</td>
                                    <td class="fw-800 text-success font-monospace">₱{{ number_format($salary->net_pay, 2) }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('employee.payslip', $salary->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder2-open fs-1 opacity-25 d-block mb-3"></i>
                                        No payslip records found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-4 { border-radius: 1rem !important; }
    .bg-primary-subtle { background-color: #e0f2fe !important; }
    .bg-warning-subtle { background-color: #fef3c7 !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; }
    .font-monospace { font-family: 'JetBrains Mono', 'Courier New', monospace !important; }
    
    .circular-chart {
        display: block;
        margin: 0 auto;
        max-width: 100%;
        max-height: 250px;
    }
    .circle-bg {
        stroke: #eee;
    }
    .circle {
        fill: none;
        stroke-linecap: round;
        animation: progress 1s ease-out forwards;
    }
    @keyframes progress {
        0% {
            stroke-dasharray: 0 100;
        }
    }
</style>
@endsection
