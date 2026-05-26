@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="mb-3">
                <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Hub
                </a>
            </div>
            <h4 class="fw-bold text-dark mb-0">Shift Definitions</h4>
            <p class="text-muted small">Manage reusable shift templates for the entire organization.</p>
        </div>
        <button class="btn btn-primary rounded-3 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="bi bi-plus-lg me-2"></i> Add New Shift
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        @forelse($shifts as $shift)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden border-start border-5" style="border-color: {{ $shift->color }} !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $shift->name }}</h6>
                                <span class="badge bg-light text-dark border small fw-normal">{{ $shift->code ?? 'NO-CODE' }}</span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm text-muted" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><button class="dropdown-item small" onclick='editShift(@json($shift))'><i class="bi bi-pencil me-2"></i> Edit</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('schedules.shifts.destroy', $shift->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger small" onclick="return confirm('Delete this shift definition?')">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock text-muted me-2"></i>
                                <span class="small fw-medium">{{ date('h:i A', strtotime($shift->time_in)) }} - {{ date('h:i A', strtotime($shift->time_out)) }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-cup-hot text-muted me-2"></i>
                                <span class="small text-muted">{{ $shift->break_minutes }} Minutes Break</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lightning text-muted me-2"></i>
                                <span class="small text-muted">{{ $shift->grace_period }} Min. Grace Period</span>
                            </div>
                        </div>

                        <div class="pt-3 border-top border-light d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Type: <strong>{{ $shift->type }}</strong></span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" {{ $shift->is_active ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="bi bi-clock-history text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold">No Shifts Defined</h5>
                    <p class="text-muted">Start by adding a "Morning", "Night", or "Mid" shift here.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add/Edit Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="shiftForm" action="{{ route('schedules.shifts.store') }}" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label small fw-bold">Shift Name</label>
                            <input type="text" name="name" id="shift_name" class="form-control" placeholder="e.g. Morning Shift" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold">Code</label>
                            <input type="text" name="code" id="shift_code" class="form-control" placeholder="MSHIFT" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Time In</label>
                            <input type="time" name="time_in" id="shift_time_in" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Time Out</label>
                            <input type="time" name="time_out" id="shift_time_out" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Break (Minutes)</label>
                            <input type="number" name="break_minutes" id="shift_break_minutes" class="form-control" value="60">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Grace Period (Min)</label>
                            <input type="number" name="grace_period" id="shift_grace_period" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Label Color</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach(['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6366f1', '#ec4899', '#64748b'] as $color)
                                    <label class="color-swatch-label" style="background-color: {{ $color }}; padding: 15px; border-radius: 8px; cursor: pointer; position: relative;">
                                        <input type="radio" name="color" value="{{ $color }}" class="d-none color-input" {{ $loop->first ? 'checked' : '' }}>
                                        <i class="bi bi-check-circle text-white check-icon d-none"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm fw-bold">Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.color-swatch-label {
    min-width: 45px;
    min-height: 45px;
    transition: transform 0.2s;
}
.color-swatch-label:active {
    transform: scale(0.9);
}
.color-swatch-label input:checked + .check-icon {
    display: block !important;
}
.check-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>

<script>
function editShift(shift) {
    document.getElementById('modalTitle').innerText = 'Edit Shift';
    document.getElementById('shiftForm').action = "{{ url('admin/scheduling/shifts') }}/" + shift.id;
    document.getElementById('methodField').innerHTML = '@method("PUT")';
    
    document.getElementById('shift_name').value = shift.name;
    document.getElementById('shift_code').value = shift.code || '';
    document.getElementById('shift_time_in').value = shift.time_in;
    document.getElementById('shift_time_out').value = shift.time_out;
    document.getElementById('shift_break_minutes').value = shift.break_minutes;
    document.getElementById('shift_grace_period').value = shift.grace_period;
    
    // Select color
    const colorRadios = document.querySelectorAll('.color-input');
    colorRadios.forEach(radio => {
        if (radio.value === shift.color) {
            radio.checked = true;
        }
    });

    const modal = new bootstrap.Modal(document.getElementById('addShiftModal'));
    modal.show();
}

// Reset modal when closing or opening for "Add"
document.getElementById('addShiftModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').innerText = 'Add New Shift';
    document.getElementById('shiftForm').action = "{{ route('schedules.shifts.store') }}";
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('shiftForm').reset();
});
</script>
@endsection