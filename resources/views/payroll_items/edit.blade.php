@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Edit Payslip: {{ $payrollItem->employee->full_name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payroll-items.update', $payrollItem->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3 g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Total Days Paid</label>
                                <input type="number" step="0.5" name="total_days" class="form-control" value="{{ $payrollItem->total_days }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Total Hours worked</label>
                                <input type="number" step="0.01" name="total_hours" class="form-control" value="{{ $payrollItem->total_hours }}" required>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3">Earnings</h6>
                        <div class="row mb-3 g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Basic Pay</label>
                                <input type="number" step="0.01" name="basic_pay" class="form-control" value="{{ $payrollItem->basic_pay }}" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Overtime Pay</label>
                                <input type="number" step="0.01" name="overtime_pay" class="form-control" value="{{ $payrollItem->overtime_pay }}" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Bonuses</label>
                                <input type="number" step="0.01" name="bonuses" class="form-control" value="{{ $payrollItem->bonuses }}" placeholder="0.00">
                            </div>
                        </div>

                        <h6 class="text-danger border-bottom pb-2 mt-4 mb-3">Deductions</h6>
                        <div class="row mb-3 g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">SSS</label>
                                <input type="number" step="0.01" name="deductions_sss" class="form-control" value="{{ $payrollItem->deductions_sss }}" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Pag-IBIG</label>
                                <input type="number" step="0.01" name="deductions_pagibig" class="form-control" value="{{ $payrollItem->deductions_pagibig }}" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">PhilHealth</label>
                                <input type="number" step="0.01" name="deductions_philhealth" class="form-control" value="{{ $payrollItem->deductions_philhealth }}" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Other Deductions</label>
                                <input type="number" step="0.01" name="other_deductions" class="form-control" value="{{ $payrollItem->other_deductions }}" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('payroll.show', $payrollItem->payroll_id) }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Payslip</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
