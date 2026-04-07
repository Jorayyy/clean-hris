@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payroll History & Salaries</h5>
        <form action="{{ route('salaries.index') }}" method="GET" class="d-flex align-items-center">
            <select name="payroll_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                <option value="">All Periods</option>
                @foreach($payrolls as $p)
                <option value="{{ $p->id }}" {{ request('payroll_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->payroll_code }} ({{ $p->start_date }} - {{ $p->end_date }})
                </option>
                @endforeach
            </select>
            <input type="text" name="employee_id" class="form-control form-control-sm me-2" placeholder="Emp ID..." value="{{ request('employee_id') }}">
            <button class="btn btn-sm btn-primary">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm align-middle text-center" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th rowspan="2">Employee</th>
                        <th rowspan="2">Period</th>
                        <th colspan="4">Earnings</th>
                        <th colspan="4">Deductions</th>
                        <th rowspan="2" class="bg-light">Net Pay</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr class="small text-muted">
                        <th>Basic</th>
                        <th>OT</th>
                        <th>Bonus</th>
                        <th>ND</th>
                        <th>SSS</th>
                        <th>PagIbig</th>
                        <th>PH</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $item)
                    <tr>
                        <td class="text-start">
                            <strong>{{ $item->employee->full_name }}</strong><br>
                            <small class="text-muted">{{ $item->employee->employee_id }}</small>
                        </td>
                        <td>
                            <span class="small">{{ $item->payroll->start_date }}</span> to <br>
                            <span class="small">{{ $item->payroll->end_date }}</span>
                        </td>
                        <td>{{ number_format($item->basic_pay, 2) }}</td>
                        <td>{{ number_format($item->overtime_pay, 2) }}</td>
                        <td>{{ number_format($item->bonuses, 2) }}</td>
                        <td>{{ number_format($item->night_diff, 2) }}</td>
                        <td class="text-danger">{{ number_format($item->deductions_sss, 2) }}</td>
                        <td class="text-danger">{{ number_format($item->deductions_pagibig, 2) }}</td>
                        <td class="text-danger">{{ number_format($item->deductions_philhealth, 2) }}</td>
                        <td class="text-danger">{{ number_format($item->other_deductions, 2) }}</td>
                        <td class="bg-light text-primary fw-bold">P{{ number_format($item->net_pay, 2) }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('salaries.edit', $item->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <a href="{{ route('payroll.payslip', $item->id) }}" class="btn btn-sm btn-outline-info">Payslip</a>
                                <form action="{{ route('salaries.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove record?')">X</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-4 text-center">No salary records found. Process a payroll batch to generate records.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $salaries->links() }}
        </div>
    </div>
</div>
@endsection
