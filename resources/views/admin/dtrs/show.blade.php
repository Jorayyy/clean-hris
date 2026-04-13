@extends('layouts.app')

@section('content')
<style>
    .dtr-header { background: #003366; color: white; font-size: 0.8rem; }
    .dtr-table th { background: #005a9c; color: white; font-size: 0.75rem; text-transform: uppercase; border: 1px solid #dee2e6; vertical-align: middle; text-align: center; }
    .dtr-table td { font-size: 0.75rem; border: 1px solid #dee2e6; vertical-align: middle; padding: 4px; }
    .bg-dtr-label { background: #f8f9fa; font-weight: bold; width: 15%; }
    .bg-dtr-value { background: #ffffff; width: 18%; }
    .footer-summary { background: #e9ecef; border-top: 2px solid #003366; }

    @media print {
        @page { size: landscape; margin: 0.5cm; }
        .dtr-header { background: #003366 !important; color: white !important; -webkit-print-color-adjust: exact; }
        .dtr-table th { background: #005a9c !important; color: white !important; -webkit-print-color-adjust: exact; }
        .bg-dtr-label { background: #f8f9fa !important; -webkit-print-color-adjust: exact; }
        .footer-summary { background: #e9ecef !important; -webkit-print-color-adjust: exact; }
        .btn, .no-print, .mb-3.d-flex { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        body { font-size: 10pt; }
        .container-fluid { padding: 0 !important; }
    }
</style>

<div class="mb-3 d-flex justify-content-between align-items-center no-print">
    <a href="{{ route('admin.dtrs.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3 fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <h5 class="fw-bold mb-0 text-dark">DTR Details for {{ $dtr->employee->full_name }}</h5>
</div>

<div class="card shadow border-0 mb-4">
    <div class="card-body p-0">
        <!-- HEADER INFO -->
        <div class="row g-0 border-bottom dtr-header p-2 px-3 fw-bold">
            <div class="col-md-6 text-uppercase">Salary Rate: {{ $dtr->employee->salary_type }} ({{ number_format($dtr->employee->daily_rate, 2) }})</div>
            <div class="col-md-6 text-end">Date Employed: {{ $dtr->employee->date_hired ?? 'N/A' }}</div>
        </div>
        
        <table class="table table-bordered mb-0">
            <tr>
                <td class="bg-dtr-label">Payroll Period</td>
                <td class="bg-dtr-value text-primary fw-bold">{{ $dtr->start_date->format('Y-m-d') }} to {{ $dtr->end_date->format('Y-m-d') }}</td>
                <td class="bg-dtr-label">Department</td>
                <td class="bg-dtr-value">{{ $dtr->employee->department ?? 'N/A' }}</td>
                <td class="bg-dtr-label">Employment</td>
                <td class="bg-dtr-value">{{ $dtr->employee->employment_status ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bg-dtr-label">Employee ID</td>
                <td class="bg-dtr-value fw-bold">{{ $dtr->employee->employee_id }}</td>
                <td class="bg-dtr-label">Section</td>
                <td class="bg-dtr-value">{{ $dtr->employee->section ?? 'N/A' }}</td>
                <td class="bg-dtr-label">Classification</td>
                <td class="bg-dtr-value">STAFF</td>
            </tr>
            <tr>
                <td class="bg-dtr-label">Name</td>
                <td class="bg-dtr-value fw-bold">{{ $dtr->employee->full_name }}</td>
                <td colspan="2"></td>
                <td class="bg-dtr-label">Pay Type</td>
                <td class="bg-dtr-value">WEEKLY</td>
            </tr>
            <tr>
                <td class="bg-dtr-label">Position</td>
                <td class="bg-dtr-value">{{ $dtr->employee->position }}</td>
                <td colspan="2"></td>
                <td class="bg-dtr-label">Location</td>
                <td class="bg-dtr-value">MAIN OFFICE</td>
            </tr>
        </table>

        <!-- LOGS TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered dtr-table mb-0 text-center">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 50px;">Date</th>
                        <th rowspan="2">Day</th>
                        <th rowspan="2" class="bg-danger text-white">Shift Time<br><small>IN | OUT</small></th>
                        <th rowspan="2" class="bg-success text-white">Actual Time<br><small>IN | OUT</small></th>
                        <th colspan="3">No. of Hours</th>
                        <th colspan="6">Overtime</th>
                        <th colspan="3">Filed Forms</th>
                    </tr>
                    <tr>
                        <th>Late</th>
                        <th>Over<br>Break</th>
                        <th>UT</th>
                        <th>Reg</th>
                        <th>RD</th>
                        <th>Holiday</th>
                        <th>RD<br>Hol</th>
                        <th>ND</th>
                        <th>ATRO</th>
                        <th>OB</th>
                        <th>Leave</th>
                        <th>UT</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $period = \Carbon\CarbonPeriod::create($dtr->start_date, $dtr->end_date);
                    @endphp
                    @foreach($period as $date)
                        @php
                            $log = $attendances->firstWhere('date', $date->format('Y-m-d'));
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $date->format('m-d') }}</td>
                            <td>{{ $date->format('D') }}</td>
                            <td class="bg-light text-danger">
                                {{ ($log && $log->employee && $log->employee->active_schedule) ? \Carbon\Carbon::parse($log->employee->active_schedule->time_in)->format('H:i') : '08:00' }} | 
                                {{ ($log && $log->employee && $log->employee->active_schedule) ? \Carbon\Carbon::parse($log->employee->active_schedule->time_out)->format('H:i') : '17:00' }}
                            </td>
                            <td class="bg-light text-success fw-bold">
                                {{ ($log && $log->time_in && $log->time_in !== '00:00:00') ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : '--:--' }} | 
                                {{ ($log && $log->time_out && $log->time_out !== '00:00:00') ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : '--:--' }}
                            </td>
                            <td class="{{ ($log && $log->late_minutes > 0) ? 'text-danger fw-bold' : '' }}">
                                {{ ($log && $log->late_minutes > 0) ? $log->late_minutes : '' }}
                            </td>
                            <td class="small pe-1">
                                @if($log && $log->break1_out && $log->break1_out !== '00:00:00')
                                    <span class="d-block text-info fw-bold" title="Lunch Out">LO: {{ \Carbon\Carbon::parse($log->break1_out)->format('H:i') }}</span>
                                @endif
                                @if($log && $log->break1_in && $log->break1_in !== '00:00:00')
                                    <span class="d-block text-info fw-bold" title="Lunch In">LI: {{ \Carbon\Carbon::parse($log->break1_in)->format('H:i') }}</span>
                                @endif
                            </td>
                            <td class="{{ ($log && $log->undertime_minutes > 0) ? 'text-warning fw-bold' : '' }}">
                                {{ ($log && $log->undertime_minutes > 0) ? $log->undertime_minutes : '' }}
                            </td>
                            <td class="text-primary fw-bold">{{ $log ? '8.00' : '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- FOOTER SUMMARY -->
        <div class="footer-summary p-3">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered bg-white small mb-0">
                        <tr class="fw-bold bg-light">
                            <td>Description</td>
                            <td>Regular</td>
                            <td>OT</td>
                            <td>Late (m)</td>
                            <td>UT (m)</td>
                        </tr>
                        <tr>
                            <td>Totals</td>
                            <td class="fw-bold">{{ $dtr->total_regular_hours }}</td>
                            <td>0.00</td>
                            <td class="text-danger fw-bold">{{ $dtr->total_late_minutes }}</td>
                            <td class="text-warning fw-bold">{{ $dtr->total_undertime_minutes }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3 text-center d-none d-print-block">
                    <div class="mt-4 border-top pt-2 small fw-bold">Employee Signature</div>
                </div>
                <div class="col-md-3 text-center d-none d-print-block">
                    <div class="mt-4 border-top pt-2 small fw-bold">Admin/Supervisor Approval</div>
                </div>
                <div class="col-md-6 text-end no-print">
                    <div class="mt-2 text-end">
                        @if($dtr->status == 'draft')
                            <button type="button" class="btn btn-info btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#verifyDtrModal">
                                <i class="bi bi-shield-check me-1"></i> Verify DTR
                            </button>
                        @elseif($dtr->status == 'verified')
                            <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#finalizeDtrModal">
                                <i class="bi bi-lock-fill me-1"></i> Finalize DTR
                            </button>
                        @else
                            <span class="badge bg-success p-2"><i class="bi bi-check-circle-fill me-1"></i> RECORD FINALIZED</span>
                        @endif
                        <button type="button" class="btn btn-dark btn-sm fw-bold ms-1" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print DTR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div class="modal fade" id="verifyDtrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Verify DTR Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dtrs.verify', $dtr->id) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p>You are about to verify the DTR for <strong>{{ $dtr->employee->full_name }}</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Enter Security Password</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info px-4">Confirm Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Finalize Modal -->
<div class="modal fade" id="finalizeDtrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Finalize & Lock DTR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dtrs.finalize', $dtr->id) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle-fill"></i> Finalizing will lock this record for payroll processing. 
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Enter Security Password</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Finalize Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
