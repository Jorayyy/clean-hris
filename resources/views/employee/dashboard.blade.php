@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="mb-0 opacity-75">View and manage your payslips below.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-primary">Employee Portal</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">My Payslips</h5>
            </div>
            <div class="card-body">
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
