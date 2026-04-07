@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-4 border-0 rounded-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payroll Batch: {{ $payroll->payroll_code }}</h5>
        <div class="d-flex align-items-center">
            <span class="badge {{ $payroll->status == 'processed' ? 'bg-success' : 'bg-warning' }} me-3">
                {{ strtoupper($payroll->status) }}
            </span>
            <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-light">Back to Home</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row text-center border-bottom pb-4 mb-4">
            <div class="col-md-3"><strong>Start Date:</strong> {{ $payroll->start_date }}</div>
            <div class="col-md-3"><strong>End Date:</strong> {{ $payroll->end_date }}</div>
            <div class="col-md-3"><strong>Pay Date:</strong> {{ $payroll->pay_date }}</div>
            <div class="col-md-3"><strong>Items:</strong> {{ $items->count() }}</div>
        </div>

        @if($payroll->status == 'draft')
        <div class="alert alert-warning text-center">
            This payroll is in draft mode. Click the button below to process all active employees.
            <form action="{{ route('payroll.process', $payroll->id) }}" method="POST" class="mt-2">
                @csrf
                <button class="btn btn-success px-4">One-Click: Process Payroll Now</button>
            </form>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm align-middle">
                <thead class="bg-light text-center small">
                    <tr>
                        <th rowspan="2">Employee</th>
                        <th colspan="2">Work Info</th>
                        <th colspan="3">Earnings</th>
                        <th colspan="3">Deductions</th>
                        <th rowspan="2">Net Pay</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                    <tr>
                        <th>Days</th>
                        <th>Hours</th>
                        <th>Basic</th>
                        <th>OT</th>
                        <th>Bonus</th>
                        <th>SSS</th>
                        <th>PagIbig</th>
                        <th>PH</th>
                    </tr>
                </thead>
                <tbody class="text-center small">
                    @forelse($items as $item)
                    <tr>
                        <td class="text-start"><strong>{{ $item->employee->full_name }}</strong></td>
                        <td>{{ $item->total_days }}</td>
                        <td>{{ $item->total_hours }}</td>
                        <td>{{ number_format($item->basic_pay, 2) }}</td>
                        <td>{{ number_format($item->overtime_pay, 2) }}</td>
                        <td>{{ number_format($item->bonuses, 2) }}</td>
                        <td class="text-danger">-{{ number_format($item->deductions_sss, 2) }}</td>
                        <td class="text-danger">-{{ number_format($item->deductions_pagibig, 2) }}</td>
                        <td class="text-danger">-{{ number_format($item->deductions_philhealth, 2) }}</td>
                        <td class="bg-light text-primary"><strong>{{ number_format($item->net_pay, 2) }}</strong></td>
                        <td>
                            <a href="{{ route('payroll.payslip', $item->id) }}" class="btn btn-sm btn-outline-info">Slip</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">No payroll items found. Try processing the batch.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
