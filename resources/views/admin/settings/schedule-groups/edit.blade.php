@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.schedule-groups.index') }}">Schedule Groups</a></li>
                <li class="breadcrumb-item active">Edit Group</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Edit Schedule Group</h2>
        <p class="text-muted">Modify the template schedule for "{{ $scheduleGroup->name }}".</p>
        
        <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center mb-0" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <strong class="d-block">Base Blueprint Mode</strong>
                <span class="small">This defines the <strong>standard weekly pattern</strong> for this group. Employees assigned here follow this by default unless an active override is assigned.</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.settings.schedule-groups.update', $scheduleGroup->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Group Details</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Group Name</label>
                            <input type="text" name="name" class="form-control rounded-pill @error('name') is-invalid @enderror" value="{{ old('name', $scheduleGroup->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control rounded-4 @error('description') is-invalid @enderror" rows="3">{{ old('description', $scheduleGroup->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 mt-3">
                            Update Schedule Group
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Daily Schedule Plotting</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle" style="min-width: 600px;">
                                <thead>
                                    <tr class="text-muted small uppercase">
                                        <th style="width: 150px">Day</th>
                                        <th>Select Schedule Template</th>
                                        <th class="text-center">Is REST Day?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $config = $scheduleGroup->schedule_config ?? []; @endphp
                                    @foreach($days as $day)
                                    <tr class="border-bottom">
                                        <td><span class="fw-bold">{{ $day }}</span></td>
                                        <td>
                                            <select name="schedule[{{ $day }}][id]" class="form-select rounded-pill">
                                                <option value="">-- No Schedule --</option>
                                                @foreach($schedules as $sched)
                                                    @php 
                                                        $selected = false;
                                                        if (isset($config[$day]['id']) && $config[$day]['id'] == $sched->id) {
                                                            $selected = true;
                                                        } elseif (is_string($config[$day] ?? null) && $config[$day] == $sched->id) {
                                                            $selected = true;
                                                        }
                                                    @endphp
                                                    <option value="{{ $sched->id }}" {{ $selected ? 'selected' : '' }}>
                                                        {{ $sched->name }} ({{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="schedule[{{ $day }}][is_rest_day]" value="1" {{ (isset($config[$day]['is_rest_day']) || ($config[$day] ?? '') === 'OFF') ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
