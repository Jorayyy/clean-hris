@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-5 text-center">
        <h2 class="fw-bold text-dark">Workforce Scheduling Hub</h2>
        <p class="text-muted small italic">Set up standard work hours (Blueprints) and handle daily staffing assignments.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Step 1: Define Shifts -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all overflow-hidden">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-weight: bold;">1</div>
                        <h5 class="fw-bold mb-0">Shift Menu</h5>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <p class="text-muted small mb-4">Define your standard work times here (e.g. "Day Shift", "Night Shift"). These will be reused across the system.</p>
                    
                    <div class="mb-4 bg-light p-3 rounded-4" style="min-height: 180px;">
                        @if($shifts->count() > 0)
                            <label class="small fw-bold text-muted text-uppercase mb-2 d-block" style="font-size: 0.65rem;">AVAILABLE TIMES ({{ $shifts->count() }})</label>
                            @foreach($shifts->take(5) as $shift)
                                <div class="d-flex align-items-center mb-2 px-2 py-1 bg-white rounded-3 shadow-sm">
                                    <div class="rounded-circle me-2" style="width: 10px; height: 10px; background-color: {{ $shift->color }}"></div>
                                    <span class="small fw-medium text-dark flex-grow-1 text-truncate">{{ $shift->name }}</span>
                                    <span class="small text-muted" style="font-size: 0.7rem;">{{ date('h:i A', strtotime($shift->time_in)) }}</span>
                                </div>
                            @endforeach
                            @if($shifts->count() > 5)
                                <div class="text-center mt-2">
                                    <span class="small text-muted">+ {{ $shifts->count() - 5 }} more shifts</span>
                                </div>
                            @endif
                        @else
                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-4">
                                <i class="bi bi-clock-history text-muted mb-2 fs-4"></i>
                                <div class="small text-muted italic">No shifts created yet.</div>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('schedules.shifts.index') }}" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                        <i class="bi bi-gear-fill me-2"></i> Manage Shift Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Step 2: Plot by Account -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all overflow-hidden">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-weight: bold;">2</div>
                        <h5 class="fw-bold mb-0">Master Blueprints</h5>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <p class="text-muted small mb-4">Assign standard weekly patterns to whole Sites. This becomes the "Auto-Schedule" for everyone in that group.</p>

                    <div class="mb-4 bg-light p-3 rounded-4" style="min-height: 180px;">
                        @if($sites->count() > 0)
                            <label class="small fw-bold text-muted text-uppercase mb-2 d-block" style="font-size: 0.65rem;">SITES/ACCOUNTS ({{ $sites->count() }})</label>
                            @foreach($sites->take(5) as $site)
                                <div class="d-flex align-items-center mb-2 px-2 py-1 bg-white rounded-3 shadow-sm">
                                    <i class="bi bi-geo-alt me-2 text-success" style="font-size: 0.8rem;"></i>
                                    <span class="small fw-medium text-dark flex-grow-1 text-truncate">{{ $site->name }}</span>
                                    @if($site->scheduleGroup)
                                        <i class="bi bi-check-circle-fill text-success ms-2" style="font-size: 0.7rem;" title="Has Schedule Pattern"></i>
                                    @endif
                                </div>
                            @endforeach
                            @if($sites->count() > 5)
                                <div class="text-center mt-2">
                                    <span class="small text-muted">+ {{ $sites->count() - 5 }} more accounts</span>
                                </div>
                            @endif
                        @else
                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-4">
                                <i class="bi bi-building text-muted mb-2 fs-4"></i>
                                <div class="small text-muted italic">No sites found.</div>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('admin.settings.schedule-groups.index') }}" class="btn btn-outline-success w-100 py-2 rounded-3 fw-bold">
                        <i class="bi bi-calendar-range me-2"></i> Manage Blueprints
                    </a>
                </div>
            </div>
        </div>

        <!-- Step 3: Direct Assignments -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all overflow-hidden">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-weight: bold;">3</div>
                        <h5 class="fw-bold mb-0">Staffing Overrides</h5>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <p class="text-muted small mb-4">Manually assign shifts to specific people. Use this for duty rotations or temporary schedule changes.</p>

                    <div class="mb-4 bg-light p-3 rounded-4" style="min-height: 180px;">
                        <label class="small fw-bold text-muted text-uppercase mb-3 d-block" style="font-size: 0.65rem;">MANUAL ASSIGNMENTS</label>
                        <div class="d-flex justify-content-between align-items-center mb-3 px-3 py-2 bg-white rounded-3 shadow-sm">
                            <span class="small text-muted">Total Manpower</span>
                            <span class="small fw-bold text-dark">{{ $employeeCount }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 px-3 py-2 bg-white rounded-3 shadow-sm border-start border-info border-4">
                            <span class="small text-muted">Manual Overrides</span>
                            <span class="small fw-bold text-info">{{ $directAssignmentCount }}</span>
                        </div>
                        <div class="small text-muted px-2 italic mt-auto" style="font-size: 0.7rem;">
                            <i class="bi bi-info-circle me-1"></i> These always "win" over Blueprints.
                        </div>
                    </div>

                    <a href="{{ route('schedules.create') }}" class="btn btn-info text-white w-100 py-2 rounded-3 fw-bold shadow-sm">
                        <i class="bi bi-person-fill-add me-2"></i> Create Manual Override
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- How it Works section -->
    <div class="mt-5 bg-white p-4 rounded-4 shadow-sm border-0">
        <div class="d-flex align-items-center mb-4">
            <h6 class="fw-bold text-dark mb-0">Hierarchy & Logic Flow</h6>
            <div class="ms-3 flex-grow-1 border-bottom border-light"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-start">
                    <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-3">
                        <i class="bi bi-lightning-fill"></i>
                    </div>
                    <div>
                        <span class="small fw-bold d-block text-dark mb-1">Priority 1: Direct Assignments</span>
                        <p class="small text-muted mb-0">Individual assignments created in **Step 3** always take top priority. Use these for temporary changes or special duties.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-start">
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-3">
                        <i class="bi bi-layers-fill"></i>
                    </div>
                    <div>
                        <span class="small fw-bold d-block text-dark mb-1">Priority 2: Account Plotting</span>
                        <p class="small text-muted mb-0">Weekly patterns in **Step 2** apply to all employees in that Site. If an employee has no direct assignment, they follow this.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-start">
                    <div class="bg-secondary bg-opacity-10 text-secondary p-2 rounded-3 me-3">
                        <i class="bi bi-shield-slash-fill"></i>
                    </div>
                    <div>
                        <span class="small fw-bold d-block text-muted mb-1">Priority 3: No-Schedule</span>
                        <p class="small text-muted mb-0">If neither 1 nor 2 is present, the employee is "off-schedule". They won't be able to clock in via the portal.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift:hover {
    transform: translateY(-8px);
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
@endsection
