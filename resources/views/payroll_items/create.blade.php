@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Manual Payslip for {{ $payroll->payroll_code }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-5">
                        <div class="col-md-12">
                            <div id="basis-info" class="alert alert-info d-none shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-1"></i> Payroll Basis ({{ $payroll->start_date }} to {{ $payroll->end_date }})</h6>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detailedDtr" aria-expanded="false">
                                        <i class="bi bi-table me-1"></i> Toggle Detailed DTR
                                    </button>
                                </div>
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <div class="small text-muted">Finalized Regular Hours</div>
                                        <div id="basis-reg-hours" class="fw-bold h5 mb-0">0.00</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Finalized OT Hours</div>
                                        <div id="basis-ot-hours" class="fw-bold h5 mb-0">0.00</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Absent Days</div>
                                        <div id="basis-absents" class="fw-bold h5 mb-0 text-danger">0</div>
                                    </div>
                                    <div class="col-md-3 border-start ps-3">
                                        <div class="small text-muted">Employee Daily Rate</div>
                                        <div id="basis-rate" class="fw-bold h5 mb-0 text-primary">₱0.00</div>
                                    </div>
                                </div>

                                <div class="collapse mt-3" id="detailedDtr">
                                    <div class="table-responsive bg-white rounded shadow-sm">
                                        <table class="table table-sm table-bordered mb-0" style="font-size: 0.75rem;">
                                            <thead class="bg-light">
                                                <tr class="text-center">
                                                    <th>Date</th>
                                                    <th>In</th>
                                                    <th>Out</th>
                                                    <th>Late</th>
                                                    <th>UT</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detailed-dtr-body">
                                                <!-- Detailed logs injected here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-2 small text-muted border-top pt-1">
                                    <i class="bi bi-clock me-1"></i> Recorded Late: <span id="basis-late" class="text-danger fw-bold">0m</span> 
                                    | Recorded UT: <span id="basis-ut" class="text-danger fw-bold">0m</span>
                                </div>
                            </div>
                            <div id="basis-error" class="alert alert-warning d-none">
                                <i class="bi bi-exclamation-triangle me-1"></i> No finalized DTR found for this employee for the period {{ $payroll->start_date }} to {{ $payroll->end_date }}.
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('payroll-items.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payroll_id" id="payroll_id" value="{{ $payroll->id }}">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Select Employee</label>
                                <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $e)
                                        <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_id }})</option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Total Days Paid</label>
                                <input type="number" step="0.5" name="total_days" id="total_days" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Total Hours worked</label>
                                <input type="number" step="0.01" name="total_hours" id="total_hours" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3">Earnings</h6>
                        <div class="row mb-3 g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Basic Pay</label>
                                <input type="number" step="0.01" name="basic_pay" id="basic_pay" class="form-control amount-field" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Overtime Pay</label>
                                <input type="number" step="0.01" name="overtime_pay" id="overtime_pay" class="form-control amount-field" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Bonuses</label>
                                <input type="number" step="0.01" name="bonuses" id="bonuses" class="form-control amount-field" placeholder="0.00">
                            </div>
                        </div>

                        <h6 class="text-danger border-bottom pb-2 mt-4 mb-3">Deductions</h6>
                        <div class="row mb-3 g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">SSS</label>
                                <input type="number" step="0.01" name="deductions_sss" id="deductions_sss" class="form-control amount-field" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Pag-IBIG</label>
                                <input type="number" step="0.01" name="deductions_pagibig" id="deductions_pagibig" class="form-control amount-field" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">PhilHealth</label>
                                <input type="number" step="0.01" name="deductions_philhealth" id="deductions_philhealth" class="form-control amount-field" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Other Deductions</label>
                                <input type="number" step="0.01" name="other_deductions" id="other_deductions" class="form-control amount-field" placeholder="0.00">
                            </div>
                        </div>

                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body text-end py-3">
                                <h4 class="mb-0 fw-bold">
                                    <span class="small text-muted me-2">NET PAY PREVIEW:</span>
                                    <span id="net-pay-val" class="text-success">₱0.00</span>
                                </h4>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('payroll.show', $payroll->id) }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Payslip</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employee_id');
    const amountFields = document.querySelectorAll('.amount-field');
    
    // Basis Info Elements
    const basisInfo = document.getElementById('basis-info');
    const basisError = document.getElementById('basis-error');
    const basisRegHours = document.getElementById('basis-reg-hours');
    const basisOtHours = document.getElementById('basis-ot-hours');
    const basisAbsents = document.getElementById('basis-absents');
    const basisRate = document.getElementById('basis-rate');
    const basisLate = document.getElementById('basis-late');
    const basisUt = document.getElementById('basis-ut');

    // Fetch DTR Basis when employee changes
    employeeSelect.addEventListener('change', async function() {
        const employeeId = this.value;
        const payrollId = document.getElementById('payroll_id').value;

        if (!employeeId) {
            basisInfo.classList.add('d-none');
            basisError.classList.add('d-none');
            return;
        }

        try {
            const response = await fetch(`/payroll-items/basis?employee_id=${employeeId}&payroll_id=${payrollId}`);
            const data = await response.json();

            if (data.dtr) {
                basisInfo.classList.remove('d-none');
                basisError.classList.add('d-none');
                
                basisRegHours.textContent = parseFloat(data.dtr.total_regular_hours).toFixed(2);
                basisOtHours.textContent = parseFloat(data.dtr.total_overtime_hours).toFixed(2);
                basisAbsents.textContent = data.dtr.total_absent_days;
                basisRate.textContent = '₱' + parseFloat(data.employee.daily_rate).toLocaleString(undefined, {minimumFractionDigits: 2});
                basisLate.textContent = data.dtr.total_late_minutes + 'm';
                basisUt.textContent = data.dtr.total_undertime_minutes + 'm';

                // Auto-fill help
                document.getElementById('total_hours').value = data.dtr.total_regular_hours;
                document.getElementById('total_days').value = (45 - data.dtr.total_absent_days) / 9; // Sample logic if 9h/day
            } else {
                // If no DTR summary, but we have attendance logs or just the employee rate
                basisInfo.classList.remove('d-none'); // Show it anyway to display the table
                basisError.classList.remove('d-none'); // But keep the warning that it's not finalized
                
                basisRegHours.textContent = '0.00';
                basisOtHours.textContent = '0.00';
                basisAbsents.textContent = '0';
                basisRate.textContent = data.employee ? '₱' + parseFloat(data.employee.daily_rate).toLocaleString(undefined, {minimumFractionDigits: 2}) : '₱0.00';
                basisLate.textContent = '0m';
                basisUt.textContent = '0m';
            }

            // Always try to inject detailed logs if they exist
            const tbody = document.getElementById('detailed-dtr-body');
            tbody.innerHTML = '';
            if (data.attendances && data.attendances.length > 0) {
                data.attendances.forEach(att => {
                    const row = `
                        <tr class="text-center">
                            <td>${new Date(att.date).toLocaleDateString('en-US', {month:'short', day:'numeric'})}</td>
                            <td>${att.time_in || '--'}</td>
                            <td>${att.time_out || '--'}</td>
                            <td class="${att.late_minutes > 0 ? 'text-danger' : ''}">${att.late_minutes || 0}m</td>
                            <td class="${att.undertime_minutes > 0 ? 'text-danger' : ''}">${att.undertime_minutes || 0}m</td>
                            <td class="fw-bold">${parseFloat(att.total_hours).toFixed(2)}h</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-2 text-muted">No attendance logs found for this period.</td></tr>';
            }
        } catch (error) {
        } catch (error) {
            console.error('Error fetching basis:', error);
        }
    });

    // Live Net Pay Preview
    function calculateNetPay() {
        let earnings = 
            (parseFloat(document.getElementById('basic_pay').value) || 0) +
            (parseFloat(document.getElementById('overtime_pay').value) || 0) +
            (parseFloat(document.getElementById('bonuses').value) || 0);
        
        let deductions =
            (parseFloat(document.getElementById('deductions_sss').value) || 0) +
            (parseFloat(document.getElementById('deductions_pagibig').value) || 0) +
            (parseFloat(document.getElementById('deductions_philhealth').value) || 0) +
            (parseFloat(document.getElementById('other_deductions').value) || 0);
        
        const netPay = earnings - deductions;
        document.getElementById('net-pay-val').textContent = '₱' + netPay.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    amountFields.forEach(field => {
        field.addEventListener('input', calculateNetPay);
    });
});
</script>
@endsection
