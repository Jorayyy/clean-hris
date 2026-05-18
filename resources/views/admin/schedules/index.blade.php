@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4" style="max-width: 1400px; margin: auto;">
    <div class="mb-5 text-center">
        <h1 class="fw-bold mb-2">Schedule Management Hub</h1>
        <p class="text-muted lead">Follow the steps below to set up and assign schedules.</p>
    </div>

    <!-- STEP-BY-STEP WORKFLOW -->
    <div class="row g-4">
        <!-- STEP 1: DEFINE SHIFTS -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-primary border-5">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                        <h4 class="fw-bold mb-0">Define Shifts</h4>
                    </div>
                    <p class="text-muted small mb-4">Create reusable shift times (e.g. "Morning 8am-5pm" or "Night 10pm-7am"). This is your "menu" of times.</p>
                    
                    <div class="list-group list-group-flush mb-4 scrollbar-thin" style="max-height: 300px; overflow-y: auto;">
                        @forelse($templates as $t)
                        <div class="list-group-item px-0 border-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $t->name }}</div>
                                <small class="text-primary">{{ \Carbon\Carbon::parse($t->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($t->time_out)->format('h:i A') }}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm text-muted" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item" href="{{ route('schedules.edit', $t->id) }}">Edit Name/Time</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('schedules.destroy', $t->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Delete this shift definition?')">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @empty
                        <div class="py-3 text-center text-muted italic small">No shifts defined yet.</div>
                        @endforelse
                    </div>

                    <a href="{{ route('schedules.create', ['template' => 1]) }}" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i> Create New Shift Time
                    </a>
                </div>
            </div>
        </div>

        <!-- STEP 2: PLOT BY ACCOUNT -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-success border-5">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                        <h4 class="fw-bold mb-0">Plot by Account</h4>
                    </div>
                    <p class="text-muted small mb-4">Pick an account (Site) and decide which shifts apply to them for each day of the week.</p>

                    <div class="list-group list-group-flush mb-4 scrollbar-thin" style="max-height: 300px; overflow-y: auto;">
                        @forelse($sites as $site)
                        <div class="list-group-item px-0 border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark">{{ $site->name }}</div>
                                    @if($site->scheduleGroup)
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.7rem;">Group: {{ $site->scheduleGroup->name }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Manual Plotting</span>
                                    @endif
                                </div>
                                <a href="{{ route('admin.settings.sites.show', $site->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">Plot</a>
                            </div>
                        </div>
                        @empty
                        <div class="py-3 text-center text-muted small">No accounts found. Add sites first.</div>
                        @endforelse
                    </div>

                    <a href="{{ route('admin.settings.schedule-groups.index') }}" class="btn btn-outline-success w-100 rounded-pill fw-bold py-2">
                        <i class="bi bi-calendar-range me-2"></i> Manage Weekly Groups
                    </a>
                </div>
            </div>
        </div>

        <!-- STEP 3: ASSIGN EXCEPTIONS -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-info border-5">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                        <h4 class="fw-bold mb-0">Direct Assignments</h4>
                    </div>
                    <p class="text-muted small mb-4">Assign specific shifts directly to one employee or a special payroll category.</p>

                    <div class="list-group list-group-flush mb-4 scrollbar-thin" style="max-height: 300px; overflow-y: auto;">
                        @forelse($schedules as $s)
                        <div class="list-group-item px-0 border-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold small">{{ $s->employee ? $s->employee->full_name : ($s->payrollGroup ? $s->payrollGroup->name . ' Group' : 'Special Assigned') }}</div>
                                <small class="text-info">{{ \Carbon\Carbon::parse($s->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($s->time_out)->format('h:i A') }}</small>
                            </div>
                            <form action="{{ route('schedules.destroy', $s->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm text-danger" onclick="return confirm('Remove this assignment?')"><i class="bi bi-x-circle"></i></button>
                            </form>
                        </div>
                        @empty
                        <div class="py-3 text-center text-muted small">No direct assignments.</div>
                        @endforelse
                    </div>

                    <a href="{{ route('schedules.create') }}" class="btn btn-info text-white w-100 rounded-pill fw-bold py-2 shadow-sm">
                        <i class="bi bi-person-plus me-2"></i> Assign Individual
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK LEGEND -->
    <div class="mt-5 p-4 bg-light rounded-4 border">
        <h6 class="fw-bold mb-3">How it works:</h6>
        <div class="row g-3 small">
            <div class="col-md-4">
                <i class="bi bi-info-circle me-2 text-primary"></i> <strong>Priority 1:</strong> Direct Assignments (Step 3) always override everything else.
            </div>
            <div class="col-md-4">
                <i class="bi bi-info-circle me-2 text-success"></i> <strong>Priority 2:</strong> Account Plotting (Step 2) applies to everyone in that site.
            </div>
            <div class="col-md-4">
                <i class="bi bi-info-circle me-2 text-muted"></i> <strong>Priority 3:</strong> If neither is set, system tags the user as no-schedule.
            </div>
        </div>
    </div>
</div>
@endsection
