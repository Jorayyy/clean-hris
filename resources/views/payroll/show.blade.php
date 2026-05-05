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
            <div class="col-md-3">
                <strong>Target:</strong> 
                @if($payroll->payrollGroup)
                    {{ $payroll->payrollGroup->name }}
                @elseif($payroll->employee)
                    {{ $payroll->employee->full_name }}
                @else
                    N/A
                @endif
            </div>
        </div>

        @if($payroll->status == 'draft' || $payroll->status == 'processing' || $payroll->status == 'processed')
        @php
            if ($payroll->payrollGroup) {
                // Get employees in the group that have finalized DTRs for this period
                $target_employees = \App\Models\Employee::where('payroll_group_id', $payroll->payroll_group_id)
                    ->where('status', 'active')
                    ->count();
            } else {
                $target_employees = 1;
            }
            // A batch is only "ready" if there's actually someone to pay AND we've created items for them
            $is_complete = ($target_employees > 0) && ($item_count >= $target_employees);
        @endphp

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 bg-light rounded-4 shadow-sm overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-4 bg-primary bg-opacity-10 d-flex flex-column align-items-center justify-content-center p-4">
                                <div class="mb-2">
                                    <span class="display-4 fw-bold text-primary">{{ $item_count }}</span>
                                    <span class="fs-4 text-muted">/ {{ $target_employees }}</span>
                                </div>
                                <div class="text-uppercase small fw-bold text-primary opacity-75">Payslips Created</div>
                                
                                @if($is_complete)
                                    <div class="mt-3 badge bg-success rounded-pill px-3 py-2 shadow-sm">
                                        <i class="bi bi-check-circle-fill me-1"></i> BATCH COMPLETE
                                    </div>
                                @else
                                    <div class="mt-3 badge bg-warning text-dark rounded-pill px-3 py-2 shadow-sm">
                                        <i class="bi bi-hourglass-split me-1"></i> REMAINING: {{ $target_employees - $item_count }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8 p-4">
                                @if($is_complete)
                                    <h5 class="fw-bold mb-2"><i class="bi bi-patch-check-fill text-success me-2"></i>Batch Ready for Finalization</h5>
                                    <p class="text-muted">All expected employees have their payslips prepared. You can now finalize this batch to lock the data and approve it for disbursement.</p>
                                    <div class="d-flex gap-2 mt-4">
                                        <form action="{{ route('payroll.approve', $payroll->id) }}" method="POST" class="w-100">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-bold">
                                                <i class="bi bi-check-all me-2"></i> Finalize and Approve Batch
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <h5 class="fw-bold mb-2"><i class="bi bi-pencil-square text-info me-2"></i>Manual Entry in Progress</h5>
                                    <p class="text-muted">Please continue adding payslips for the remaining employees in this group. The finalization option will become available once all payslips are added.</p>
                                    <div class="d-flex gap-2 mt-4">
                                        <a href="{{ route('payroll-items.create', ['payroll_id' => $payroll->id]) }}" class="btn btn-dark btn-lg w-100 rounded-pill shadow-sm fw-bold">
                                            <i class="bi bi-plus-circle me-2"></i> Add Individual Payslip
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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
                            <div class="btn-group">
                                <a href="{{ route('payroll.payslip', $item->id) }}" class="btn btn-sm btn-outline-info">Slip</a>
                                <a href="{{ route('payroll-items.edit', $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
