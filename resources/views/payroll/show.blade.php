@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-4 border-0 rounded-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payroll Batch: {{ $payroll->payroll_code }}</h5>
        <div class="d-flex align-items-center">
            <span class="badge {{ $payroll->status == 'approved' ? 'bg-success' : ($payroll->status == 'processed' ? 'bg-info' : 'bg-warning') }} me-3">
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

        @if($payroll->status == 'draft' || $payroll->status == 'processing')
        <div class="alert {{ $payroll->status == 'processing' ? 'alert-info' : 'alert-warning' }} text-center shadow-sm">
            @if($payroll->status == 'processing')
                <h6 class="fw-bold"><i class="bi bi-clock-history me-2"></i>Status: PROCESSING</h6>
                <p class="mb-3">Manual payslip entry is in progress. Please ensure all employees in this group have their payslips created before finalizing.</p>
            @else
                <h6 class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Status: DRAFT</h6>
                <p class="mb-3">This payroll is in draft mode. Start manually creating payslips for active employees within this group.</p>
            @endif
            
            <div class="d-flex justify-content-center gap-2">
                @php
                    $total_employees = \App\Models\Employee::where('payroll_group_id', $payroll->payroll_group_id)->where('status', 'active')->count();
                    $is_complete = $item_count >= $total_employees;
                @endphp

                <a href="{{ route('payroll-items.create', ['payroll_id' => $payroll->id]) }}" class="btn btn-success px-5 shadow-sm fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Add Individual Payslip
                </a>

                <form action="{{ route('payroll.approve', $payroll->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold" 
                        {{ !$is_complete ? 'disabled' : '' }}
                        onclick="return confirm('Note: Approving will finalize this batch and lock it for changes. Proceed?')">
                        <i class="bi bi-patch-check-fill me-1"></i> Finalize & Approve Whole Batch
                    </button>
                </form>
            </div>
            
            @if(!$is_complete)
                <div class="mt-2 text-danger small fw-bold">
                    <i class="bi bi-exclamation-circle me-1"></i> 
                    Finalize button is disabled: {{ $item_count }} of {{ $total_employees }} employees processed.
                </div>
            @endif
        </div>
        @else
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-8">
                <div class="alert {{ $payroll->status == 'approved' ? 'alert-success' : 'alert-info' }} mb-0 d-flex align-items-center">
                    <i class="bi {{ $payroll->status == 'approved' ? 'bi-check-circle-fill' : 'bi-info-circle-fill' }} h4 me-3 mb-0"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Status: {{ strtoupper($payroll->status) }}</h6>
                        @if($payroll->status == 'approved')
                            <p class="small mb-0">Approved by <strong>{{ $payroll->approver->name ?? 'System' }}</strong> on {{ $payroll->approved_at }}</p>
                        @else
                            <p class="small mb-0">Payroll items generated. Awaiting final administrative review and approval.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                @if($payroll->status == 'processed')
                    <form action="{{ route('payroll.approve', $payroll->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" onclick="return confirm('Note: Approving will finalize this batch and lock it for changes. Proceed?')">
                            <i class="bi bi-patch-check me-2"></i>Finalize & Approve Batch
                        </button>
                    </form>
                @endif
                <button class="btn btn-outline-secondary px-3 ms-2 no-print" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print Report
                </button>
            </div>
        </div>

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
                            <div class="btn-group">
                                <a href="{{ route('payroll.payslip', $item->id) }}" class="btn btn-sm btn-outline-info">Slip</a>
                                <a href="{{ route('payroll-items.edit', $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('payroll-items.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this payslip?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
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
