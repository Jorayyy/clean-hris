@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Adjust Salary Details</h5>
                <span>Emp: {{ $salary->employee->full_name }} ({{ $salary->employee->employee_id }})</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 small">
                    Payroll Period: {{ $salary->payroll->start_date }} to {{ $salary->payroll->end_date }}
                </div>

                <form action="{{ route('salaries.update', $salary->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-success border-bottom pb-2 mb-3">EARNINGS / ADDITIONALS</h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Basic Salary</label>
                                <input type="number" step="0.01" name="basic_pay" class="form-control" value="{{ $salary->basic_pay }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Overtime Pay</label>
                                <input type="number" step="0.01" name="overtime_pay" class="form-control" value="{{ $salary->overtime_pay }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Bonuses / Incentives</label>
                                <input type="number" step="0.01" name="bonuses" class="form-control" value="{{ $salary->bonuses }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Night Differential</label>
                                <input type="number" step="0.01" name="night_diff" class="form-control" value="{{ $salary->night_diff }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-danger border-bottom pb-2 mb-3">DEDUCTIONS</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">SSS Contribution</label>
                                <input type="number" step="0.01" name="deductions_sss" class="form-control" value="{{ $salary->deductions_sss }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Pag-Ibig Contribution</label>
                                <input type="number" step="0.01" name="deductions_pagibig" class="form-control" value="{{ $salary->deductions_pagibig }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">PhilHealth Contribution</label>
                                <input type="number" step="0.01" name="deductions_philhealth" class="form-control" value="{{ $salary->deductions_philhealth }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Other Deductions (Late/UT/Loans)</label>
                                <input type="number" step="0.01" name="other_deductions" class="form-control" value="{{ $salary->other_deductions }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-light p-3 border rounded">
                        <div>
                            <span class="text-muted small">Current Net Pay:</span>
                            <h3 class="text-primary mb-0">P{{ number_format($salary->net_pay, 2) }}</h3>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary px-4">Update & Recalculate</button>
                            <a href="{{ route('salaries.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
