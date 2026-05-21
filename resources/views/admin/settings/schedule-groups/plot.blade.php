@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border shadow-sm">
                <!-- Header -->
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        Mancao Electronic Connect Business Solutions OPC 
                        <span class="text-muted fw-normal small">(plotting schedule - {{ $scheduleGroup->name }})</span>
                    </h6>
                    <a href="{{ route('admin.settings.schedule-groups.index') }}" class="text-secondary fs-4">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.schedule-groups.update-plot', $scheduleGroup->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="bg-light">
                                    <tr class="small text-dark fw-bold">
                                        <th style="width: 15%;">DAY</th>
                                        <th>SELECT SCHEDULE (SHIFT)</th>
                                        <th style="width: 20%;">REST DAY?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $config = $scheduleGroup->schedule_config ?? []; @endphp
                                    @foreach($days as $day)
                                    <tr>
                                        <td class="fw-bold small">{{ strtoupper($day) }}</td>
                                        <td>
                                            <select name="schedule[{{ $day }}][id]" class="form-select form-select-sm rounded-0 border-0 text-center">
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
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input border-secondary" type="checkbox" name="schedule[{{ $day }}][is_rest_day]" value="1" {{ (isset($config[$day]['is_rest_day']) || ($config[$day] ?? '') === 'OFF') ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-calendar-check-fill"></i> Save Schedule Plotting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-bordered { border: 1px solid #dee2e6 !important; }
.form-select:focus { box-shadow: none; }
.card-header h6 { font-size: 0.95rem; }
</style>
@endsection