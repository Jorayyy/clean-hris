@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow rounded border-0">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Edit Schedule</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Schedule Name (Optional)</label>
                        <input type="text" name="name" class="form-control" value="{{ $schedule->name }}" placeholder="e.g. Morning Shift">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Target Type</label>
                        <div class="d-flex gap-4 p-3 bg-light rounded shadow-sm">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="target_type" id="typeGroup" value="group" {{ $schedule->payroll_group_id ? 'checked' : '' }} onclick="toggleFields()">
                                <label class="form-check-label" for="typeGroup">Payroll Group</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="target_type" id="typeIndividual" value="individual" {{ $schedule->employee_id ? 'checked' : '' }} onclick="toggleFields()">
                                <label class="form-check-label" for="typeIndividual">Individual Employee</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 {{ $schedule->employee_id ? 'd-none' : '' }}" id="groupField">
                        <label class="form-label fw-semibold">Select Payroll Group</label>
                        <select name="payroll_group_id" class="form-select">
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ $schedule->payroll_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 {{ $schedule->payroll_group_id ? 'd-none' : '' }}" id="individualField">
                        <label class="form-label fw-semibold">Select Employee</label>
                        <select name="employee_id" class="form-select">
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" {{ $schedule->employee_id == $e->id ? 'selected' : '' }}>{{ $e->full_name }} ({{ $e->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time In</label>
                            <input type="time" name="time_in" class="form-control" required value="{{ $schedule->time_in }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time Out</label>
                            <input type="time" name="time_out" class="form-control" required value="{{ $schedule->time_out }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block">Schedule Days</label>
                        <div class="d-flex flex-wrap gap-3">
                        @php $schedDays = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true) ?? []; @endphp
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" 
                                    {{ in_array($day, $schedDays) ? 'checked' : '' }} id="check{{ $day }}">
                                <label class="form-check-label" for="check{{ $day }}">{{ $day }}</label>
                            </div>
                        @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Update Schedule</button>
                        <a href="{{ route('schedules.index') }}" class="btn btn-light px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFields() {
    const isGroup = document.getElementById('typeGroup').checked;
    document.getElementById('groupField').classList.toggle('d-none', !isGroup);
    document.getElementById('individualField').classList.toggle('d-none', isGroup);
}
</script>
@endsection