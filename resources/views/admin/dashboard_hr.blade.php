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
        <p class="text-muted mb-0" style="font-size: 0.95rem;">{{ now()->format('l, F j, Y') }} &mdash; your workforce at a glance.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('employees.create') }}" class="btn btn-primary px-4"><i class="bi bi-person-plus me-2"></i>Add Employee</a>
        <a href="{{ route('announcements.create') }}" class="btn btn-light px-4"><i class="bi bi-megaphone me-2"></i>Announcement</a>
    </div>
</div>

@if($pendingTickets > 0 || $pendingDtrs > 0)
<div class="d-flex align-items-center flex-wrap gap-2 p-3 mb-4 rounded-4" style="background: #ffedeb;">
    <i class="bi bi-bell-fill text-danger ms-1"></i>
    <span class="fw-semibold small" style="color: #c41e13;">Pending HR actions</span>
    <div class="d-inline-flex gap-2 ms-1">
        @if($pendingTickets > 0)
            <span class="badge bg-white fw-semibold" style="color: #0b66b5;">{{ $pendingTickets }} ticket{{ $pendingTickets > 1 ? 's' : '' }} open</span>
        @endif
        @if($pendingDtrs > 0)
            <span class="badge bg-white text-danger fw-semibold">{{ $pendingDtrs }} DTR{{ $pendingDtrs > 1 ? 's' : '' }} pending review</span>
        @endif
    </div>
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-light ms-auto fw-semibold">Resolve<i class="bi bi-arrow-right ms-1"></i></a>
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
                <div class="small text-muted"><span class="dot dot-success me-1"></span>Deployment active</div>
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
        <a href="{{ route('admin.tickets.index') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Open Tickets</span>
                        <div class="kpi-chip" style="background: rgba(175,82,222,0.12); color: #8929b8;"><i class="bi bi-chat-dots-fill"></i></div>
                    </div>
                    <h2 class="fw-bold mb-1 kpi-number">{{ $pendingTickets }}</h2>
                    <div class="small" style="color: #0071e3;">Needs resolution<i class="bi bi-arrow-right ms-1"></i></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('announcements.create') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Announcements</span>
                        <div class="kpi-chip" style="background: rgba(255,159,10,0.14); color: #995f00;"><i class="bi bi-megaphone-fill"></i></div>
                    </div>
                    <h2 class="fw-bold mb-1 kpi-number">Post</h2>
                    <div class="small" style="color: #0071e3;">Broadcast to staff<i class="bi bi-arrow-right ms-1"></i></div>
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

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Recent Support Tickets</h6>
                <a href="{{ route('admin.tickets.index') }}" class="small text-decoration-none fw-medium">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentTickets as $ticket)
                        <div class="list-group-item px-4 d-flex align-items-center gap-3">
                            <div class="avatar-initial">{{ strtoupper(substr($ticket->employee->name ?? 'S', 0, 1)) }}</div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-truncate" style="font-size: 0.9rem;">{{ \Illuminate\Support\Str::limit($ticket->subject, 32) }}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.78rem;">{{ $ticket->employee->name ?? 'System' }}</div>
                            </div>
                            @if($ticket->status == 'open')
                                <span class="badge">Open</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge badge-orange">In progress</span>
                            @else
                                <span class="badge badge-green">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-0">No recent tickets.</p>
                        </div>
                    @endforelse
                </div>
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
            gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.02, stops: [20, 100] }
        },
        colors: ['#0071e3'],
        grid: { borderColor: 'rgba(0,0,0,0.05)', strokeDashArray: 0, padding: { left: 8, right: 8 } },
        tooltip: {
            theme: 'light',
            y: { formatter: function(v) { return v + ' punches'; } }
        }
    };
    new ApexCharts(document.querySelector('#attendanceChart'), options).render();

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
    new ApexCharts(document.querySelector('#classChart'), classOptions).render();
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
    .badge { font-size: 0.72rem; }
    .badge:not([class*="badge-"]):not(.bg-white) { background: #ffe5e3 !important; color: #d02f26 !important; }
    .badge-orange { background: #ffefd6 !important; color: #995f00 !important; }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
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
