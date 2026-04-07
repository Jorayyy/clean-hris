@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow rounded border-0 bg-dark text-white overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="position-relative z-1">
                    <h3 class="fw-bold mb-1">Worker Dashboard</h3>
                    <p class="mb-0 text-white-50">Logged in as <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->employee_id }})</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="col-md-4 mb-4">
        <div class="card shadow rounded border-0 border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-1">Hours this Month</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">{{ number_format($totalHoursThisMonth, 1) }}</h3>
                    <i class="bi bi-clock text-primary fs-3 ms-auto opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow rounded border-0 border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-1">Active Tickets</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">{{ $pendingTickets }}</h3>
                    <i class="bi bi-ticket-perforated text-warning fs-3 ms-auto opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow rounded border-0 border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-1">Latest Net Pay</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">
                        {{ $latestSalary ? 'P'.number_format($latestSalary->net_pay, 2) : 'N/A' }}
                    </h3>
                    <i class="bi bi-cash-coin text-success fs-3 ms-auto opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow border-0 rounded overflow-hidden">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">My Payslips History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                <th>Basic Pay</th>
                                <th>Overtime</th>
                                <th>Total Deductions</th>
                                <th>Net Pay</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salaries as $salary)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $salary->payroll->title }}</div>
                                        <small class="text-muted">{{ $salary->payroll->start_date }} to {{ $salary->payroll->end_date }}</small>
                                    </td>
                                    <td>P{{ number_format($salary->basic_pay, 2) }}</td>
                                    <td>P{{ number_format($salary->overtime_pay, 2) }}</td>
                                    <td class="text-danger">P{{ number_format($salary->deductions_sss + $salary->deductions_pagibig + $salary->deductions_philhealth + $salary->other_deductions, 2) }}</td>
                                    <td class="fw-bold text-success">P{{ number_format($salary->net_pay, 2) }}</td>
                                    <td>
                                        <a href="{{ route('employee.payslip', $salary->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">View Payslip</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No payslip records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
