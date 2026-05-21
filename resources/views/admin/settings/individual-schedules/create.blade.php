@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('admin.settings.individual-schedules.index') }}" class="text-decoration-none text-muted small uppercase fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Individual List
        </a>
        <h4 class="fw-bold mt-2 text-dark">Create Individual Schedule</h4>
        <p class="text-muted small">Select an employee and plot their unique 7-day pattern.</p>
    </div>

    <form action="{{ route('admin.settings.individual-schedules.store') }}" method="POST">
        @csrf
        
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 border-bottom bg-light bg-opacity-50">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">1. Choose Employee</label>
                        <select name="employee_id" class="form-select border-primary" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 pt-md-4">
                        <div class="alert alert-warning border-0 small mb-0 mt-2">
                             Note: This schedule will take priority over any Site/Group schedules.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <label class="form-label fw-bold small text-muted text-uppercase mb-3">2. Plot 7-Day Pattern</label>
                
                <div class="row row-cols-1 row-cols-md-7 g-2">
                    @php 
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
                    @endphp
                    
                    @foreach($days as $index => $day)
                        <div class="col">
                            <div class="card h-100 border rounded-4 text-center p-2 shadow-none bg-white">
                                <p class="fw-bold mb-1 text-primary small">{{ substr($day, 0, 3) }}</p>
                                
                                <div class="mb-2">
                                    <select name="day_{{ $index }}_shift_id" class="form-select form-select-sm border-0 bg-light text-center py-1" id="shift_{{ $index }}" style="font-size: 0.75rem;">
                                        <option value="">-- Shift --</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input me-1" type="checkbox" name="day_{{ $index }}_rest_day" value="1" id="rest_{{ $index }}" onchange="toggleRest('{{ $index }}')" style="transform: scale(0.8);">
                                    <label class="form-check-label text-muted" for="rest_{{ $index }}" style="font-size: 0.65rem;">Rest Day</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="card-footer bg-white border-0 p-4 pt-0 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5">
                    Save Individual Schedule
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
