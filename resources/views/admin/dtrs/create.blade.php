@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-dark text-white text-center py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-plus me-2"></i>Generate New DTR Record</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.dtrs.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Generation Mode</label>
                        <div class="btn-group w-100 shadow-sm" role="group">
                            <input type="radio" class="btn-check" name="mode" id="modeSingle" value="single" checked autocomplete="off">
                            <label class="btn btn-outline-dark fw-bold" for="modeSingle"><i class="bi bi-person me-1"></i> Individual</label>

                            <input type="radio" class="btn-check" name="mode" id="modeGroup" value="group" autocomplete="off">
                            <label class="btn btn-outline-dark fw-bold" for="modeGroup"><i class="bi bi-people me-1"></i> Payroll Group</label>
                        </div>
                    </div>

                    <div id="singleSelect" class="mb-4">
                        <label class="form-label fw-bold text-primary mb-1">Select Employee</label>
                        <select name="employee_id" class="form-select border-primary shadow-sm py-2">
                            @foreach($employees as $row)
                                <option value="{{ $row->id }}">
                                    {{ $row->full_name }} ({{ $row->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="groupSelect" class="mb-4 d-none">
                        <label class="form-label fw-bold text-primary mb-1">Select Payroll Group</label>
                        <select name="payroll_group_id" class="form-select border-primary shadow-sm py-2">
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted border-bottom d-block mb-3">Coverage Period</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-01') }}" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" name="end_date" value="{{ date('Y-m-t') }}" class="form-control shadow-sm" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-dark btn-lg py-3 fw-bold shadow">
                            <i class="bi bi-gear-wide-connected me-1"></i> Process Batch Generation
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('admin.dtrs.index') }}" class="btn btn-link text-secondary text-decoration-none small fw-bold">
                            <i class="bi bi-arrow-left me-1"></i> Back to DTR List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'single') {
                document.getElementById('singleSelect').classList.remove('d-none');
                document.getElementById('groupSelect').classList.add('d-none');
                document.querySelector('label[for="modeSingle"]').classList.add('active');
            } else {
                document.getElementById('singleSelect').classList.add('d-none');
                document.getElementById('groupSelect').classList.remove('d-none');
            }
        });
    });
</script>
@endsection
