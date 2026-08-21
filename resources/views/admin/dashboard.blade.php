@extends('layouts.app')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim(Auth::user()->name ?? Auth::user()->email))[0];
@endphp

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">{{ $greeting }}, {{ $firstName }}</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">{{ now()->format('l, F j, Y') }} &mdash; here's the pulse of your workforce.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('payroll.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Start Payroll</a>
        <a href="{{ route('employees.create') }}" class="btn btn-light px-4"><i class="bi bi-person-plus me-2"></i>Add Employee</a>
    </div>
</div>

@if(($pendingDtrs ?? 0) > 0 || ($unprocessedPayrolls ?? 0) > 0 || ($pendingTickets ?? 0) > 0)
<div class="d-flex align-items-center flex-wrap gap-2 p-3 mb-4 rounded-4" style="background: #ffedeb;">
    <i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>
    <span class="fw-semibold small" style="color: #c41e13;">Priority</span>
    <div class="d-inline-flex gap-2 ms-1">
        @if(($pendingDtrs ?? 0) > 0)
            <span class="badge bg-white text-danger fw-semibold">{{ $pendingDtrs }} DTR{{ $pendingDtrs > 1 ? 's' : '' }} pending</span>
        @endif
        @if(($unprocessedPayrolls ?? 0) > 0)
            <span class="badge bg-white fw-semibold" style="color: #995f00;">{{ $unprocessedPayrolls }} draft payroll{{ $unprocessedPayrolls > 1 ? 's' : '' }}</span>
        @endif
        @if(($pendingTickets ?? 0) > 0)
            <span class="badge bg-white fw-semibold" style="color: #0b66b5;">{{ $pendingTickets }} ticket{{ $pendingTickets > 1 ? 's' : '' }} open</span>
        @endif
    </div>
    <a href="{{ route('admin.dtrs.index') }}" class="btn btn-sm btn-light ms-auto fw-semibold">Resolve<i class="bi bi-arrow-right ms-1"></i></a>
</div>
@endif

<div class="row g-3 g-md-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Active Staff</span>
                    <div class="kpi-chip" style="background: rgba(0,113,227,0.1); color: #0071e3;"><i class="bi bi-people-fill"></i></div>
                </div>
                <h2 class="fw-bold mb-1 kpi-number">{{ $totalEmployees }}</h2>
                <div class="small text-muted"><span class="dot dot-success me-1"></span>All systems operational</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Present Today</span>
                    <div class="kpi-chip" style="background: rgba(52,199,89,0.12); color: #248a3d;"><i class="bi bi-clock-history"></i></div>
                </div>
                <h2 class="fw-bold mb-1 kpi-number">{{ $totalAttendanceToday }}</h2>
                <div class="small {{ $lateAttendanceToday > 0 ? 'text-danger' : 'text-muted' }}">
                    @if($lateAttendanceToday > 0)<i class="bi bi-exclamation-circle me-1"></i>@endif
                    {{ $lateAttendanceToday }} late arrival{{ $lateAttendanceToday == 1 ? '' : 's' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Net Disbursed</span>
                    <div class="kpi-chip" style="background: rgba(255,159,10,0.14); color: #995f00;"><i class="bi bi-cash-coin"></i></div>
                </div>
                <h2 class="fw-bold mb-1 kpi-number">&#8369;{{ number_format($totalPayrollDisbursed, 0) }}</h2>
                <div class="small text-muted">All-time net pay</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.tickets.index') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Open Tickets</span>
                        <div class="kpi-chip" style="background: rgba(175,82,222,0.12); color: #8929b8;"><i class="bi bi-chat-dots-fill"></i></div>
                    </div>
                    <h2 class="fw-bold mb-1 kpi-number">{{ $pendingTickets }}</h2>
                    <div class="small" style="color: #0071e3;">View transactions<i class="bi bi-arrow-right ms-1"></i></div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Attendance Volume</h6>
                <span class="text-muted small">Last 7 days</span>
            </div>
            <div class="card-body pt-2">
                <div id="attendanceChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Workforce Mix</h6>
                <span class="text-muted small">By classification</span>
            </div>
            <div class="card-body pt-2">
                <div id="classChart"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Needs Attention</h6>
                <a href="{{ route('attendance.index') }}" class="small text-decoration-none fw-medium">All attendance</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($attendanceExceptions as $ex)
                        <div class="list-group-item px-4 d-flex align-items-center gap-3">
                            <div class="avatar-initial">{{ strtoupper(substr($ex->employee->full_name ?? '?', 0, 1)) }}</div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-truncate" style="font-size: 0.9rem;">{{ $ex->employee->full_name ?? 'Unknown' }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ \Carbon\Carbon::parse($ex->date)->format('D, M j') }}</div>
                            </div>
                            @if($ex->late_minutes > 0)
                                <span class="badge">+{{ $ex->late_minutes }}m late</span>
                            @endif
                            @if($ex->undertime_minutes > 0)
                                <span class="badge badge-orange">-{{ $ex->undertime_minutes }}m UT</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-0">Clean week &mdash; no late or undertime records.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 d-flex flex-column gap-3 gap-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Hours This Month</h6>
                <span class="text-muted small">Regular vs overtime</span>
            </div>
            <div class="card-body">
                @php $totalHours = ($yieldMetrics->reg_hours ?? 0) + ($yieldMetrics->ot_hours ?? 0); @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Regular</span>
                        <span class="small fw-semibold">{{ number_format($yieldMetrics->reg_hours ?? 0, 1) }}h</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $totalHours > 0 ? (($yieldMetrics->reg_hours ?? 0)/$totalHours * 100) : 0 }}%; background-color: #0071e3;"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Overtime</span>
                        <span class="small fw-semibold">{{ number_format($yieldMetrics->ot_hours ?? 0, 1) }}h</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $totalHours > 0 ? (($yieldMetrics->ot_hours ?? 0)/$totalHours * 100) : 0 }}%; background-color: #ff9f0a;"></div>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between p-3 rounded-4" style="background: #f5f5f7;">
                    <span class="text-muted" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;">Efficiency</span>
                    <span class="fw-bold" style="font-size: 1.6rem; letter-spacing: -0.03em;">{{ ($totalHours > 0 && ($yieldMetrics->reg_hours ?? 0) > 0) ? number_format(($yieldMetrics->reg_hours / $totalHours) * 100, 1) : 0 }}%</span>
                </div>
            </div>
        </div>

        <div class="card flex-grow-1">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Upcoming Holidays</h6>
            </div>
            <div class="card-body pt-1">
                @forelse($upcomingHolidays as $holiday)
                    <div class="d-flex align-items-center py-2 border-bottom hairline">
                        <div class="holiday-date me-3">
                            <span class="fw-bold d-block lh-1">{{ \Carbon\Carbon::parse($holiday->date)->format('d') }}</span>
                            <span class="text-muted" style="font-size: 0.58rem; letter-spacing: 0.05em;">{{ strtoupper(\Carbon\Carbon::parse($holiday->date)->format('M')) }}</span>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold text-truncate" style="font-size: 0.88rem;">{{ $holiday->name }}</div>
                            <span class="text-muted" style="font-size: 0.72rem; text-transform: capitalize;">{{ ucfirst($holiday->type) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-3 mb-0">No upcoming holidays recorded</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Payroll Runway</h6>
                @php
                    $activePayroll = \App\Models\Payroll::where('status', '!=', 'approved')->latest()->first();
                @endphp
                @if($activePayroll)
                    <span class="code-chip">{{ $activePayroll->payroll_code }}</span>
                @endif
            </div>
            <div class="card-body">
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
                    <div class="position-relative mb-2" style="height: 64px;">
                        <div class="progress position-absolute w-100" style="height: 3px; top: 16px; background: rgba(0,0,0,0.07);">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%; background-color: #0071e3;"></div>
                        </div>
                        <div class="d-flex justify-content-between position-absolute w-100">
                            @foreach($stages as $stage)
                                <div class="text-center" style="width: 25%;">
                                    <div class="stage-dot mx-auto d-flex align-items-center justify-content-center {{ $stage['active'] ? 'stage-active' : '' }}">
                                        <i class="bi {{ $stage['icon'] }}"></i>
                                    </div>
                                    <span class="d-block mt-2 fw-medium {{ $stage['active'] ? '' : 'text-muted' }}" style="font-size: 0.7rem; {{ $stage['active'] ? 'color:#0071e3;' : '' }}">{{ $stage['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="p-3 rounded-4 mt-4" style="background: #f5f5f7;">
                        <p class="small text-muted mb-1">Current status</p>
                        <h6 class="fw-semibold mb-0" style="text-transform: capitalize;">{{ ucfirst($activePayroll->status) }}</h6>
                    </div>
                @else
                    @php
                        $today = \Carbon\Carbon::now();
                        $nextPayrollDate = $today->day <= 15 ? $today->copy()->day(15) : $today->copy()->endOfMonth();
                    @endphp
                    <div class="text-center py-4 px-3">
                        <div class="kpi-chip mx-auto mb-3" style="background: rgba(0,113,227,0.1); color: #0071e3; width: 48px; height: 48px; font-size: 1.25rem;"><i class="bi bi-calendar2-check"></i></div>
                        <h6 class="fw-semibold mb-1">Next Estimated Payroll</h6>
                        <p class="text-muted small mb-4">{{ $nextPayrollDate->format('F j, Y') }} &middot; {{ $nextPayrollDate->format('l') }}</p>
                        <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm px-4"><i class="bi bi-plus-lg me-1"></i>Create Batch</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Recent Payroll Batches</h6>
                <a href="{{ route('payroll.index') }}" class="small text-decoration-none fw-medium">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Batch</th>
                            <th>Group</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayrolls as $p)
                            <tr>
                                <td class="ps-4"><span class="code-chip">{{ $p->payroll_code }}</span></td>
                                <td style="font-size: 0.88rem;">
                                    @if($p->employee_id)
                                        {{ $p->employee->full_name ?? 'Individual' }}
                                    @else
                                        {{ $p->payrollGroup->name ?? 'All Groups' }}
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 0.82rem;">{{ $p->start_date }} &ndash; {{ $p->end_date }}</td>
                                <td>
                                    @if($p->status == 'processed')
                                        <span class="badge badge-blue">Processed</span>
                                    @elseif($p->status == 'approved')
                                        <span class="badge badge-green">Approved</span>
                                    @else
                                        <span class="badge badge-orange">{{ ucfirst($p->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-sm btn-light">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No recent payroll batches available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Site Distribution</h6></div>
            <div class="card-body pt-1">
                @forelse($siteDistribution as $site)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom hairline">
                        <div class="d-flex align-items-center gap-3 min-width-0">
                            <div class="kpi-chip flex-shrink-0" style="background: rgba(0,113,227,0.1); color: #0071e3; width: 30px; height: 30px; font-size: 0.75rem;"><i class="bi bi-geo-alt-fill"></i></div>
                            <div class="text-truncate">
                                <div class="fw-semibold text-truncate" style="font-size: 0.88rem;">{{ $site->site_name }}</div>
                                <span class="text-muted" style="font-size: 0.72rem;">Active workforce</span>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0 ms-3">
                            <span class="fw-semibold d-block lh-1">{{ $site->total }}</span>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ ($totalEmployees > 0) ? round($site->total / $totalEmployees * 100, 1) : 0 }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-4 mb-0">No site data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Payroll Groups</h6>
                <a href="{{ route('payroll-groups.index') }}" class="small text-decoration-none fw-medium">Manage</a>
            </div>
            <div class="card-body pt-2">
                @foreach($groups as $group)
                    @php
                        $percentage = $totalEmployees > 0 ? ($group->employees_count / $totalEmployees) * 100 : 0;
                        $palette = ['#0071e3', '#32ade6', '#34c759', '#ff9f0a', '#af52de', '#ff3b30'];
                        $clr = $palette[$loop->index % count($palette)];
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium" style="font-size: 0.88rem;">{{ $group->name }}</span>
                            <span class="text-muted fw-medium" style="font-size: 0.78rem;">{{ $group->employees_count }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%; background-color: {{ $clr }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Birthdays</h6>
                <span class="text-muted small">Next 30 days</span>
            </div>
            <div class="card-body pt-1">
                @forelse($upcomingBirthdays as $emp)
                    <div class="d-flex align-items-center py-2 border-bottom hairline">
                        @if($emp->photo)
                            <img src="{{ asset('storage/' . $emp->photo) }}" class="avatar-initial p-0 me-3" style="object-fit: cover;" alt="">
                        @else
                            <div class="avatar-initial me-3">{{ strtoupper(substr($emp->full_name, 0, 1)) }}</div>
                        @endif
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold text-truncate" style="font-size: 0.88rem;">{{ $emp->full_name }}</div>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($emp->birthday)->format('M j') }}</span>
                        </div>
                        <i class="bi bi-cake2-fill" style="color: #ff9f0a;"></i>
                    </div>
                @empty
                    <p class="text-muted small text-center py-4 mb-0">None this month</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var appleFont = 'Inter, -apple-system, sans-serif';
    var muted = '#86868b';

    var options = {
        series: [{
            name: 'Punches',
            data: @json($attendanceCounts ?? [])
        }],
        chart: {
            height: 260,
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: appleFont,
            animations: { easing: 'easeout', speed: 600 }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5, lineCap: 'round' },
        xaxis: {
            categories: @json($attendanceLabels ?? []),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: muted, fontSize: '11px', fontWeight: 500 } }
        },
        yaxis: { show: false },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.28,
                opacityTo: 0.02,
                stops: [20, 100]
            }
        },
        colors: ['#0071e3'],
        grid: { borderColor: 'rgba(0,0,0,0.05)', strokeDashArray: 0, padding: { left: 8, right: 8 } },
        tooltip: {
            theme: 'light',
            y: { formatter: function(v) { return v + ' punches'; } }
        }
    };
    new ApexCharts(document.querySelector("#attendanceChart"), options).render();

    var classOptions = {
        series: @json($classificationCounts->pluck('total')),
        labels: @json($classificationCounts->pluck('classification')),
        chart: {
            height: 260,
            type: 'donut',
            fontFamily: appleFont,
            animations: { speed: 600 }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '12px',
            fontWeight: 500,
            markers: { size: 6, shape: 'circle' },
            itemMargin: { horizontal: 6 }
        },
        colors: ['#0071e3', '#34c759', '#ff9f0a', '#ff3b30', '#af52de', '#5ac8fa', '#8e8e93'],
        stroke: { width: 2, colors: ['#ffffff'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '74%',
                    labels: {
                        show: true,
                        name: { fontFamily: appleFont, fontSize: '12px', color: muted },
                        value: { fontFamily: appleFont, fontSize: '26px', fontWeight: 700, color: '#1d1d1f' },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total Staff',
                            fontFamily: appleFont,
                            fontSize: '12px',
                            color: muted,
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                            }
                        }
                    }
                }
            }
        },
        tooltip: { theme: 'light' }
    };
    new ApexCharts(document.querySelector("#classChart"), classOptions).render();
});
</script>

<style>
    .kpi-number { font-size: 1.85rem; letter-spacing: -0.03em; }
    .kpi-chip {
        width: 36px; height: 36px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; font-size: 0.95rem; flex-shrink: 0;
    }
    .avatar-initial {
        width: 34px; height: 34px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 0.8rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #8e8e93, #636366);
    }
    .avatar-initial:nth-child(odd) { background: linear-gradient(135deg, #0071e3, #0058b0); }
    .dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; vertical-align: middle; }
    .dot-success { background: #34c759; box-shadow: 0 0 0 3px rgba(52,199,89,0.15); }
    .hairline { border-color: rgba(0,0,0,0.06) !important; }
    .min-width-0 { min-width: 0; }
    .code-chip {
        font-family: ui-monospace, 'SF Mono', Menlo, monospace;
        font-size: 0.72rem; font-weight: 600;
        background: rgba(0,113,227,0.08); color: #0071e3;
        padding: 0.25rem 0.6rem; border-radius: 7px;
        letter-spacing: 0.02em;
    }
    .badge { font-size: 0.72rem; }
    .badge:not([class*="badge-"]):not(.bg-white) { background: #ffe5e3 !important; color: #d02f26 !important; }
    .badge-orange { background: #ffefd6 !important; color: #995f00 !important; }
    .badge-blue { background: rgba(0,113,227,0.1) !important; color: #0071e3 !important; }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
    .stage-dot {
        width: 34px; height: 34px; border-radius: 50%;
        background: #fff; border: 1.5px solid rgba(0,0,0,0.12);
        color: #86868b; font-size: 0.8rem;
        position: relative; z-index: 2;
    }
    .stage-dot.stage-active {
        background: #0071e3; border-color: #0071e3; color: #fff;
        box-shadow: 0 3px 10px rgba(0,113,227,0.35);
    }
    .holiday-date {
        min-width: 42px; text-align: center;
        background: #f5f5f7; border-radius: 10px; padding: 0.45rem 0.3rem;
    }
    .card-hover { transition: transform 0.2s cubic-bezier(0.25,0.1,0.25,1), box-shadow 0.2s cubic-bezier(0.25,0.1,0.25,1); }
    .card-hover:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.05), 0 16px 40px rgba(0,0,0,0.08); }
    .apexcharts-tooltip { border-radius: 12px !important; border: none !important; box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important; }
    .apexcharts-legend-text { color: #6e6e73 !important; }
</style>
@endsection
