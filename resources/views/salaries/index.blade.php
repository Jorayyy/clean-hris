@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack me-2 text-primary"></i>Payroll History & Salaries</h4>
    </div>
    <div class="col-auto">
        <form action="{{ route('salaries.index') }}" method="GET" class="d-flex align-items-center bg-white rounded shadow-sm border p-1" style="min-width: 400px;">
            <select name="payroll_id" class="form-select border-0 shadow-none fw-bold" onchange="this.form.submit()">
                <option value="">All Periods</option>
                @foreach($payrolls as $p)
                <option value="{{ $p->id }}" {{ request('payroll_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->payroll_code }} ({{ \Carbon\Carbon::parse($p->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($p->end_date)->format('M d, Y') }})
                </option>
                @endforeach
            </select>
            <div class="vr mx-2 my-1"></div>
            <input type="text" name="employee_id" class="form-control border-0 shadow-none" placeholder="Employee ID..." value="{{ request('employee_id') }}" style="width: 150px;">
            <button class="btn btn-primary btn-sm px-3 ms-1 fw-bold">Search</button>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Coverage Period</th>
                        <th class="text-center">Earnings</th>
                        <th class="text-center">Deductions</th>
                        <th class="text-center">Net Pay</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $item->employee->full_name }}</div>
                            <small class="text-muted">{{ $item->employee->employee_id }}</small>
                        </td>
                        <td>
                            <i class="bi bi-calendar3 small text-muted me-1"></i>
                            <span class="small">{{ \Carbon\Carbon::parse($item->payroll->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($item->payroll->end_date)->format('M d, Y') }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-success fw-normal border">
                                <i class="bi bi-plus-circle me-1"></i>P{{ number_format($item->basic_pay + $item->overtime_pay + $item->bonuses + $item->night_diff, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-danger fw-normal border">
                                <i class="bi bi-dash-circle me-1"></i>P{{ number_format($item->deductions_sss + $item->deductions_pagibig + $item->deductions_philhealth + $item->other_deductions, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fw-bold text-primary">P{{ number_format($item->net_pay, 2) }}</div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('payroll.payslip', $item->id) }}" class="btn btn-sm btn-outline-info" title="View Payslip">
                                    <i class="bi bi-file-earmark-pdf"></i> Payslip
                                </a>
                                <a href="{{ route('salaries.edit', $item->id) }}" class="btn btn-sm btn-outline-warning ms-1" title="Edit Salary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('salaries.destroy', $item->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to remove this salary record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete record">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No salary records found. Process a payroll batch to generate records.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $salaries->links() }}
</div>
@endsection
