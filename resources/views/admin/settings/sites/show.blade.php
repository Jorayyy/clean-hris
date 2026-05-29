@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('schedules.index') }}" class="btn btn-sm text-muted p-0 mb-3"><i class="bi bi-arrow-left me-1"></i> Back to Hub</a>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-0">Configure Account: <span class="text-primary">{{ $site->name }}</span></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.sites.index') }}">Accounts</a></li>
                    <li class="breadcrumb-item active">Fixed Schedule</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-primary text-white py-3 px-4">
            <h5 class="mb-0 fw-bold">Group: {{ strtoupper($site->name) }} SCHEDULE PLOTTING</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.settings.sites.update-schedule', $site->id) }}" method="POST">
                @csrf
                
                <div class="row mb-5">
                    <div class="col-md-12">
                        <div class="bg-light p-4 rounded-4 border">
                            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2"></i> Active Blueprints</h5>
                            <p class="text-muted small">These Blueprints are currently assigned to this site. Employees assigned to this site will follow these patterns unless they have an individual override.</p>
                            
                            <div class="row g-3">
                                @forelse($site->scheduleGroups as $group)
                                    <div class="col-md-6">
                                        <div class="bg-white p-3 rounded-3 shadow-sm border-start border-primary border-4 h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-primary">{{ $group->name }}</span>
                                                <a href="{{ route('admin.settings.schedule-groups.plot', $group->id) }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 small">Edit Pattern</a>
                                            </div>
                                            <div class="very-small text-muted mb-2">Weekly Pattern:</div>
                                            <div class="row g-1">
                                                @php 
                                                    $config = $group->schedule_config ?? [];
                                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                @endphp
                                                @foreach($days as $day)
                                                    @php $dayConfig = $config[$day] ?? null; @endphp
                                                    <div class="col-6" style="font-size: 0.65rem;">
                                                        <span class="fw-bold">{{ substr($day, 0, 3) }}:</span> 
                                                        @if(isset($dayConfig['is_rest_day']) || (is_string($dayConfig) && $dayConfig === 'OFF'))
                                                            <span class="text-danger">OFF</span>
                                                        @elseif(isset($dayConfig['id']) || is_numeric($dayConfig))
                                                            @php 
                                                                $shiftId = $dayConfig['id'] ?? $dayConfig;
                                                                $shift = \App\Models\Shift::find($shiftId);
                                                            @endphp
                                                            {{ $shift ? \Carbon\Carbon::parse($shift->time_in)->format('h:i') : '?' }}
                                                        @else
                                                            <span class="text-muted italic">None</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-3 bg-white rounded-3 border dashed">
                                        <span class="text-muted small italic">No blueprints assigned to this site.</span>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.settings.schedule-groups.create') }}?site_id={{ $site->id }}" class="btn btn-sm btn-primary rounded-pill">Create First Blueprint</a>
                                        </div>
                                    </div>
                                @endforelse
                                <div class="col-12 text-center mt-3">
                                    <a href="{{ route('admin.settings.schedule-groups.create') }}?site_id={{ $site->id }}" class="small text-decoration-none">
                                        <i class="bi bi-plus-circle me-1"></i> Add Another Blueprint for this Site
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5" id="manual-plotting-section" style="{{ $site->schedule_group_id ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                    <div class="col-12">
                        <h5 class="fw-bold"><i class="bi bi-clock me-2"></i> Manual Schedule Plotting (Per Day)</h5>
                    </div>
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <div class="col-md-4">
                        <label class="form-label fw-bold">{{ $day }}</label>
                        <select name="schedule[{{ $day }}]" class="form-select bg-light border-0 py-2">
                            <option value="">Select Schedule</option>
                            @foreach($templates as $sched)
                                <option value="{{ $sched->id }}" {{ (isset($site->schedule_config[$day]) && $site->schedule_config[$day] == $sched->id) ? 'selected' : '' }}>
                                    {{ $sched->name }} ({{ \Carbon\Carbon::parse($sched->time_in)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->time_out)->format('H:i') }})
                                </option>
                            @endforeach
                            <option value="OFF" {{ (isset($site->schedule_config[$day]) && $site->schedule_config[$day] == 'OFF') ? 'selected' : '' }}>REST DAY / OFF</option>
                        </select>
                    </div>
                    @endforeach
                </div>

                <div class="border-top pt-4">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_special_1_hour" id="special1" {{ $site->is_special_1_hour ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="special1">
                            Special 1 Hour Schedule Only
                        </label>
                        <ul class="text-muted small mt-2">
                            <li>Employees who render duty of 1 hour per day but are being paid for a whole day duty</li>
                            <li>Must render duty within the plotted schedule</li>
                            <li>Duty outside the plotted schedule, employee will be tagged as absent</li>
                            <li>If undertime, employee will be tagged as absent</li>
                            <li>No overtime computation</li>
                        </ul>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_present_policy" id="policyIn" {{ $site->is_present_policy ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="policyIn">
                            Special Case As Long as With In & Out Present Policy
                        </label>
                        <ul class="text-muted small mt-2">
                            <li>Employees who had in & out regardless of total hrs & is being paid for a whole day duty</li>
                            <li>No overtime computation</li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-info border-0 rounded-3 mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Note:</strong> This plotting will take effect to all members of the site/account selected.
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3">
                    <i class="bi bi-save me-2"></i> SAVE SCHEDULE PLOTTING
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('select[name="schedule_group_id"]').addEventListener('change', function() {
    const section = document.getElementById('manual-plotting-section');
    if (this.value) {
        section.style.opacity = '0.5';
        section.style.pointerEvents = 'none';
    } else {
        section.style.opacity = '1';
        section.style.pointerEvents = 'auto';
    }
});
</script>
@endpush
