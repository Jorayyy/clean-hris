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
                        <select name="payroll_group_id" class="form-select border-primary" required>
                            <option value="">-- Choose Group --</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->employees_count ?? 0 }} employees)</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Only active employees in this group will be included.</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Period Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Period End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
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
