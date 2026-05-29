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
            <div class="col-md-3"><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($payroll->start_date)->format('M d, Y') }}</div>
            <div class="col-md-3"><strong>End Date:</strong> {{ \Carbon\Carbon::parse($payroll->end_date)->format('M d, Y') }}</div>
            <div class="col-md-3"><strong>Pay Date:</strong> {{ \Carbon\Carbon::parse($payroll->pay_date)->format('M d, Y') }}</div>
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
                // Total active employees in this group
                $total_group_employees = \App\Models\Employee::where('payroll_group_id', $payroll->payroll_group_id)
                    ->where('status', 'active')
                    ->count();
            } else {
                $total_group_employees = 1;
            }
            
            // We consider it "Ready to Finalize" if we have items AND no more finalized DTRs waiting to be processed
            $can_approve = ($item_count > 0) && ($finalized_dtr_count == 0);
        @endphp

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 bg-light rounded-4 shadow-sm overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-4 bg-primary bg-opacity-10 d-flex flex-column align-items-center justify-content-center p-4">
                                <div class="mb-2">
                                    <span class="display-4 fw-bold text-primary">{{ $item_count }}</span>
                                    <span class="fs-4 text-muted">/ {{ $total_group_employees }}</span>
                                </div>
                                <div class="text-uppercase small fw-bold text-primary opacity-75">Payslips Created</div>
                                
                                @if($can_approve && $item_count == $total_group_employees)
                                    <div class="mt-3 badge bg-success rounded-pill px-3 py-2 shadow-sm">
                                        <i class="bi bi-check-circle-fill me-1"></i> GROUP COMPLETE
                                    </div>
                                @elseif($can_approve)
                                    <div class="mt-3 badge bg-info rounded-pill px-3 py-2 shadow-sm text-dark">
                                        <i class="bi bi-info-circle-fill me-1"></i> PARTIAL BATCH
                                    </div>
                                @else
                                    <div class="mt-3 badge bg-warning text-dark rounded-pill px-3 py-2 shadow-sm">
                                        <i class="bi bi-hourglass-split me-1"></i> PENDING DTRs: {{ $finalized_dtr_count }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8 p-4">
                                @if($finalized_dtr_count > 0)
                                    <h5 class="fw-bold mb-2"><i class="bi bi-gear-fill text-info me-2"></i>Batch Ready for Processing</h5>
                                    <p class="text-muted">
                                        The system has detected <strong>{{ $finalized_dtr_count }}</strong> additional finalized DTR(s) for this period. 
                                        Process them to include them in this payroll.
                                    </p>
                                    <div class="d-flex gap-2 mt-4">
                                        <form action="{{ route('payroll.process-batch', $payroll->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-bold">
                                                <i class="bi bi-play-circle-fill me-2"></i> Process {{ $finalized_dtr_count }} Payslips
                                            </button>
                                        </form>
                                        <a href="{{ route('payroll-items.create', ['payroll_id' => $payroll->id]) }}" class="btn btn-outline-dark btn-lg px-4 rounded-pill shadow-sm fw-bold">
                                            <i class="bi bi-plus-circle me-1"></i> Add Manual
                                        </a>
                                    </div>
                                @elseif($item_count > 0)
                                    <h5 class="fw-bold mb-2"><i class="bi bi-patch-check-fill text-success me-2"></i>Batch Ready for Finalization</h5>
                                    <p class="text-muted">
                                        @if($item_count < $total_group_employees)
                                            <strong>Note:</strong> Some employees (like Jane Smith) were skipped because they have no finalized DTRs. 
                                            You can still finalize the batch for those already processed.
                                        @else
                                            All expected employees have their payslips prepared.
                                        @endif
                                    </p>
                                    <div class="d-flex gap-2 mt-4">
                                        <form action="{{ route('payroll.approve', $payroll->id) }}" method="POST" class="w-100">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm fw-bold">
                                                <i class="bi bi-check-all me-2"></i> Finalize and Approve ({{ $item_count }})
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <h5 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-warning me-2"></i>No Items to Process</h5>
                                    <p class="text-muted">There are no finalized DTRs for this period yet. Please verify and finalize the employee DTRs first before processing payroll.</p>
                                    <div class="d-flex gap-2 mt-4">
                                        <a href="{{ route('admin.dtrs.index') }}" class="btn btn-dark btn-lg w-100 rounded-pill shadow-sm fw-bold">
                                            <i class="bi bi-arrow-right-circle me-2"></i> Go to DTR Logs
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
                        <th rowspan="2">Add-ons</th>
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
                        <td>
                            @php
                                $total_addons = 0;
                                if($item->allowances_json) {
                                    foreach($item->allowances_json as $a) $total_addons += $a['amount'];
                                }
                            @endphp
                            <span class="text-success">+{{ number_format($total_addons, 2) }}</span>
                        </td>
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
