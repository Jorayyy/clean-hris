@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center py-3">
                <h5 class="mb-0 fw-bold">Create Payroll Period</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('payroll.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Generation Mode</label>
                        <div class="btn-group w-100 shadow-sm" role="group">
                            <input type="radio" class="btn-check" name="mode" id="modeGroup" value="group" checked autocomplete="off">
                            <label class="btn btn-outline-dark fw-bold" for="modeGroup"><i class="bi bi-people me-1"></i> Payroll Group</label>

                            <input type="radio" class="btn-check" name="mode" id="modeSingle" value="single" autocomplete="off">
                            <label class="btn btn-outline-dark fw-bold" for="modeSingle"><i class="bi bi-person me-1"></i> Individual</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Period Code (Unique ID)</label>
                        <input type="text" name="payroll_code" class="form-control" value="PAY-{{ now()->format('Ymd-His') }}" required>
                    </div>

                    <div id="groupSelection" class="mb-3">
                        <label class="form-label text-primary font-weight-bold">Select Payroll Group to Process</label>
                        <select name="payroll_group_id" id="payroll_group_id" class="form-select border-primary shadow-sm py-2">
                            <option value="">-- Choose Group --</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->employees_count ?? 0 }} employees)</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Only active employees in this group will be included.</small>
                    </div>

                    <div id="singleSelection" class="mb-3 d-none">
                        <label class="form-label fw-bold text-primary mb-1">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select border-primary shadow-sm py-2">
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="dtr_period_container" class="mb-3" style="display:none;">
                        <label class="form-label text-success font-weight-bold">Select Finalized DTR Period</label>
                        <select id="dtr_period_select" class="form-select border-success">
                            <option value="">-- Loading... --</option>
                        </select>
                        <small class="text-muted">Only finalized DTR records will be processed.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Period Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Period End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required readonly>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-primary">Pay Release Date (Friday)</label>
                        <input type="date" name="pay_date" class="form-control border-primary" required>
                        <small class="text-muted">Tip: Usually the Friday following the period end date.</small>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 p-2">Create Payroll Period</button>
                    <a href="{{ route('payroll.index') }}" class="btn btn-link w-100 text-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const groupSelect = document.getElementById('payroll_group_id');
    const employeeSelect = document.getElementById('employee_id');
    const periodContainer = document.getElementById('dtr_period_container');
    const periodSelect = document.getElementById('dtr_period_select');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const modeRadios = document.querySelectorAll('input[name="mode"]');
    const groupSelection = document.getElementById('groupSelection');
    const singleSelection = document.getElementById('singleSelection');

    function fetchPeriods() {
        const mode = document.querySelector('input[name="mode"]:checked').value;
        const groupId = groupSelect.value;
        const employeeId = employeeSelect.value;
        
        let url = '/api/finalized-dtrs?';
        if (mode === 'group' && groupId) {
            url += `payroll_group_id=${groupId}`;
        } else if (mode === 'single' && employeeId) {
            url += `employee_id=${employeeId}`;
        } else {
            periodContainer.style.display = 'none';
            return;
        }

        // Show loading state
        periodContainer.style.display = 'block';
        periodSelect.innerHTML = '<option value="">-- Loading... --</option>';
        startDateInput.value = '';
        endDateInput.value = '';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                periodSelect.innerHTML = '<option value="">-- Select Finalized DTR Period --</option>';
                if (data.length === 0) {
                    periodSelect.innerHTML = '<option value="">No finalized DTRs found</option>';
                } else {
                    data.forEach(period => {
                        const option = document.createElement('option');
                        option.value = `${period.start_date}|${period.end_date}`;
                        option.textContent = period.label;
                        periodSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching periods:', error);
                periodSelect.innerHTML = '<option value="">Error loading periods</option>';
            });
    }

    modeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'single') {
                groupSelection.classList.add('d-none');
                singleSelection.classList.remove('d-none');
                groupSelect.value = '';
                groupSelect.required = false;
                employeeSelect.required = true;
            } else {
                groupSelection.classList.remove('d-none');
                singleSelection.classList.add('d-none');
                employeeSelect.value = '';
                employeeSelect.required = false;
                groupSelect.required = true;
            }
            periodContainer.style.display = 'none';
            startDateInput.value = '';
            endDateInput.value = '';
        });
    });

    groupSelect.addEventListener('change', fetchPeriods);
    employeeSelect.addEventListener('change', fetchPeriods);

    periodSelect.addEventListener('change', function() {
        if (this.value) {
            const [start, end] = this.value.split('|');
            startDateInput.value = start;
            endDateInput.value = end;
        } else {
            startDateInput.value = '';
            endDateInput.value = '';
        }
    });
});
</script>
@endpush
