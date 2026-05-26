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
                            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2"></i> Use Schedule Group</h5>
                            <p class="text-muted small">Select a pre-defined schedule group for this account. If you select a group, the manual plotting below will be ignored.</p>
                            <select name="schedule_group_id" class="form-select form-select-lg rounded-pill border-0 shadow-sm px-4">
                                <option value="">-- No Group (Use Manual Plotting Below) --</option>
                                @foreach($scheduleGroups as $group)
                                    <option value="{{ $group->id }}" {{ $site->schedule_group_id == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($site->scheduleGroup)
                                <div class="mt-3 p-3 bg-white rounded-3 shadow-sm border-start border-primary border-4">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-primary">Active Group Schedule:</span>
                                        <a href="{{ route('admin.settings.schedule-groups.edit', $site->scheduleGroup->id) }}" class="small text-decoration-none">View/Edit Group</a>
                                    </div>
                                    <div class="mt-2 row">
                                        @php 
                                            $config = $site->scheduleGroup->schedule_config ?? [];
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        @endphp
                                        @foreach($days as $day)
                                            @php $dayConfig = $config[$day] ?? null; @endphp
                                            <div class="col-md-3 mb-2 small">
                                                <strong>{{ substr($day, 0, 3) }}:</strong> 
                                                @if(isset($dayConfig['is_rest_day']) || (is_string($dayConfig) && $dayConfig === 'OFF'))
                                                    <span class="text-danger">REST</span>
                                                @elseif(isset($dayConfig['id']) || is_numeric($dayConfig))
                                                    @php 
                                                        $shiftId = $dayConfig['id'] ?? $dayConfig;
                                                        $shift = \App\Models\Shift::find($shiftId);
                                                    @endphp
                                                    @if($shift)
                                                        {{ \Carbon\Carbon::parse($shift->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->time_out)->format('h:i A') }}
                                                    @else
                                                        <span class="text-muted small">Not Set</span>
                                                    @endif
                                                @elseif(isset($dayConfig['start']))
                                                    {{ \Carbon\Carbon::parse($dayConfig['start'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($dayConfig['end'])->format('h:i A') }}
                                                @else
                                                    <span class="text-muted small">Not Set</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
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
                                    {{ $sched->name }} ({{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }})
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
