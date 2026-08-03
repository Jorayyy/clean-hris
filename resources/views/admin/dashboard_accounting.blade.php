@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden mb-4">
        <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 position-relative">
            <div class="z-1">
                <p class="text-white-50 small mb-1 text-uppercase fw-semibold tracking-wider">HRIS Operations</p>
                <h4 class="fw-800 mb-2 tracking-tight">Payroll & Attendance Command Center</h4>
                <p class="mb-0 opacity-75">A focused view of the work that needs attention today: attendance, payroll, exceptions, and open requests.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-start justify-content-lg-end z-1">
                <a href="{{ route('employees.create') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                    <i class="bi bi-person-plus-fill me-2 text-primary"></i>Add Employee
                </a>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-light fw-bold rounded-pill px-4 shadow-sm">
                    <i class="bi bi-clock-history me-2"></i>Attendance
                </a>
                <a href="{{ route('payroll.create') }}" class="btn btn-primary fw-bold rounded-pill px-4 border border-white border-opacity-25 shadow-sm" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                    <i class="bi bi-plus-circle-fill me-2"></i>Start Payroll
                </a>
            </div>
            <i class="bi bi-building-gear position-absolute end-0 top-50 translate-middle-y opacity-25" style="font-size: 8rem; margin-right: -2rem;"></i>
        </div>
    </div>

    @if(($pendingDtrs ?? 0) > 0 || ($unprocessedPayrolls ?? 0) > 0 || ($pendingTickets ?? 0) > 0)
    <div class="alert bg-white border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-4">
        <div class="bg-danger-subtle text-danger rounded-circle p-2 me-3">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        </div>
        <div class="flex-grow-1">
            <span class="fw-bold text-dark small">PENDING ACTIONS:</span>
            <div class="d-inline-flex gap-2 ms-3 flex-wrap">
                @if(($pendingDtrs ?? 0) > 0)
                    <span class="badge bg-danger-subtle text-danger rounded-pill fw-bold">{{ $pendingDtrs }} DTRs to review</span>
                @endif
                @if(($unprocessedPayrolls ?? 0) > 0)
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill fw-bold">{{ $unprocessedPayrolls }} draft payrolls</span>
                @endif
                @if(($pendingTickets ?? 0) > 0)
                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill fw-bold">{{ $pendingTickets }} open tickets</span>
                @endif
            </div>
        </div>
        <a href="{{ route('admin.dtrs.index') }}" class="btn btn-sm btn-outline-danger border-0 fw-bold">Resolve now <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-3">
            <div class="card shadow-sm rounded-4 border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span class="fw-bold small tracking-wider">ACTIVE STAFF</span>
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <h2 class="fw-800 mb-1">{{ $totalEmployees }}</h2>
                    <div class="text-success small fw-bold">Currently active</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm rounded-4 border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span class="fw-bold small tracking-wider">PRESENT TODAY</span>
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <h2 class="fw-800 mb-1">{{ $totalAttendanceToday }}</h2>
                    <div class="text-primary small fw-bold">Real punches</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm rounded-4 border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span class="fw-bold small tracking-wider">LATE TODAY</span>
                        <i class="bi bi-alarm fs-5"></i>
                    </div>
                    <h2 class="fw-800 mb-1">{{ $lateAttendanceToday }}</h2>
                    <div class="text-danger small fw-bold">Attendance exceptions</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden bg-primary-subtle border-1 border-primary border-opacity-25">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3 text-primary">
                        <span class="fw-bold small tracking-wider">DRAFT PAYROLLS</span>
                        <i class="bi bi-file-earmark-diff fs-5"></i>
                    </div>
                    <h2 class="fw-800 mb-1 text-primary-emphasis">{{ $unprocessedPayrolls }}</h2>
                    <a href="{{ route('payroll.index') }}" class="stretched-link text-primary text-decoration-none small fw-bold">Open payroll queue</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 ps-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold">Attendance Volume (Last 7 Days)</h6>
                        <div class="small text-muted">Real punches, not placeholder rows</div>
                    </div>
                    <i class="bi bi-graph-up-arrow text-primary fs-5"></i>
                </div>
                <div class="card-body px-4">
                    <div id="attendanceChart" style="min-height: 280px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 ps-4">
                    <h6 class="mb-0 fw-bold">Attendance Exceptions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Late</th>
                                    <th>Undertime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceExceptions as $attendance)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $attendance->employee->full_name ?? 'Unknown' }}</div>
                                            <div class="text-muted x-small">{{ \Carbon\Carbon::parse($attendance->date)->format('M d') }}</div>
                                        </td>
                                        <td class="text-danger fw-bold">{{ (int) $attendance->late_minutes }}m</td>
                                        <td class="text-warning fw-bold">{{ (int) $attendance->undertime_minutes }}m</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No attendance exceptions in the last 7 days.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 ps-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Recent Payroll Batches</h6>
                    <a href="{{ route('payroll.index') }}" class="small fw-bold text-decoration-none">View all</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light font-monospace small text-muted text-uppercase tracking-wider">
                                <tr>
                                    <th class="ps-4">Batch</th>
                                    <th>Group</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayrolls as $payroll)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary font-monospace" style="font-size: 0.75rem;">{{ $payroll->payroll_code }}</td>
                                        <td style="font-size: 0.8rem;">
                                            @if($payroll->employee_id)
                                                {{ $payroll->employee->full_name ?? 'Individual' }}
                                            @else
                                                {{ $payroll->payrollGroup->name ?? 'All Groups' }}
                                            @endif
                                        </td>
                                        <td style="font-size: 0.75rem;" class="text-muted">{{ $payroll->start_date }} to {{ $payroll->end_date }}</td>
                                        <td>
                                            <span class="badge border {{ $payroll->status == 'processed' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle' }} rounded-pill px-3 py-1" style="font-size: 0.65rem;">
                                                {{ ucfirst($payroll->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No recent payroll batches available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 ps-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Open Tickets</h6>
                    <a href="{{ route('admin.tickets.index') }}" class="small fw-bold text-decoration-none">View all</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Subject</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets as $ticket)
                                    <tr>
                                        <td class="ps-4">{{ $ticket->employee->name ?? 'System' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 24) }}</td>
                                        <td class="text-end pe-4">
                                            <span class="badge rounded-pill {{ $ticket->status == 'open' ? 'bg-danger' : ($ticket->status == 'in_progress' ? 'bg-warning text-dark' : 'bg-success') }}">
                                                {{ str_replace('_', ' ', $ticket->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No recent tickets.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        series: [{
            name: 'Punches',
            data: @json($attendanceCounts ?? [])
        }],
        chart: {
            height: 280,
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: @json($attendanceLabels ?? []),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: { show: false },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [20, 100]
            }
        },
        colors: ['#0d6efd'],
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
    };

    var chart = new ApexCharts(document.querySelector('#attendanceChart'), options);
    chart.render();
});
</script>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-4 { border-radius: 1rem !important; }
    .bg-primary-subtle { background-color: #e0f2fe !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; }
    .bg-warning-subtle { background-color: #fef3c7 !important; }
    .bg-info-subtle { background-color: #e0f2fe !important; }
    .x-small { font-size: 0.7rem; }
    .font-monospace { font-family: 'JetBrains Mono', 'Courier New', monospace !important; }
    .progress-bar { transition: width 1s ease-in-out; }
</style>
@endsection