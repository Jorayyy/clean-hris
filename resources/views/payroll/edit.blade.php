@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0 fw-bold text-center"><i class="bi bi-pencil-square me-2"></i>Edit Payroll Period</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('payroll.update', $payroll->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Period Code</label>
                        <input type="text" name="payroll_code" class="form-control" value="{{ $payroll->payroll_code }}" required>
                        @error('payroll_code')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-primary">Payroll Group</label>
                        <select name="payroll_group_id" class="form-select border-primary" required>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ $payroll->payroll_group_id == $g->id ? 'selected' : '' }}>
                                    {{ $g->name }} ({{ $g->employees_count ?? 0 }} employees)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $payroll->start_date }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $payroll->end_date }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-primary">Pay Date</label>
                        <input type="date" name="pay_date" class="form-control border-primary" value="{{ $payroll->pay_date }}" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary p-2 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Update Period
                        </button>
                        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary p-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
