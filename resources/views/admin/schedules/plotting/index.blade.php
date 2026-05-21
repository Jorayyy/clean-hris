@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Visual Scheduler</h4>
            <p class="text-muted small mb-0">Drag and drop or click to assign shifts to employees for the current week.</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select border-0 shadow-sm" style="width: 200px;" id="siteFilter">
                <option value="">All Accounts</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary rounded-3 px-4 shadow-sm fw-bold" onclick="saveBulkChanges()">
                Save All Changes
            </button>
        </div>
    </div>

    <!-- Shift Palette -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0">Shift Palette <small class="fw-normal text-muted ms-2">(Click a shift to select, then click on cells below)</small></h6>
        </div>
        <div class="card-body bg-light bg-opacity-50 py-3">
            <div class="d-flex gap-3 flex-wrap">
                @foreach($shifts as $shift)
                    <div class="shift-palette-item shadow-sm p-2 rounded-3 bg-white border d-flex align-items-center" 
                         onclick="selectShift(this, {{ $shift->id }}, '{{ $shift->color }}', '{{ $shift->name }}')" 
                         style="cursor: pointer; transition: all 0.2s;">
                        <div class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: {{ $shift->color }}"></div>
                        <span class="small fw-bold">{{ $shift->name }}</span>
                    </div>
                @endforeach
                <div class="shift-palette-item shadow-sm p-2 rounded-3 bg-white border d-flex align-items-center" 
                     onclick="selectShift(this, 'OFF', '#6c757d', 'DAY OFF')" 
                     style="cursor: pointer; transition: all 0.2s;">
                    <i class="bi bi-x-circle-fill text-secondary me-2"></i>
                    <span class="small fw-bold">DAY OFF</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduling Grid -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center" id="schedulerTable">
                <thead class="bg-white">
                    <tr>
                        <th class="text-start ps-4 py-3 bg-light" style="width: 250px;">Employee</th>
                        @php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; @endphp
                        @foreach($days as $day)
                            <th class="py-3 bg-light">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr class="employee-row" data-site-id="{{ $employee->site_id }}">
                        <td class="text-start ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $employee->full_name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $employee->site->name ?? 'No Account' }}</div>
                                </div>
                            </div>
                        </td>
                        @foreach($days as $day)
                            @php 
                                $sched = $employee->schedules->where('is_template', false)->first();
                                $isActiveDay = $sched && in_array($day, (array)$sched->days);
                                $currentShift = $isActiveDay ? $shifts->find($sched->shift_id) : null;
                            @endphp
                            <td class="p-2 schedule-cell position-relative" 
                                data-employee-id="{{ $employee->id }}" 
                                data-day="{{ $day }}"
                                onclick="applyShift(this)">
                                @if($currentShift)
                                    <div class="shift-tag rounded-3 shadow-sm p-2 text-white small fw-bold" style="background-color: {{ $currentShift->color }}">
                                        {{ $currentShift->name }}
                                        <div style="font-size: 0.6rem; font-weight: normal;">{{ date('H:i', strtotime($currentShift->time_in)) }} - {{ date('H:i', strtotime($currentShift->time_out)) }}</div>
                                    </div>
                                @else
                                    <div class="empty-cell rounded-3 py-3 border border-dashed text-muted small italic" style="font-size: 0.65rem;">
                                        OFF
                                    </div>
                                @endif
                                <div class="cell-overlay"></div>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.shift-palette-item.active {
    box-shadow: 0 0 0 3px #3b82f6 !important;
    transform: scale(1.05);
}
.schedule-cell {
    min-width: 120px;
    height: 80px;
    cursor: pointer;
    transition: background-color 0.2s;
}
.schedule-cell:hover {
    background-color: #f8fafc;
}
.shift-tag {
    transition: all 0.2s;
    user-select: none;
}
.empty-cell {
    opacity: 0.5;
}
.cell-editing {
    border: 2px solid #3b82f6 !important;
}
</style>

<script>
let selectedShiftId = null;
let selectedShiftColor = null;
let selectedShiftName = null;
let changes = [];

function selectShift(el, id, color, name) {
    document.querySelectorAll('.shift-palette-item').forEach(item => item.classList.remove('active'));
    el.classList.add('active');
    selectedShiftId = id;
    selectedShiftColor = color;
    selectedShiftName = name;
}

function applyShift(cell) {
    if (selectedShiftId === null) {
        alert('Please select a shift from the palette first');
        return;
    }

    const empId = cell.dataset.employeeId;
    const day = cell.dataset.day;

    // Update UI
    if (selectedShiftId === 'OFF') {
        cell.innerHTML = `<div class="empty-cell rounded-3 py-3 border border-dashed text-muted small italic" style="font-size: 0.65rem;">OFF</div>`;
    } else {
        cell.innerHTML = `
            <div class="shift-tag rounded-3 shadow-sm p-2 text-white small fw-bold" style="background-color: ${selectedShiftColor}">
                ${selectedShiftName}
                <div style="font-size: 0.6rem;">MODIFIED</div>
            </div>`;
    }

    // Mark for saving
    const existing = changes.find(c => c.employee_id === empId);
    if (existing) {
        if (selectedShiftId === 'OFF') {
            existing.days = existing.days.filter(d => d !== day);
        } else {
            if (!existing.days.includes(day)) existing.days.push(day);
            existing.shift_id = selectedShiftId;
        }
    } else {
        changes.push({
            employee_id: empId,
            shift_id: selectedShiftId === 'OFF' ? null : selectedShiftId,
            days: selectedShiftId === 'OFF' ? [] : [day]
        });
    }
    
    cell.classList.add('cell-editing');
}

async function saveBulkChanges() {
    if (changes.length === 0) {
        alert('No changes to save.');
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    // Since we are doing bulk, I'll simplify the request
    // In a real app, I'd loop through changes or send them all
    for (const change of changes) {
        try {
            await fetch("{{ route('schedules.bulk-assign') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    employee_ids: [change.employee_id],
                    shift_id: change.shift_id,
                    days: change.days
                })
            });
        } catch (e) {
            console.error(e);
        }
    }

    window.location.reload();
}

document.getElementById('siteFilter').addEventListener('change', function() {
    const siteId = this.value;
    document.querySelectorAll('.employee-row').forEach(row => {
        if (!siteId || row.dataset.siteId === siteId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection