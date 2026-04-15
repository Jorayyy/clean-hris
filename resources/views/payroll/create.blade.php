@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center">Create Payroll Period</div>
            <div class="card-body">
                <form action="{{ route('payroll.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Period Code (Unique ID)</label>
                        <input type="text" name="payroll_code" class="form-control" value="PAY-{{ now()->format('Ymd-His') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-primary font-weight-bold">Select Payroll Group to Process</label>
                        <select name="payroll_group_id" id="payroll_group_id" class="form-select border-primary" required>
                            <option value="">-- Choose Group --</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->employees_count ?? 0 }} employees)</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Only active employees in this group will be included.</small>
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
    const periodContainer = document.getElementById('dtr_period_container');
    const periodSelect = document.getElementById('dtr_period_select');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    groupSelect.addEventListener('change', function() {
        const groupId = this.value;
        if (!groupId) {
            periodContainer.style.display = 'none';
            return;
        }

        // Show loading state
        periodContainer.style.display = 'block';
        periodSelect.innerHTML = '<option value="">-- Loading... --</option>';
        startDateInput.value = '';
        endDateInput.value = '';

        fetch(`/api/finalized-dtrs?payroll_group_id=${groupId}`)
            .then(response => response.json())
            .then(data => {
                periodSelect.innerHTML = '<option value="">-- Select Finalized DTR Period --</option>';
                if (data.length === 0) {
                    periodSelect.innerHTML = '<option value="">No finalized DTRs found for this group</option>';
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
    });

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
