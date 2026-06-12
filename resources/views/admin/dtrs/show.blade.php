@extends('layouts.app')

@section('content')
@php
    if (!function_exists('formatDtrMinutes')) {
        function formatDtrMinutes($totalMinutes) {
            $totalMinutes = (int) $totalMinutes;
            if ($totalMinutes <= 0) return '-';
            
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            
            $parts = [];
            if ($hours > 0) $parts[] = $hours . 'h';
            if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';
            
            return implode(' ', $parts);
        }
    }
@endphp
<style>
    .dtr-table th { 
        background: #3498db !important; 
        color: white !important; 
        font-size: 11px; 
        font-weight: bold; 
        border: 1px solid #ddd; 
        vertical-align: middle; 
        text-align: center; 
        padding: 6px !important;
        -webkit-print-color-adjust: exact;
    }
    .dtr-table td { 
        font-size: 12px; 
        font-weight: 600; 
        border: 1px solid #ddd; 
        vertical-align: middle; 
        padding: 5px; 
        text-align: center;
        color: #2c3e50;
    }
    .bg-shift { background-color: #ffe4e1 !important; color: #2c3e50 !important; } /* Very Soft Pink */
    .bg-actual { background-color: #e0f2f1 !important; color: #2c3e50 !important; } /* Very Soft Teal/Green */
    .text-absent { color: #e74c3c; font-weight: 800; font-size: 10px; }
    .bg-dtr-label { background: #3498db !important; color: white !important; font-weight: bold; width: 14%; border: 1px solid #ddd; font-size: 12px; }
    .bg-dtr-value { background: #ffffff; width: 19.33%; border: 1px solid #ddd; font-size: 12px; font-weight: 600; color: #2c3e50; }
    
    @media print {
        @page { size: landscape; margin: 0.5cm; }
        .no-print, .btn, .mb-3.d-flex { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
        body { font-size: 9pt; }
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
                <td class="bg-dtr-label">Position</td>
                <td class="bg-dtr-value">{{ $dtr->employee->position }}</td>
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
                        <th rowspan="2" style="width: 50px;">Day</th>
                        <th colspan="2">Shift Time</th>
                        <th colspan="2">Actual Time</th>
                        <th colspan="4">No. of Hours</th>
                        <th colspan="5">Overtime</th>
                        <th colspan="4">Filed Forms</th>
                    </tr>
                    <tr>
                        <th class="bg-shift text-dark" style="width: 50px;">IN</th>
                        <th class="bg-shift text-dark" style="width: 50px;">OUT</th>
                        <th class="bg-actual text-dark" style="width: 50px;">IN</th>
                        <th class="bg-actual text-dark" style="width: 50px;">OUT</th>
                        <th style="width: 60px;">Late</th>
                        <th style="width: 60px;">Break</th>
                        <th style="width: 60px;">UT</th>
                        <th style="width: 60px;">Reg</th>
                        <th style="width: 60px;">RD</th>
                        <th style="width: 60px;">Hol</th>
                        <th style="width: 60px;">RDH</th>
                        <th style="width: 60px;">ND</th>
                        <th style="width: 60px;">OT</th>
                        <th style="width: 60px;">OB</th>
                        <th style="width: 60px;">LV</th>
                        <th style="width: 60px;">UT</th>
                        <th style="width: 60px;">OT</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $period = \Carbon\CarbonPeriod::create($dtr->start_date, $dtr->end_date);
                    @endphp
                    @foreach($period as $date)
                        @php
                            $dateStr = $date->format('Y-m-d');
                            $log = $attendances->first(function($a) use ($dateStr) {
                                $aDate = is_string($a->date) ? $a->date : $a->date->format('Y-m-d');
                                return $aDate == $dateStr;
                            });
                            $sched = $dtr->employee->getScheduleForDate($dateStr);
                            $isAbsent = (!$log && $sched && !$sched->is_rest_day);
                            $isRestDay = ($sched && $sched->is_rest_day) || (!$sched);
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $date->format('m-d') }}</td>
                            <td>{{ $date->format('D') }}</td>
                            
                            <!-- SHIFT TIME -->
                            <td class="bg-shift">
                                {{ ($sched && $sched->time_in) ? \Carbon\Carbon::parse($sched->time_in)->format('H:i') : '-' }}
                            </td>
                            <td class="bg-shift">
                                {{ ($sched && $sched->time_out) ? \Carbon\Carbon::parse($sched->time_out)->format('H:i') : '-' }}
                            </td>
                            
                            <!-- ACTUAL TIME -->
                            <td class="bg-actual">
                                {{ ($log && $log->time_in && $log->time_in !== '00:00:00') ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : '-' }}
                            </td>
                            <td class="bg-actual {{ ($log && $log->time_in && $log->time_in !== '00:00:00' && (!$log->time_out || $log->time_out == '00:00:00')) ? 'bg-danger-subtle text-danger fw-bold' : '' }}">
                                @if($log && $log->time_in && $log->time_in !== '00:00:00' && (!$log->time_out || $log->time_out == '00:00:00'))
                                    <i class="bi bi-exclamation-triangle-fill"></i> MISSING
                                @else
                                    {{ ($log && $log->time_out && $log->time_out !== '00:00:00') ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : '-' }}
                                @endif
                            </td>

                            <!-- NO OF HOURS -->
                            <td class="{{ ($log && $log->late_minutes > 0) ? 'text-danger fw-bold' : '' }}">
                                {{ ($log && $log->late_minutes > 0) ? formatDtrMinutes($log->late_minutes) : '-' }}
                            </td>
                            <td>-</td>
                            <td class="{{ ($log && $log->undertime_minutes > 0) ? 'text-warning fw-bold' : '' }}">
                                {{ ($log && $log->undertime_minutes > 0) ? formatDtrMinutes($log->undertime_minutes) : '-' }}
                            </td>
                            <td class="fw-bold">
                                @if($isAbsent) <span class="text-absent">ABSENT</span> 
                                @else {{ ($log && $log->total_hours > 0) ? number_format($log->total_hours, 2) : '-' }} @endif
                            </td>

                            <!-- OVERTIME -->
                            <td>{{ ($log && $log->overtime_hours > 0 && $isRestDay) ? number_format($log->overtime_hours, 2) : '-' }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>{{ ($log && $log->overtime_hours > 0 && !$isRestDay) ? number_format($log->overtime_hours, 2) : '-' }}</td>

                            <!-- FILED FORMS -->
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                                @if($log)
                                    <form action="{{ route('admin.attendances.toggle-ot', $log->id) }}" method="POST" class="no-print d-inline">
                                        @csrf @method('PATCH')
                                        <input type="checkbox" class="form-check-input" onChange="this.form.submit()" {{ $log->ot_authorized ? 'checked' : '' }}>
                                    </form>
                                    <span class="d-none d-print-inline">{{ $log->ot_authorized ? 'YES' : '-' }}</span>
                                @else
                                    -
                                @endif
                            </td>
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
                            <td>Late</td>
                            <td>UT</td>
                        </tr>
                        <tr>
                            <td>Totals</td>
                            <td class="fw-bold">{{ number_format($dtr->total_regular_hours, 2) }}</td>
                            <td class="fw-bold">{{ number_format($dtr->total_overtime_hours, 2) }}</td>
                            <td class="text-danger fw-bold">{{ formatDtrMinutes($dtr->total_late_minutes) }}</td>
                            <td class="text-warning fw-bold">{{ formatDtrMinutes($dtr->total_undertime_minutes) }}</td>
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
                            <button type="button" class="btn btn-info btn-sm fw-bold" 
                                @if($dtr->total_regular_hours <= 0) disabled title="Empty records cannot be verified" @else data-bs-toggle="modal" data-bs-target="#verifyDtrModal" @endif>
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
