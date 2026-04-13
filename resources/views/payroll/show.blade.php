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

        @if($payroll->status == 'draft')
        <div class="alert alert-warning text-center">
            <p class="mb-3">This payroll is in draft mode. Click the button below to process all active employees.</p>
            <button type="button" class="btn btn-success px-5 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#processPayrollModal">
                <i class="bi bi-play-circle me-1"></i> One-Click: Process Payroll Now
            </button>
        </div>

        <!-- Process Payroll Modal -->
        <div class="modal fade" id="processPayrollModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Authorize Payroll Processing</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('payroll.process', $payroll->id) }}" method="POST">
                        @csrf
                        <div class="modal-body text-start">
                            <p>You are about to compute and finalize the payroll for <strong>{{ $payroll->payroll_code }}</strong> ({{ $payroll->start_date }} to {{ $payroll->end_date }}).</p>
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle-fill"></i> This will generate payslips for all active employees and cannot be undone easily.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Enter Security Password</label>
                                <input type="password" name="admin_password" class="form-control" placeholder="Required for final approval" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success px-4">Begin Processing</button>
                        </div>
                    </form>
                </div>
            </div>
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
