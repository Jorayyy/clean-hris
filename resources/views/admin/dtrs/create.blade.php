@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Generate New DTR Record</h4>
    </div>
    <div class="col text-end">
        <a href="{{ route('admin.dtrs.index') }}" class="btn btn-outline-secondary shadow-sm px-4 fw-bold">
            <i class="bi bi-x-circle me-1"></i> Back to DTRs
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.dtrs.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Generation Mode</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="mode" id="modeSingle" value="single" checked autocomplete="off">
                    <label class="btn btn-outline-primary" for="modeSingle"><i class="bi bi-person me-1"></i> Individual Employee</label>

                    <input type="radio" class="btn-check" name="mode" id="modeGroup" value="group" autocomplete="off">
                    <label class="btn btn-outline-primary" for="modeGroup"><i class="bi bi-people me-1"></i> Entire Payroll Group</label>
                </div>
            </div>

            <div id="singleSelect" class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Select Employee</label>
                <select name="employee_id" class="form-select border-primary">
                    @foreach($employees as $row)
                        <option value="{{ $row->id }}">
                            {{ $row->full_name }} ({{ $row->employee_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="groupSelect" class="mb-4 d-none">
                <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Select Payroll Group</label>
                <select name="payroll_group_id" class="form-select border-primary">
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Coverage Period</label>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-01') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-t') }}" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg p-3 fw-bold">
                    <i class="bi bi-gear-wide-connected me-1"></i> Process Batch Generation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'single') {
                document.getElementById('singleSelect').classList.remove('d-none');
                document.getElementById('groupSelect').classList.add('d-none');
            } else {
                document.getElementById('singleSelect').classList.add('d-none');
                document.getElementById('groupSelect').classList.remove('d-none');
            }
        });
    });
</script>
@endsection
