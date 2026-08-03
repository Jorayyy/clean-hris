@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Pulse Quick Actions -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4 bg-info text-white overflow-hidden">
            <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 position-relative">
                <div class="z-1">
                    <h4 class="fw-800 mb-1 tracking-tight">HR Operations Center</h4>
                    <p class="mb-0 opacity-75">Workforce Management Dashboard: Track employees, attendance, and support requests.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap z-1">
                    <a href="{{ route('employees.create') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-person-plus-fill me-2 text-info"></i>Add New Employee
                    </a>
                </div>
                <!-- Decorative Icon -->
                <i class="bi bi-people position-absolute end-0 top-50 translate-middle-y opacity-25" style="font-size: 8rem; margin-right: -2rem;"></i>
            </div>
        </div>
    </div>

    <!-- Critical HR To-Do's -->
    @if($pendingTickets > 0 || $pendingDtrs > 0)
    <div class="col-md-12">
        <div class="alert bg-white border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-0">
            <div class="bg-info-subtle text-info rounded-circle p-2 me-3">
                <i class="bi bi-bell-fill fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <span class="fw-bold text-dark small">PENDING HR ACTIONS:</span>
                <div class="d-inline-flex gap-3 ms-3">
                    @if($pendingTickets > 0)
                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill fw-bold">{{ $pendingTickets }} Open Tickets</span>
                    @endif
                    @if($pendingDtrs > 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill fw-bold">{{ $pendingDtrs }} DTRs Pending Review</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-info border-0 fw-bold">RESOLVE TICKETS <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="col-12 col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">ACTIVE STAFF</span>
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $totalEmployees }}</h2>
                <div class="text-success small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Deployment Active</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">TODAY'S ATTENDANCE</span>
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $totalAttendanceToday }}</h2>
                <div class="text-primary small fw-bold">Present Today</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">OPEN TICKETS</span>
                    <i class="bi bi-chat-dots fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $pendingTickets }}</h2>
                <div class="text-info small fw-bold">Needs Resolution</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden bg-info-subtle border-1 border-info border-opacity-25">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-info">
                    <span class="fw-bold small tracking-wider">ANNOUNCEMENTS</span>
                    <i class="bi bi-megaphone-fill fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1 text-info-emphasis">POST</h2>
                <a href="{{ route('announcements.create') }}" class="stretched-link text-info text-decoration-none small fw-bold">Broadcast to Staff</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Workforce Distribution -->
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 ps-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill me-2 text-info"></i>BPO Pulse (Classification)</h6>
            </div>
            <div class="card-body px-4">
                <div id="classChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

    <!-- Attendance Chart -->
    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold border-start border-4 border-primary ps-2">Attendance Volume (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <div id="attendanceChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 d-flex align-items-stretch">
    <!-- Workforce Insight Modules -->
    <div class="col-12 col-lg-8">
        <div class="row g-4 h-100">
            <!-- Site Distribution -->
            <div class="col-12 col-md-6 d-flex flex-column">
                <div class="card shadow-sm border-0 rounded-4 h-100">
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
            <div class="col-12 col-md-6 d-flex flex-column">
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
    <div class="col-12 col-lg-4 d-flex flex-column">
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
    <!-- Active Payroll Groups -->
    <div class="col-12 col-md-6">
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
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Recent Support Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light">
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
                                <td>{{ Str::limit($ticket->subject, 20) }}</td>
                                <td class="text-end pe-4">
                                    <span class="badge @if($ticket->status == 'open') bg-danger @elseif($ticket->status == 'in_progress') bg-warning @else bg-success @endif rounded-pill">
                                        {{ str_replace('_', ' ', $ticket->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4">No recent tickets</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Distribution -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Staff Distribution</h6>
            </div>
            <div class="card-body">
                @foreach($groups as $group)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>{{ $group->name }}</span>
                        <span class="fw-bold">{{ $group->employees_count }} Emps</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ ($totalEmployees > 0) ? ($group->employees_count / $totalEmployees * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
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
