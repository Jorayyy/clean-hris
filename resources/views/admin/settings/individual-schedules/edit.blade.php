@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('admin.settings.individual-schedules.index') }}" class="text-decoration-none text-muted small uppercase fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Individual List
        </a>
        <h4 class="fw-bold mt-2 text-dark">Modify Individual Schedule</h4>
        <p class="text-muted small">Update the personalized 7-day pattern for <strong>{{ $employee->full_name }}</strong>.</p>
    </div>

    <form action="{{ route('admin.settings.individual-schedules.store') }}" method="POST">
        @csrf
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 border-bottom bg-light bg-opacity-50">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold;">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $employee->full_name }}</h5>
                        <span class="small text-muted">{{ $employee->employee_id }} — {{ $employee->site->name ?? 'No Site' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <label class="form-label fw-bold small text-muted text-uppercase mb-3">Plot 7-Day Pattern</label>
                
                <div class="row row-cols-1 row-cols-md-7 g-3">
                    @php 
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
                    @endphp
                    
                    @foreach($days as $index => $day)
                        @php
                            $current = $currentSchedules[$index] ?? null;
                            $isRest = $current ? $current->is_rest_day : false;
                        @endphp
                        <div class="col">
                            <div class="card h-100 border rounded-4 text-center p-3 shadow-none">
                                <p class="fw-bold mb-2 text-primary">{{ substr($day, 0, 3) }}</p>
                                
                                <div class="mb-3">
                                    <select name="day_{{ $index }}_shift_id" class="form-select form-select-sm border-0 bg-light text-center" id="shift_{{ $index }}" @if($isRest) disabled @endif>
                                        <option value="">-- Shift --</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" @if($current && $current->shift_id == $shift->id) selected @endif>
                                                {{ $shift->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input me-2" type="checkbox" name="day_{{ $index }}_rest_day" value="1" id="rest_{{ $index }}" onchange="toggleRest('{{ $index }}')" @if($isRest) checked @endif>
                                    <label class="form-check-label small text-muted" for="rest_{{ $index }}">Rest Day</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="card-footer bg-white border-0 p-4 pt-0 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5">
                    Update Individual Schedule
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleRest(day) {
    const shiftSelect = document.getElementById('shift_' + day);
    const restCheck = document.getElementById('rest_' + day);
    
    if (restCheck.checked) {
        shiftSelect.value = "";
        shiftSelect.disabled = true;
        shiftSelect.classList.add('bg-secondary', 'bg-opacity-10');
    } else {
        shiftSelect.disabled = false;
        shiftSelect.classList.remove('bg-secondary', 'bg-opacity-10');
    }
}
</script>
@endsection
