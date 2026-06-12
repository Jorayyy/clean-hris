@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Pulse Quick Actions -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden">
            <div class="card-body p-4 d-flex justify-content-between align-items-center position-relative">
                <div class="z-1">
                    <h4 class="fw-800 mb-1 tracking-tight">Admin Command Center</h4>
                    <p class="mb-0 opacity-75">Welcome back! Here's the pulse of your workforce today.</p>
                </div>
                <div class="d-flex gap-2 z-1">
                    <a href="{{ route('payroll.create') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Start Payroll
                    </a>
                    <a href="{{ route('employees.create') }}" class="btn btn-primary fw-bold rounded-pill px-4 border border-white border-opacity-25 shadow-sm" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <i class="bi bi-person-plus-fill me-2"></i>Add Employee
                    </a>
                </div>
                <!-- Decorative Icon -->
                <i class="bi bi-shield-check position-absolute end-0 top-50 translate-middle-y opacity-25" style="font-size: 8rem; margin-right: -2rem;"></i>
            </div>
        </div>
    </div>

    <!-- Critical To-Do's Area -->
    @if($pendingDtrs > 0 || $unprocessedPayrolls > 0 || $pendingTickets > 0)
    <div class="col-md-12">
        <div class="alert bg-white border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-0">
            <div class="bg-danger-subtle text-danger rounded-circle p-2 me-3">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <span class="fw-bold text-dark small">PRIORITY TASKS:</span>
                <div class="d-inline-flex gap-3 ms-3">
                    @if($pendingDtrs > 0)
                        <span class="badge bg-danger-subtle text-danger rounded-pill fw-bold">{{ $pendingDtrs }} DTRs Pending</span>
                    @endif
                    @if($unprocessedPayrolls > 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill fw-bold">{{ $unprocessedPayrolls }} Draft Payrolls</span>
                    @endif
                    @if($pendingTickets > 0)
                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill fw-bold">{{ $pendingTickets }} Tickets Open</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.dtrs.index') }}" class="btn btn-sm btn-outline-danger border-0 fw-bold">RESOLVE NOW <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">ACTIVE STAFF</span>
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $totalEmployees }}</h2>
                <div class="text-success small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> System Active</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">ATTENDANCE</span>
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $totalAttendanceToday }}</h2>
                <div class="text-primary small fw-bold">Present Today</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">TOTAL DISBURSED</span>
                    <i class="bi bi-cash-coin fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1 text-truncate" style="max-width: 100%;">₱{{ number_format($totalPayrollDisbursed, 0) }}</h2>
                <div class="text-muted small">All-time Net Pay</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden bg-info-subtle border-1 border-info border-opacity-25">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-info">
                    <span class="fw-bold small tracking-wider">ANNOUNCEMENTS</span>
                    <i class="bi bi-megaphone-fill fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1 text-info-emphasis">POST</h2>
                <a href="{{ route('announcements.create') }}" class="stretched-link text-info text-decoration-none small fw-bold">Broadcast New Message</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Workforce Distribution -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 ps-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill me-2 text-info"></i>BPO Pulse (Classification)</h6>
            </div>
            <div class="card-body px-4">
                <div id="classChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

    <!-- Attendance Trend Chart -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center ps-4">
                <i class="bi bi-graph-up text-primary me-2"></i>
                <h6 class="mb-0 fw-bold">Attendance Volume (Last 7 Days)</h6>
            </div>
            <div class="card-body px-4">
                <div id="attendanceChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 d-flex align-items-stretch">
    <!-- Workforce Insight Modules -->
    <div class="col-lg-8">
        <div class="row g-4 h-100">
            <!-- Site Distribution -->
            <div class="col-md-6 d-flex flex-column">
                <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                    <div class="card-header bg-white border-0 py-3 ps-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2 text-primary"></i>Site Distribution</h6>
                    </div>
                    <div class="card-body px-4 pt-1">
                        <div class="list-group list-group-flush">
                            @forelse($siteDistribution as $site)
                                <div class="list-group-item px-0 border-0 py-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 shadow-none border-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-geo-alt-fill" style="font-size: 0.8rem;"></i>
                                    </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold small text-dark">{{ $site->site_name }}</h6>
                                            <span class="text-muted" style="font-size: 0.65rem;">Active Workforce</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold d-block lh-1 text-dark">{{ $site->total }}</span>
                                        <span class="text-muted" style="font-size: 0.6rem;">
                                            {{ ($totalEmployees > 0) ? round($site->total / $totalEmployees * 100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <p class="text-muted small mb-0">No site data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yield vs Overtime -->
            <div class="col-md-6 d-flex flex-column">
                <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                    <div class="card-header bg-white border-0 py-3 ps-4">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-clock me-2 text-danger"></i>Yield vs Overtime</h6>
                    </div>
                    <div class="card-body px-4 pt-1 d-flex flex-column">
                        @php
                            $totalHours = ($yieldMetrics->reg_hours ?? 0) + ($yieldMetrics->ot_hours ?? 0);
                        @endphp
                        
                        <div class="mb-3 mt-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted" style="font-weight: 500;">Regular Hours</span>
                                <span class="small text-dark fw-bold">{{ number_format($yieldMetrics->reg_hours ?? 0, 1) }}h</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px; background-color: #f1f1f1;">
                                <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ $totalHours > 0 ? (($yieldMetrics->reg_hours ?? 0)/$totalHours * 100) : 0 }}%; background-color: #0d6efd;"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted" style="font-weight: 500;">Overtime Hours</span>
                                <span class="small text-danger fw-bold">{{ number_format($yieldMetrics->ot_hours ?? 0, 1) }}h</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px; background-color: #f1f1f1;">
                                <div class="progress-bar bg-danger rounded-pill" role="progressbar" style="width: {{ $totalHours > 0 ? (($yieldMetrics->ot_hours ?? 0)/$totalHours * 100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div class="mt-auto mb-2 p-4 rounded-4 border-0 bg-light text-center">
                            <div class="text-muted mb-2 text-uppercase tracking-wider" style="font-size: 0.6rem; font-weight: 600;">EFFICIENCY RATIO</div>
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2rem;">{{ ($totalHours > 0 && $yieldMetrics->reg_hours > 0) ? number_format(($yieldMetrics->reg_hours / $totalHours) * 100, 1) : 0 }}%</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Area -->
    <div class="col-lg-4 d-flex flex-column">
        <!-- Payroll Deadline / Runway -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white flex-shrink-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="fw-bold small text-uppercase tracking-widest text-primary mb-0" style="letter-spacing: 0.1rem;">PAYROLL RUNWAY</h6>
                    @php
                        $activePayroll = \App\Models\Payroll::where('status', '!=', 'approved')->latest()->first();
                    @endphp
                    @if($activePayroll)
                        <span class="badge bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3">{{ $activePayroll->payroll_code }}</span>
                    @endif
                </div>

                @if($activePayroll)
                    @php
                        $status = strtolower($activePayroll->status);
                        $stages = [
                            ['label' => 'Setup', 'icon' => 'bi-gear', 'active' => true],
                            ['label' => 'Process', 'icon' => 'bi-cpu', 'active' => in_array($status, ['processing', 'processed', 'review'])],
                            ['label' => 'Review', 'icon' => 'bi-eye', 'active' => in_array($status, ['processed', 'review'])],
                            ['label' => 'Pay', 'icon' => 'bi-cash-stack', 'active' => false],
                        ];
                        $progress = match($status) {
                            'draft' => 25,
                            'processing' => 50,
                            'processed' => 75,
                            'review' => 90,
                            default => 10
                        };
                    @endphp
                    <div class="payroll-runway-container py-3">
                        <div class="position-relative mb-5" style="height: 40px;">
                            <div class="progress position-absolute w-100" style="height: 4px; top: 18px; border-radius: 2px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between position-absolute w-100">
                                @foreach($stages as $stage)
                                    <div class="text-center" style="width: 25%;">
                                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm {{ $stage['active'] ? 'bg-primary text-white border-primary' : 'bg-white text-muted border' }}" 
                                             style="width: 36px; height: 36px; position: relative; z-index: 2; border-width: 2px !important;">
                                            <i class="bi {{ $stage['icon'] }} small"></i>
                                        </div>
                                        <div class="mt-2 text-center" style="width: 100%;">
                                            <span class="d-block small fw-bold {{ $stage['active'] ? 'text-primary' : 'text-muted' }}" style="font-size: 0.7rem;">{{ $stage['label'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-5 p-3 bg-light rounded-4 border-start border-primary border-4">
                            <p class="small text-muted mb-1">Current Status</p>
                            <h6 class="fw-bold mb-0 text-dark">{{ ucfirst($activePayroll->status) }}</h6>
                        </div>
                    </div>
                @else
                    @php
                        $today = \Carbon\Carbon::now();
                        $nextPayrollDate = $today->day <= 15 
                            ? $today->copy()->day(15) 
                            : $today->copy()->endOfMonth();
                    @endphp
                    <div class="text-center py-5 px-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                            <i class="bi bi-calendar2-check text-primary fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Next Estimated Payroll</h6>
                        <p class="text-muted small mb-4">{{ $nextPayrollDate->format('F d, Y') }}<br><span class="opacity-75">{{ $nextPayrollDate->format('l') }}</span></p>
                        
                        <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm rounded-pill px-5 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Create New Batch
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 flex-grow-1 bg-white">
            <div class="card-header bg-white border-0 py-3 ps-4">
                <h6 class="mb-0 fw-bold">Workforce Calendar</h6>
            </div>
            <div class="card-body p-4 pt-0">
                <h6 class="fw-bold small text-muted mb-2 tracking-wider text-uppercase font-monospace border-bottom pb-2 mt-2">Upcoming Holidays</h6>
                @forelse($upcomingHolidays as $holiday)
                    <div class="d-flex align-items-center p-2 mb-2 bg-light rounded-3">
                        <div class="bg-primary text-white rounded-pill px-2 py-1 me-3 text-center" style="min-width: 50px;">
                            <span class="fw-800 small d-block lh-1">{{ \Carbon\Carbon::parse($holiday->date)->format('d') }}</span>
                            <span class="small opacity-75" style="font-size: 0.55rem;">{{ strtoupper(\Carbon\Carbon::parse($holiday->date)->format('M')) }}</span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 fw-bold small text-dark text-truncate">{{ $holiday->name }}</h6>
                            <span class="badge bg-primary-subtle text-primary border-primary-subtle py-0" style="font-size: 0.6rem;">{{ ucfirst($holiday->type) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">No upcoming holidays recorded</p>
                @endforelse

                <h6 class="fw-bold small text-muted mb-2 tracking-wider text-uppercase font-monospace border-bottom pb-2 mt-3">Employee Birthdays</h6>
                @forelse($upcomingBirthdays as $emp)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0">
                            @if($emp->photo)
                                <img src="{{ asset('storage/' . $emp->photo) }}" class="rounded-circle shadow-sm" width="35" height="35" style="object-fit: cover;">
                            @else
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                                    <i class="bi bi-person small"></i>
                                </div>
                            @endif
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-0 fw-bold small text-dark">{{ $emp->full_name }}</h6>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($emp->birthday)->format('M d') }}</span>
                        </div>
                        <div class="bg-warning-subtle text-warning p-1 rounded-pill">
                            <i class="bi bi-cake2-fill" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">None this month</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Payroll Batches -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-0 ps-4">
                <h6 class="mb-0 fw-bold">Recent Payroll Batches</h6>
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
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayrolls as $p)
                        <td class="ps-4 fw-bold text-primary font-monospace" style="font-size: 0.75rem;">{{ $p->payroll_code }}</td>
                        <td style="font-size: 0.8rem;">
                            @if($p->employee_id)
                                <span class="text-info"><i class="bi bi-person me-1"></i>{{ $p->employee->full_name ?? 'Individual' }}</span>
                            @else
                                {{ $p->payrollGroup->name ?? 'All Groups' }}
                            @endif
                        </td>
                        <td style="font-size: 0.75rem;"><span class="text-muted">{{ $p->start_date }} to {{ $p->end_date }}</span></td>
                        <td>
                            <span class="badge border {{ $p->status == 'processed' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle' }} rounded-pill px-3 py-1" style="font-size: 0.65rem;">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-sm btn-link font-monospace text-decoration-none fw-bold" style="font-size: 0.7rem;">REVIEW</a>
                        </td>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentPayrolls->hasPages())
                <div class="card-footer bg-white border-0 px-4 pb-4">
                    {{ $recentPayrolls->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Active Payroll Groups -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0 ps-4">
                <h6 class="mb-0 fw-bold">Distribution by Group</h6>
            </div>
            <div class="card-body p-4 pt-1">
                @foreach($groups as $group)
                    @php 
                        $percentage = $totalEmployees > 0 ? ($group->employees_count / $totalEmployees) * 100 : 0;
                        $colors = ['primary', 'info', 'success', 'warning', 'danger'];
                        $clr = $colors[$loop->index % count($colors)];
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold small text-dark">{{ $group->name }}</span>
                            <span class="text-muted fw-bold" style="font-size: 0.75rem;">{{ $group->employees_count }} Emps</span>
                        </div>
                        <div class="progress rounded-pill shadow-sm" style="height: 10px;">
                            <div class="progress-bar bg-{{ $clr }} rounded-pill" role="progressbar" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
                <div class="mt-4">
                    <a href="{{ route('payroll-groups.index') }}" class="btn btn-light w-100 rounded-pill fw-bold border shadow-sm btn-sm py-2 text-muted">
                        <i class="bi bi-gear-fill me-2"></i> Manage Groups
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Trend Chart
    var options = {
        series: [{
            name: 'Punches',
            data: @json($attendanceCounts ?? [])
        }],
        chart: {
            height: 250,
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
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100]
            }
        },
        colors: ['#0d6efd'],
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
    };

    var chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
    chart.render();

    // Classification Donut Chart
    var classOptions = {
        series: @json($classificationCounts->pluck('total')),
        labels: @json($classificationCounts->pluck('classification')),
        chart: {
            height: 250,
            type: 'donut',
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom' },
        colors: ['#0d6efd', '#0dcaf0', '#198754', '#ffc107', '#dc3545'],
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        }
    };
    var classChart = new ApexCharts(document.querySelector("#classChart"), classOptions);
    classChart.render();
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
    .font-monospace { font-family: 'JetBrains Mono', 'Courier New', monospace !important; }
    .progress-bar { transition: width 1s ease-in-out; }
</style>
@endsection
                            show: true,
                            label: 'Total Staff',
                            formatter: function (w) { return {{ $totalEmployees }} }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom' }
    };
    new ApexCharts(document.querySelector("#staffDistributionChart"), distributionOptions).render();
});
</script>
@endpush

@endsection