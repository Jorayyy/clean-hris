@extends('layouts.app')

@section('content')
@php
    $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $dayLetters = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
    $shiftById = $shifts->keyBy('id');
@endphp

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">Scheduling</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Define work times once, apply them to whole sites, and override individuals when needed.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="step-num">1</div>
                    <div>
                        <span class="fw-semibold d-block" style="font-size: 0.92rem;">Create Shifts</span>
                        <span class="text-muted small">Name your standard work times once &mdash; e.g. &ldquo;Day Shift 8AM&ndash;5PM&rdquo;.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="step-num step-green">2</div>
                    <div>
                        <span class="fw-semibold d-block" style="font-size: 0.92rem;">Set Weekly Patterns</span>
                        <span class="text-muted small">Assign shifts to each day of the week for an entire site. Everyone there follows it automatically.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="step-num step-purple">3</div>
                    <div>
                        <span class="fw-semibold d-block" style="font-size: 0.92rem;">Override Individuals</span>
                        <span class="text-muted small">Need someone on a different schedule? A personal override always beats the site pattern.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-clock-fill" style="color: #0071e3;"></i>
                    <h6 class="fw-semibold mb-0">Shifts</h6>
                    <span class="text-muted small ms-auto">{{ $shifts->count() }} defined</span>
                </div>
                <p class="text-muted small mb-3">The reusable building blocks for every schedule.</p>

                <div class="flex-grow-1 mb-3">
                    @forelse($shifts->take(6) as $shift)
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom hairline">
                            <span class="flex-shrink-0 rounded-circle" style="width: 10px; height: 10px; background-color: {{ $shift->color }};"></span>
                            <span class="fw-medium text-truncate" style="font-size: 0.88rem;">{{ $shift->name }}</span>
                            <span class="text-muted ms-auto flex-shrink-0" style="font-size: 0.75rem;">
                                {{ \Carbon\Carbon::parse($shift->time_in)->format('g:i A') }} &ndash; {{ \Carbon\Carbon::parse($shift->time_out)->format('g:i A') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-clock text-muted fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-3">No shifts yet. Create one to get started.</p>
                            <a href="{{ route('schedules.shifts.index') }}" class="btn btn-sm btn-primary">Create first shift</a>
                        </div>
                    @endforelse
                    @if($shifts->count() > 6)
                        <div class="text-center mt-2"><span class="text-muted small">+ {{ $shifts->count() - 6 }} more</span></div>
                    @endif
                </div>

                <a href="{{ route('schedules.shifts.index') }}" class="btn btn-light w-100 btn-sm"><i class="bi bi-pencil me-1"></i>Manage Shifts</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-calendar-week-fill" style="color: #34c759;"></i>
                    <h6 class="fw-semibold mb-0">Site Weekly Patterns</h6>
                    <span class="text-muted small ms-auto">{{ $sites->count() }} sites</span>
                </div>
                <p class="text-muted small mb-3">Each site gets its own Monday-to-Sunday plan. Staff follow it by default.</p>

                <div class="flex-grow-1 mb-3">
                    @forelse($sites->take(6) as $site)
                        @php
                            $config = $site->scheduleGroup?->schedule_config ?? $site->schedule_config ?? [];
                            $config = is_array($config) ? $config : [];
                            $activeDays = collect($dayNames)->filter(fn ($d) => isset($config[$d]) && $config[$d] !== 'OFF')->count();
                        @endphp
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom hairline">
                            <i class="bi bi-geo-alt text-muted flex-shrink-0" style="font-size: 0.85rem;"></i>
                            <span class="fw-medium text-truncate" style="font-size: 0.88rem;">{{ $site->name }}</span>
                            @if($activeDays > 0)
                                <span class="badge badge-green ms-auto flex-shrink-0">{{ $activeDays }} day{{ $activeDays > 1 ? 's' : '' }}/wk</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary ms-auto flex-shrink-0">No pattern</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-building text-muted fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-3">No sites yet. Add one to build weekly patterns.</p>
                            <a href="{{ route('sites.index') }}" class="btn btn-sm btn-primary">Add a site</a>
                        </div>
                    @endforelse
                    @if($sites->count() > 6)
                        <div class="text-center mt-2"><span class="text-muted small">+ {{ $sites->count() - 6 }} more</span></div>
                    @endif
                </div>

                <a href="{{ route('admin.settings.schedule-groups.index') }}" class="btn btn-light w-100 btn-sm"><i class="bi bi-calendar-range me-1"></i>Manage Weekly Patterns</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-person-fill-gear" style="color: #af52de;"></i>
                    <h6 class="fw-semibold mb-0">Personal Overrides</h6>
                    <span class="text-muted small ms-auto">{{ $directAssignmentCount }} of {{ $employeeCount }}</span>
                </div>
                <p class="text-muted small mb-3">Custom schedules for specific people. These always win over the site pattern.</p>

                <div class="flex-grow-1 mb-3">
                    <div class="p-3 rounded-4 mb-2" style="background: #f5f5f7;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Total staff</span>
                            <span class="fw-semibold">{{ $employeeCount }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-4" style="background: rgba(175,82,222,0.08);">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small" style="color: #8929b8;">On personal overrides</span>
                            <span class="fw-semibold" style="color: #8929b8;">{{ $directAssignmentCount }}</span>
                        </div>
                        @if($employeeCount > 0)
                            <div class="progress mt-2" style="height: 5px; background: rgba(175,82,222,0.15);">
                                <div class="progress-bar" role="progressbar" style="width: {{ round($directAssignmentCount / $employeeCount * 100) }}%; background-color: #af52de;"></div>
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.settings.individual-schedules.index') }}" class="btn btn-light w-100 btn-sm"><i class="bi bi-person-gear me-1"></i>Manage Personal Schedules</a>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-pills mb-3 gap-2" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 fw-medium" data-bs-toggle="pill" data-bs-target="#sites-pane" type="button" role="tab">Sites overview</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 fw-medium" data-bs-toggle="pill" data-bs-target="#overrides-pane" type="button" role="tab">Personal overrides ({{ $overriddenEmployees->count() }})</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="sites-pane" role="tabpanel">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Site</th>
                            <th>Weekly Pattern</th>
                            <th class="text-center">Work Week</th>
                            <th class="text-center">Staff</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sites as $site)
                            @php
                                $config = $site->scheduleGroup?->schedule_config ?? $site->schedule_config ?? [];
                                $config = is_array($config) ? $config : [];
                                $activeDays = collect($dayNames)->filter(fn ($d) => isset($config[$d]) && $config[$d] !== 'OFF')->count();
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold" style="font-size: 0.92rem;">{{ $site->name }}</div>
                                    <div class="text-muted" style="font-size: 0.76rem;">{{ $site->location ?? 'No location set' }}</div>
                                </td>
                                <td>
                                    @if($site->scheduleGroup)
                                        <span class="fw-medium" style="font-size: 0.88rem;">{{ $site->scheduleGroup->name }}</span>
                                    @elseif($activeDays > 0)
                                        <span class="fw-medium" style="font-size: 0.88rem;">Custom per-day plan</span>
                                    @else
                                        <span class="text-muted" style="font-size: 0.85rem;">Not set up yet</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1" dir="ltr">
                                        @foreach($dayLetters as $i => $letter)
                                            @php
                                                $val = $config[$dayNames[$i]] ?? null;
                                                if ($val === 'OFF') {
                                                    $chipState = 'off';
                                                    $chipShift = null;
                                                } else {
                                                    if (is_array($val)) {
                                                        $val = $val['id'] ?? null;
                                                    }
                                                    $chipShift = is_numeric($val) ? $shiftById->get((int) $val) : null;
                                                    $chipState = $chipShift ? 'on' : 'none';
                                                }
                                            @endphp
                                            @if($chipState === 'on')
                                                <span class="week-chip week-on" title="{{ $dayNames[$i] }}: {{ $chipShift->name }} ({{ \Carbon\Carbon::parse($chipShift->time_in)->format('g:i A') }} - {{ \Carbon\Carbon::parse($chipShift->time_out)->format('g:i A') }})">{{ $letter }}</span>
                                            @elseif($chipState === 'off')
                                                <span class="week-chip week-off" title="{{ $dayNames[$i] }}: Rest day">{{ $letter }}</span>
                                            @else
                                                <span class="week-chip" title="{{ $dayNames[$i] }}: Not scheduled">{{ $letter }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-center fw-semibold">{{ $site->employees_count }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.settings.sites.show', $site->id) }}" class="btn btn-sm btn-light">
                                        {{ ($site->scheduleGroup || $activeDays > 0) ? 'Edit pattern' : 'Set up pattern' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-diagram-3 text-muted fs-2 d-block mb-2"></i>
                                    <p class="text-muted small mb-3">No sites yet. Sites group employees so you can schedule everyone at once.</p>
                                    <a href="{{ route('sites.index') }}" class="btn btn-sm btn-primary">Add your first site</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="overrides-pane" role="tabpanel">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Site</th>
                            <th>Schedule</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overriddenEmployees as $emp)
                            @php
                                $override = $emp->schedules->firstWhere('is_template', false);
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-initial">{{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 0.92rem;">{{ $emp->full_name }}</div>
                                            <div class="text-muted" style="font-size: 0.76rem;">{{ $emp->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 0.88rem;">{{ $emp->site->name ?? 'Unassigned' }}</td>
                                <td>
                                    @if($override && $override->shift)
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <span class="rounded-circle flex-shrink-0" style="width: 8px; height: 8px; background-color: {{ $override->shift->color }};"></span>
                                            <span class="fw-medium" style="font-size: 0.88rem;">{{ $override->shift->name }}</span>
                                        </span>
                                        <div class="text-muted" style="font-size: 0.76rem;">
                                            {{ \Carbon\Carbon::parse($override->time_in ?? $override->shift->time_in)->format('g:i A') }} &ndash;
                                            {{ \Carbon\Carbon::parse($override->time_out ?? $override->shift->time_out)->format('g:i A') }}
                                        </div>
                                    @elseif($override)
                                        <span class="fw-medium" style="font-size: 0.88rem;">Custom times</span>
                                        <div class="text-muted" style="font-size: 0.76rem;">
                                            {{ \Carbon\Carbon::parse($override->time_in)->format('g:i A') }} &ndash; {{ \Carbon\Carbon::parse($override->time_out)->format('g:i A') }}
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.85rem;">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.settings.individual-schedules.index') }}" class="btn btn-sm btn-light">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="bi bi-person-check text-success fs-2 d-block mb-2"></i>
                                    <p class="text-muted small mb-0">Everyone is following their site's weekly pattern. No personal overrides needed.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .step-num {
        width: 30px; height: 30px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 0.82rem; font-weight: 700;
        background: rgba(0,113,227,0.12); color: #0071e3;
    }
    .step-num.step-green { background: rgba(52,199,89,0.14); color: #248a3d; }
    .step-num.step-purple { background: rgba(175,82,222,0.14); color: #8929b8; }
    .hairline { border-color: rgba(0,0,0,0.06) !important; }
    .week-chip {
        width: 24px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; font-size: 0.68rem; font-weight: 600;
        background: rgba(0,0,0,0.05); color: #aeaeb2;
        cursor: default;
    }
    .week-chip.week-on { background: #0071e3; color: #fff; }
    .week-chip.week-off { background: rgba(0,0,0,0.08); color: #86868b; }
    .avatar-initial {
        width: 34px; height: 34px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 0.72rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #0071e3, #0058b0);
    }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
</style>
@endsection
