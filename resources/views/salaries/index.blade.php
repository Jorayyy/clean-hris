@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">Salaries</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Payroll history and per-employee salary records.</p>
    </div>
    <form action="{{ route('salaries.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <select name="payroll_id" class="form-select" style="width: auto; min-width: 210px;" onchange="this.form.submit()">
            <option value="">All Periods</option>
            @foreach($payrolls as $p)
            <option value="{{ $p->id }}" {{ request('payroll_id') == $p->id ? 'selected' : '' }}>
                {{ $p->payroll_code }} ({{ \Carbon\Carbon::parse($p->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($p->end_date)->format('M d, Y') }})
            </option>
            @endforeach
        </select>
        <input type="text" name="employee_id" class="form-control" placeholder="Employee ID..." value="{{ request('employee_id') }}" style="width: 150px;">
        <button class="btn btn-light">Search</button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Employee</th>
                    <th>Coverage Period</th>
                    <th class="text-end">Earnings</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end pe-4">Net Pay</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $item)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-initial">{{ strtoupper(substr($item->employee->first_name, 0, 1)) }}{{ strtoupper(substr($item->employee->last_name ?? '', 0, 1)) }}</div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.92rem;">{{ $item->employee->full_name }}</div>
                                <div class="text-muted" style="font-size: 0.76rem;">{{ $item->employee->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size: 0.88rem;">
                        {{ \Carbon\Carbon::parse($item->payroll->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($item->payroll->end_date)->format('M d, Y') }}
                        <div class="text-muted" style="font-size: 0.72rem;">{{ $item->payroll->payroll_code }}</div>
                    </td>
                    <td class="text-end fw-semibold" style="color:#157347; font-size: 0.88rem;">&#8369;{{ number_format($item->total_earnings, 2) }}</td>
                    <td class="text-end fw-semibold" style="color:#d02f26; font-size: 0.88rem;">&#8369;{{ number_format($item->total_deductions, 2) }}</td>
                    <td class="text-end pe-4 fw-bold" style="font-size: 0.95rem;">&#8369;{{ number_format($item->net_pay, 2) }}</td>
                    <td class="text-end pe-4" style="width: 1%;">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('payroll.payslip', $item->id) }}" class="btn btn-sm btn-light btn-sm-pill" title="View Payslip">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Payslip
                            </a>
                            <a href="{{ route('salaries.edit', $item->id) }}" class="btn btn-sm btn-light icon-btn" title="Edit Salary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('salaries.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this salary record?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light icon-btn icon-danger" title="Delete record">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-cash-stack text-muted fs-2 d-block mb-2"></i>
                        <p class="text-muted small mb-3">No salary records found. Process a payroll batch to generate records.</p>
                        <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-primary">Go to Payroll</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $salaries->links() }}
</div>

<style>
    .avatar-initial {
        width: 34px; height: 34px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 0.72rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #0071e3, #0058b0);
    }
    .icon-btn { padding: 0.3rem 0.55rem; line-height: 1.2; }
    .icon-btn.icon-danger:hover { background: #ffe5e3; color: #d02f26; }
    .btn-sm-pill { border-radius: 980px; padding-left: 0.9rem; padding-right: 0.9rem; font-size: 0.8rem; }
</style>
@endsection
