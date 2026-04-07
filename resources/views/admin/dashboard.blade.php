@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow rounded border-0 border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Total Employees</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">{{ $totalEmployees }}</h3>
                    <i class="bi bi-people text-primary fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow rounded border-0 border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Clock-ins Today</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">{{ $totalAttendanceToday }}</h3>
                    <i class="bi bi-clock-history text-success fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow rounded border-0 border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Pending Support</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">{{ $pendingTickets }}</h3>
                    <i class="bi bi-ticket-perforated text-warning fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow rounded border-0 border-start border-info border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Total Disbursed</h6>
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-2">P{{ number_format($totalPayrollDisbursed, 2) }}</h3>
                    <i class="bi bi-cash-coin text-info fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Recent Payroll Batches -->
    <div class="col-md-8">
        <div class="card shadow rounded border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Recent Payroll Batches</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Batch Code</th>
                                <th>Group</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayrolls as $p)
                            <tr>
                                <td>{{ $p->payroll_code }}</td>
                                <td>{{ $p->payrollGroup->name }}</td>
                                <td><small>{{ $p->start_date }} to {{ $p->end_date }}</small></td>
                                <td>
                                    <span class="badge {{ $p->status == 'processed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Payroll Groups -->
    <div class="col-md-4">
        <div class="card shadow rounded border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Payroll Group Allocation</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($groupLabels as $index => $label)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>{{ $label }}</span>
                        <span class="badge bg-secondary rounded-pill">{{ $groupCounts[$index] }} Emps</span>
                    </li>
                    @endforeach
                </ul>
                <div class="mt-4 text-center">
                    <a href="{{ route('payroll-groups.index') }}" class="btn btn-sm btn-dark w-100">Manage Groups</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Support Tickets -->
    <div class="col-md-12">
        <div class="card shadow rounded border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Latest Support Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Employee</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTickets as $t)
                            <tr>
                                <td>
                                    <strong>{{ $t->employee->full_name }}</strong><br>
                                    <small class="text-muted">{{ $t->employee->employee_id }}</small>
                                </td>
                                <td>{{ $t->subject }}</td>
                                <td>
                                    <span class="badge {{ $t->status == 'open' ? 'bg-danger' : ($t->status == 'in_progress' ? 'bg-info' : 'bg-success') }}">
                                        {{ str_replace('_', ' ', strtoupper($t->status)) }}
                                    </span>
                                </td>
                                <td>{{ $t->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="btn btn-sm btn-link py-0">Review</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection