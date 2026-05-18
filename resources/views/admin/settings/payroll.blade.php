@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0">Payroll Configuration</h4>
                <p class="text-muted">Manage contribution rates and attendance deduction multipliers.</p>
            </div>
            <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Back to Payroll Page
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <form action="{{ route('admin.payroll-settings.update') }}" method="POST">
                @csrf
                
                <!-- Contribution Rates -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 py-4 px-4">
                        <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-cash-stack me-2"></i>Government Contribution Rates</h5>
                        <p class="text-muted small mb-0">Set default percentage rates for automatic calculations.</p>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="alert alert-info border-0 rounded-3 shadow-sm py-3 mb-4 bg-opacity-10">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                                <div>
                                    <p class="mb-0 small fw-medium">Percentages should be entered as decimals. For example: 5% = 0.05, 10% = 0.10.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">SSS Rate (%)</label>
                                <div class="form-floating">
                                    <input type="number" step="0.0001" name="sss_rate" class="form-control bg-light border-0" id="sss" value="{{ $settings->sss_rate ?? 0.045 }}">
                                    <label for="sss">e.g. 0.045</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Pag-IBIG Rate (%)</label>
                                <div class="form-floating">
                                    <input type="number" step="0.0001" name="pagibig_rate" class="form-control bg-light border-0" id="pag" value="{{ $settings->pagibig_rate ?? 0.02 }}">
                                    <label for="pag">e.g. 0.020</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">PhilHealth Rate (%)</label>
                                <div class="form-floating">
                                    <input type="number" step="0.0001" name="philhealth_rate" class="form-control bg-light border-0" id="phil" value="{{ $settings->philhealth_rate ?? 0.05 }}">
                                    <label for="phil">e.g. 0.050</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Multipliers -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 py-4 px-4">
                        <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-clock-history me-2"></i>Attendance Deduction Rules</h5>
                        <p class="text-muted small mb-0">Penalties for late arrivals and early departures.</p>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border border-1 h-100">
                                    <label class="form-label fw-bold d-block mb-2 text-dark">Late Penalty Multiplier</label>
                                    <input type="number" step="0.01" name="late_rate" class="form-control border-0 py-2 fs-5 fw-bold" value="{{ $settings->late_rate ?? 1.00 }}">
                                    <p class="text-muted small mt-2 mb-0">1.0 = Deduct exact hourly wage. 2.0 = Double penalty.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border border-1 h-100">
                                    <label class="form-label fw-bold d-block mb-2 text-dark">Undertime Penalty Multiplier</label>
                                    <input type="number" step="0.01" name="undertime_rate" class="form-control border-0 py-2 fs-5 fw-bold" value="{{ $settings->undertime_rate ?? 1.00 }}">
                                    <p class="text-muted small mt-2 mb-0">Multiplier applied to undertime hours against base hourly rate.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm fw-bold">
                        <i class="bi bi-check2-circle me-2"></i> Update Payroll Settings
                    </button>
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <!-- Other Addition Enrollment Shortcut -->
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white p-4 mb-4">
                <div class="text-center py-4">
                    <i class="bi bi-file-earmark-arrow-up fs-1 mb-3"></i>
                    <h5 class="fw-bold">Bulk Addition Enrollment</h5>
                    <p class="opacity-75 small">Mass upload other additions for employees via template.</p>
                    <a href="{{ route('admin.payroll.other-addition-enrollment.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                         Go to Enrollment
                    </a>
                </div>
            </div>

            <!-- Allowance Library Shortcut -->
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 mb-4">
                <div class="text-center py-4">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                        <i class="bi bi-gift-fill fs-1 text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white">Payroll Add-ons</h5>
                    <p class="text-white-50 mb-4 px-3">Manage recurring allowances like Attendance Bonus, Food & Rice Allowance.</p>
                    <a href="{{ route('admin.settings.allowances.index') }}" class="btn btn-outline-light rounded-pill px-4">
                        Manage Add-ons Library <i class="bi bi-arrow-right-short ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Deduction Library Shortcut -->
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4">
                <div class="text-center py-4">
                    <div class="bg-primary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                        <i class="bi bi-tags-fill fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold text-white">Company Deductions</h5>
                    <p class="text-white-50 mb-4 px-3">Manage recurring deductions like HMO, Loans, and Uniform fees.</p>
                    <a href="{{ route('admin.settings.deductions.index') }}" class="btn btn-outline-light rounded-pill px-4">
                        Manage Deduction Library <i class="bi bi-arrow-right-short ms-1"></i>
                    </a>
                </div>
                <hr class="opacity-25 my-4">
                <div class="small">
                    <h6 class="fw-bold mb-2 text-white">Need Help?</h6>
                    <p class="text-white-50">These settings affect calculations across the entire system. Please verify local labor laws before making major adjustments.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
