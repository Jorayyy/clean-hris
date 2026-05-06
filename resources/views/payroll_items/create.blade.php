@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <!-- New DTR Summary Section -->
        <div id="basis-info" class="card border-0 shadow-sm mb-4 d-none overflow-hidden">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-bar-graph me-2"></i>DTR SUMMARY BASIS</h6>
                    <span class="badge bg-white text-primary rounded-pill px-3">RECORDED DATA</span>
                </div>
            </div>
            <div class="card-body bg-light p-0">
                <div class="row g-0 text-center border-bottom bg-white">
                    <div class="col-3 p-3 border-end">
                        <div class="small text-muted text-uppercase fw-bold mb-1">Total Reg Hours</div>
                        <div id="basis-reg-hours" class="h4 mb-0 fw-bold text-dark">0.00</div>
                    </div>
                    <div class="col-3 p-3 border-end">
                        <div class="small text-muted text-uppercase fw-bold mb-1">Daily Rate</div>
                        <div id="basis-rate" class="h4 mb-0 fw-bold text-success">₱0.00</div>
                    </div>
                    <div class="col-3 p-3 border-end">
                        <div class="small text-muted text-uppercase fw-bold mb-1">OT Hours</div>
                        <div id="basis-ot-hours" class="h4 mb-0 fw-bold text-info">0.00</div>
                    </div>
                    <div class="col-3 p-3">
                        <div class="small text-muted text-uppercase fw-bold mb-1">Absents</div>
                        <div id="basis-absents" class="h4 mb-0 fw-bold text-danger">0</div>
                    </div>
                </div>
                
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted text-uppercase">Detailed Attendance Logs:</span>
                        <button class="btn btn-sm btn-link text-decoration-none py-0 small" type="button" data-bs-toggle="collapse" data-bs-target="#detailedDtrTable">
                            Toggle View
                        </button>
                    </div>
                    <div class="collapse show" id="detailedDtrTable">
                        <div class="table-responsive rounded shadow-sm border bg-white">
                            <table class="table table-sm mb-0 small">
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
                                <tbody id="detailed-dtr-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-2 small text-muted border-top pt-1 px-3 pb-2 text-center">
                    <i class="bi bi-clock me-1"></i> Recorded Late: <span id="basis-late" class="text-danger fw-bold">0m</span> 
                    | Recorded UT: <span id="basis-ut" class="text-danger fw-bold">0m</span>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="basis-error" class="alert alert-warning border-0 shadow-sm mb-4 d-none">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No finalized DTR summary found for this period. Auto-fill will be disabled.
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Manual Payslip for {{ $payroll->payroll_code }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('payroll-items.store') }}" method="POST" id="payslip-form">
                    @csrf
                    <input type="hidden" name="payroll_id" id="payroll_id" value="{{ $payroll->id }}">

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted text-uppercase">Select Employee</label>
                            <select name="employee_id" id="employee_id" class="form-select form-select-lg border-2 @error('employee_id') is-invalid @enderror" required>
                                @if($employees->count() > 1 || $employees->count() == 0)
                                    <option value="">-- Choose Employee to load DTR --</option>
                                @endif
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ $employees->count() == 1 ? 'selected' : '' }}>{{ $e->full_name }} ({{ $e->employee_id }})</option>
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
                            <label class="form-label fw-bold small">Adjustment/Other Bonuses</label>
                            <input type="number" step="0.01" name="bonuses" id="bonuses" class="form-control amount-field" placeholder="0.00">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mt-4 mb-3">
                        <h6 class="text-success mb-0">Allowances (Add-ons)</h6>
                        <div class="d-flex gap-2">
                            @if($allowanceTypes->isEmpty())
                                <a href="{{ route('admin.settings.allowances.index') }}" class="btn btn-sm btn-warning py-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Setup Allowance Types
                                </a>
                            @endif
                            <button type="button" id="add-allowance-row" class="btn btn-sm btn-outline-success py-0" {{ $allowanceTypes->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-plus-circle me-1"></i> Add Allowance
                            </button>
                        </div>
                    </div>

                    <div id="allowances-list" class="vstack gap-2 mb-4">
                        <!-- Allowance Rows will be added here -->
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mt-4 mb-3">
                        <h6 class="text-danger mb-0">Deductions</h6>
                        <div class="d-flex gap-2">
                            @if($deductionTypes->isEmpty())
                                <a href="{{ route('admin.settings.deductions.index') }}" class="btn btn-sm btn-warning py-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Setup Deduction Types
                                </a>
                            @endif
                            <button type="button" id="add-deduction-row" class="btn btn-sm btn-outline-danger py-0" {{ $deductionTypes->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-plus-circle me-1"></i> Add Deduction
                            </button>
                        </div>
                    </div>

                    <div id="deductions-list" class="vstack gap-2">
                        <!-- Initial Standard Deductions -->
                        <div class="row g-2 deduction-entry align-items-center">
                            <div class="col-md-7">
                                <select name="deductions[0][type]" class="form-select status-select shadow-sm" style="height: auto !important; min-height: 38px;">
                                    @forelse($deductionTypes as $type)
                                        <option value="{{ $type->code }}" {{ $type->code == 'SSS' ? 'selected' : '' }}>{{ $type->name }} ({{ $type->code }})</option>
                                    @empty
                                        <option value="">-- No Deduction Types Defined --</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-light border-end-0">₱</span>
                                    <input type="number" step="0.01" name="deductions[0][amount]" class="form-control amount-field deduction-amount border-start-0" placeholder="0.00" style="height: 38px;">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row" style="height: 38px;"><i class="bi bi-trash"></i></button>
                            </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const allowanceTypes = @json($allowanceTypes);
    const deductionTypes = @json($deductionTypes);

    const employeeSelect = document.getElementById('employee_id');
    const amountFields = document.querySelectorAll('.amount-field');
    const basisInfo = document.getElementById('basis-info');
    const basisError = document.getElementById('basis-error');
    const basisRegHours = document.getElementById('basis-reg-hours');
    const basisOtHours = document.getElementById('basis-ot-hours');
    const basisAbsents = document.getElementById('basis-absents');
    const basisRate = document.getElementById('basis-rate');
    const basisLate = document.getElementById('basis-late');
    const basisUt = document.getElementById('basis-ut');
    const tbody = document.getElementById('detailed-dtr-body');

    async function fetchBasis(employeeId) {
        if (!employeeId) {
            basisInfo.classList.add('d-none');
            basisError.classList.add('d-none');
            return;
        }

        const payrollId = document.getElementById('payroll_id').value;

        try {
            const response = await fetch('/payroll-items/basis?employee_id=' + employeeId + '&payroll_id=' + payrollId);
            if (!response.ok) throw new Error('Network response was not ok');
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

                document.getElementById('total_hours').value = parseFloat(data.dtr.total_regular_hours).toFixed(2);
                const totalDays = (parseFloat(data.dtr.total_regular_hours) / 8).toFixed(1);
                document.getElementById('total_days').value = totalDays;

                const dailyRate = parseFloat(data.employee.daily_rate);
                const hourlyRate = dailyRate / 8;
                document.getElementById('basic_pay').value = (dailyRate * totalDays).toFixed(2);

                if (data.dtr.total_overtime_hours > 0 && data.dtr.is_ot_authorized) {
                    document.getElementById('overtime_pay').value = (data.dtr.total_overtime_hours * hourlyRate * 1.25).toFixed(2);
                } else {
                    document.getElementById('overtime_pay').value = '0.00';
                }

                let totalBonuses = 0;
                if (data.dtr.incentives > 0) {
                    totalBonuses += parseFloat(data.dtr.incentives);
                }
                
                // Add Night Diff (typically 10% of hourly rate)
                if (data.dtr.total_night_diff_hours > 0 && data.dtr.is_nd_authorized) {
                    totalBonuses += (data.dtr.total_night_diff_hours * hourlyRate * 0.10);
                }

                // Add Holiday Pay (typically 100% extra for regular holiday)
                if (data.dtr.total_holiday_hours > 0 && data.dtr.is_holiday_authorized) {
                    totalBonuses += (data.dtr.total_holiday_hours * hourlyRate);
                }

                document.getElementById('bonuses').value = totalBonuses.toFixed(2);

                // Auto-fill Allowances (Add-ons)
                const allowancesList = document.getElementById('allowances-list');
                allowancesList.innerHTML = '';
                allowanceCount = 0; // Reset counter

                if (allowanceTypes.length > 0) {
                    allowanceTypes.forEach(type => {
                        let amount = 0;
                        if (type.type === 'fixed') {
                            amount = type.default_amount;
                        } else if (type.type === 'daily') {
                            amount = type.default_amount * totalDays;
                        }

                        if (amount > 0) {
                            addAllowanceRow(type.code, amount.toFixed(2));
                        }
                    });
                }

                // Auto-fill LATE and UT deductions
                const list = document.getElementById('deductions-list');
                list.innerHTML = '';
                let deductionIndex = 0;

                const lateMultiplier = data.settings ? parseFloat(data.settings.late_rate) : 1.0;
                const utMultiplier = data.settings ? parseFloat(data.settings.undertime_rate) : 1.0;

                if (data.dtr.total_late_minutes > 0) {
                    const lateAmt = (data.dtr.total_late_minutes / 60 * hourlyRate * lateMultiplier).toFixed(2);
                    addDeductionRow('LATE', lateAmt, deductionIndex++);
                }

                if (data.dtr.total_undertime_minutes > 0) {
                    const utAmt = (data.dtr.total_undertime_minutes / 60 * hourlyRate * utMultiplier).toFixed(2);
                    addDeductionRow('UT', utAmt, deductionIndex++);
                }

                // Add standard row if empty
                if (list.children.length === 0) {
                    addDeductionRow('SSS', '0.00', 0);
                }

                calculateNetPay();
            } else {
                basisInfo.classList.add('d-none');
                basisError.classList.remove('d-none');
            }

            tbody.innerHTML = '';
            if (data.attendances && data.attendances.length > 0) {
                data.attendances.forEach(att => {
                    const row = '<tr class="text-center"><td>' + new Date(att.date + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'numeric'}) + '</td><td>' + (att.time_in || '--') + '</td><td>' + (att.time_out || '--') + '</td><td>' + (att.late_minutes || 0) + 'm</td><td>' + (att.undertime_minutes || 0) + 'm</td><td class="fw-bold">' + parseFloat(att.total_hours).toFixed(2) + 'h</td></tr>';
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-2 text-muted">No attendance logs found for this period.</td></tr>';
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function addDeductionRow(typeCode, amount, index) {
        const list = document.getElementById('deductions-list');
        let options = '';
        deductionTypes.forEach(t => {
            options += `<option value="${t.code}" ${t.code === typeCode ? 'selected' : ''}>${t.name} (${t.code})</option>`;
        });

        const template = `
            <div class="row g-2 deduction-entry align-items-center">
                <div class="col-md-7">
                    <select name="deductions[${index}][type]" class="form-select status-select shadow-sm" style="height: auto !important; min-height: 38px;">
                        ${options}
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-light border-end-0">₱</span>
                        <input type="number" step="0.01" name="deductions[${index}][amount]" class="form-control amount-field deduction-amount border-start-0" value="${amount}" placeholder="0.00" style="height: 38px;">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 remove-row shadow-sm" style="height: 38px;"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
        list.insertAdjacentHTML('beforeend', template);
        const newRow = list.lastElementChild;
        newRow.querySelector('.deduction-amount').addEventListener('input', calculateNetPay);
        newRow.querySelector('.remove-row').addEventListener('click', function() {
            newRow.remove();
            calculateNetPay();
        });
    }

    // New Allowance Logic
    let allowanceCount = 0;
    let deductionCount = 1; // Start from 1 because of initial row or handled by auto-fill
    const allowancesList = document.getElementById('allowances-list');
    const addAllowanceBtn = document.getElementById('add-allowance-row');
    const addDeductionBtn = document.getElementById('add-deduction-row');

    if (addAllowanceBtn) {
        addAllowanceBtn.addEventListener('click', function() {
            addAllowanceRow('', 0);
        });
    }

    if (addDeductionBtn) {
        addDeductionBtn.addEventListener('click', function() {
            // Find highest index
            const rows = document.querySelectorAll('.deduction-entry').length;
            addDeductionRow('', 0, rows);
        });
    }

    function addAllowanceRow(code = '', amount = 0) {
        const index = allowanceCount++;
        let options = '<option value="">-- Select --</option>';
        allowanceTypes.forEach(t => {
            options += `<option value="${t.code}" ${t.code === code ? 'selected' : ''}>${t.name}</option>`;
        });

        const row = `
            <div class="row g-2 allowance-entry align-items-center">
                <div class="col-md-7">
                    <select name="allowances[${index}][type]" class="form-select shadow-sm" style="min-height: 38px;">
                        ${options}
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-light border-end-0">₱</span>
                        <input type="number" step="0.01" name="allowances[${index}][amount]" class="form-control amount-field allowance-amount border-start-0" value="${amount}" style="height: 38px;">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-secondary w-100 remove-allowance" style="height: 38px;"><i class="bi bi-dash"></i></button>
                </div>
            </div>
        `;
        allowancesList.insertAdjacentHTML('beforeend', row);
        
        const newRow = allowancesList.lastElementChild;
        newRow.querySelector('.amount-field').addEventListener('input', calculateNetPay);
        newRow.querySelector('.remove-allowance').addEventListener('click', function() {
            newRow.remove();
            calculateNetPay();
        });
        
        calculateNetPay();
    }

    // Update calculateNetPay to include allowances
    function calculateNetPay() {
        let basic = parseFloat(document.getElementById('basic_pay').value) || 0;
        let ot = parseFloat(document.getElementById('overtime_pay').value) || 0;
        let bonuses = parseFloat(document.getElementById('bonuses').value) || 0;
        
        let allowances = 0;
        document.querySelectorAll('.allowance-amount').forEach(input => {
            allowances += parseFloat(input.value) || 0;
        });

        let deductions = 0;
        document.querySelectorAll('.deduction-amount').forEach(input => {
            deductions += parseFloat(input.value) || 0;
        });

        let net = (basic + ot + bonuses + allowances) - deductions;
        document.getElementById('net-pay-val').textContent = '₱' + net.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    // Add listeners to existing amount fields
    document.querySelectorAll('.amount-field').forEach(field => {
        field.addEventListener('input', calculateNetPay);
    });

    // Handle initial deduction row removal
    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.closest('.row').remove();
            calculateNetPay();
        });
    });

    employeeSelect.addEventListener('change', function() {
        fetchBasis(this.value);
    });

    if (employeeSelect.value) {
        fetchBasis(employeeSelect.value);
    }
});
</script>
@endsection
