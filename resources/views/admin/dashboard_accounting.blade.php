@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Pulse Quick Actions -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden">
            <div class="card-body p-4 d-flex justify-content-between align-items-center position-relative">
                <div class="z-1">
                    <h4 class="fw-800 mb-1 tracking-tight">Payroll Command Center</h4>
                    <p class="mb-0 opacity-75">Accounting Dashboard: Manage payrolls, disbursements, and financial logs.</p>
                </div>
                <div class="d-flex gap-2 z-1">
                    <a href="{{ route('payroll.create') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Start New Payroll
                    </a>
                </div>
                <!-- Decorative Icon -->
                <i class="bi bi-cash-stack position-absolute end-0 top-50 translate-middle-y opacity-25" style="font-size: 8rem; margin-right: -2rem;"></i>
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
                <span class="fw-bold text-dark small">PENDING ACTIONS:</span>
                <div class="d-inline-flex gap-3 ms-3">
                    @if($pendingDtrs > 0)
                        <span class="badge bg-danger-subtle text-danger rounded-pill fw-bold">{{ $pendingDtrs }} DTRs to Process</span>
                    @endif
                    @if($unprocessedPayrolls > 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill fw-bold">{{ $unprocessedPayrolls }} Draft Payrolls</span>
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
                    <span class="fw-bold small tracking-wider">DRAFT PAYROLLS</span>
                    <i class="bi bi-file-earmark-diff fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $unprocessedPayrolls }}</h2>
                <div class="text-warning small fw-bold">Require Review</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">PENDING DTRS</span>
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $pendingDtrs }}</h2>
                <div class="text-danger small fw-bold">Need Finalization</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">PAYROLL DISBURSED</span>
                    <i class="bi bi-cash-coin fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1 text-truncate" style="max-width: 100%;">₱{{ number_format($totalPayrollDisbursed, 0) }}</h2>
                <div class="text-success small fw-bold">Total Net Pay</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden bg-primary-subtle border-1 border-primary border-opacity-25">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-primary">
                    <span class="fw-bold small tracking-wider">REPORTS</span>
                    <i class="bi bi-file-earmark-bar-graph fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1 text-primary-emphasis">EXPORT</h2>
                <a href="{{ route('payroll.index') }}" class="stretched-link text-primary text-decoration-none small fw-bold">Generate Summaries</a>
            </div>
        </div>
    </div>
    <!-- Stats Cards -->
    <!-- ... existing cards ... -->
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
                    <div class="card-header bg-white border-0 py-3 ps-4">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Site Distribution</h6>
                    </div>
                    <div class="card-body px-4 pt-0">
                        <div class="list-group list-group-flush">
                            @forelse($siteDistribution as $site)
                                <div class="list-group-item px-0 border-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="small text-muted fw-medium">{{ $site->site_name }}</span>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-bold me-2">{{ $site->total }}</span>
                                        <div class="progress" style="width: 60px; height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($totalEmployees > 0) ? ($site->total / $totalEmployees * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small py-3">No site data available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yield vs Overtime -->
            <div class="col-md-6 d-flex flex-column">
                <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                    <div class="card-header bg-white border-0 py-3 ps-4">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Yield vs Overtime</h6>
                    </div>
                    <div class="card-body px-4 pt-0 d-flex flex-column justify-content-center">
                        @php
                            $totalHours = ($yieldMetrics->reg_hours ?? 0) + ($yieldMetrics->ot_hours ?? 0);
                            $regPercent = $totalHours > 0 ? (($yieldMetrics->reg_hours ?? 0) / $totalHours * 100) : 0;
                            $otPercent = $totalHours > 0 ? (($yieldMetrics->ot_hours ?? 0) / $totalHours * 100) : 0;
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">Regular Hours</span>
                                <span class="small fw-bold">{{ number_format($yieldMetrics->reg_hours ?? 0, 1) }}h</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 12px;">
                                <div class="progress-bar bg-success-subtle text-success progress-bar-striped" role="progressbar" style="width: {{ $regPercent }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">Overtime Hours</span>
                                <span class="small fw-bold text-danger">{{ number_format($yieldMetrics->ot_hours ?? 0, 1) }}h</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 12px;">
                                <div class="progress-bar bg-danger-subtle text-danger progress-bar-striped" role="progressbar" style="width: {{ $otPercent }}%"></div>
                            </div>
                        </div>
                        <div class="mt-auto p-3 bg-light rounded-4 border-dashed">
                            <div class="text-center">
                                <div class="small text-muted mb-1 text-uppercase tracking-wider" style="font-size: 0.65rem;">Efficiency Ratio</div>
                                <h4 class="fw-800 mb-0">{{ $regPercent > 0 ? number_format($regPercent, 1) : 0 }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Area -->
    <div class="col-lg-4 d-flex flex-column">
        <!-- Payroll Deadline / Runway -->
        <div class="card shadow-sm border-0 rounded-4 bg-white mb-4 flex-shrink-0">
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
                            <p class="small text-muted mb-1">Target Disbursement</p>
                            <h6 class="fw-bold mb-0 text-dark">{{ $activePayroll->pay_date }}</h6>
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

        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 ps-4">
                <h6 class="mb-0 fw-bold">Workforce Calendar</h6>
            </div>
            <div class="card-body p-4 pt-0">
                <h6 class="fw-bold small text-muted mb-3 tracking-wider text-uppercase font-monospace border-bottom pb-2 mt-2">Upcoming Holidays</h6>
                @forelse($upcomingHolidays as $holiday)
                    <div class="d-flex align-items-center p-2 mb-2 bg-light rounded-3">
                        <div class="bg-primary text-white rounded-pill px-3 py-1 me-3 text-center" style="min-width: 60px;">
                            <span class="fw-800 small d-block lh-1">{{ \Carbon\Carbon::parse($holiday->date)->format('d') }}</span>
                            <span class="small opacity-75" style="font-size: 0.6rem;">{{ strtoupper(\Carbon\Carbon::parse($holiday->date)->format('M')) }}</span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold small text-dark">{{ $holiday->name }}</h6>
                            <span class="badge bg-primary-subtle text-primary border-primary-subtle py-0 small">{{ ucfirst($holiday->type) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">No upcoming holidays recorded</p>
                @endforelse

                <h6 class="fw-bold small text-muted mb-3 tracking-wider text-uppercase font-monospace border-bottom pb-2 mt-4">Employee Birthdays</h6>
                @forelse($upcomingBirthdays as $emp)
                    <div class="d-flex align-items-center mb-3">
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
                            <tr>
                                <td class="ps-4 fw-bold text-primary font-monospace small">{{ $p->payroll_code }}</td>
                                <td>
                                    @if($p->employee_id)
                                        <span class="text-info"><i class="bi bi-person me-1"></i>{{ $p->employee->full_name ?? 'Individual' }}</span>
                                    @else
                                        {{ $p->payrollGroup->name ?? 'All Groups' }}
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $p->start_date }} to {{ $p->end_date }}</small></td>
                                <td>
                                    <span class="badge border {{ $p->status == 'processed' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle' }} rounded-pill px-3 py-1">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-sm btn-link font-monospace text-decoration-none fw-bold">REVIEW</a>
                                </td>
                            </tr>
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
    var options = {
        series: [{
            name: 'Logs Today',
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
                stops: [20, 100, 100, 100]
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