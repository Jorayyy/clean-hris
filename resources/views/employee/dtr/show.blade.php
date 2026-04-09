@extends('layouts.app')

@section('content')
<style>
    .dtr-header { background: #003366; color: white; font-size: 0.8rem; }
    .dtr-table th { background: #005a9c; color: white; font-size: 0.75rem; text-transform: uppercase; border: 1px solid #dee2e6; vertical-align: middle; text-align: center; }
    .dtr-table td { font-size: 0.75rem; border: 1px solid #dee2e6; vertical-align: middle; padding: 4px; }
    .bg-dtr-label { background: #f8f9fa; font-weight: bold; width: 15%; }
    .bg-dtr-value { background: #ffffff; width: 18%; }
    .footer-summary { background: #e9ecef; border-top: 2px solid #003366; }
</style>

<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('employee.dtr.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3 fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Back to History
    </a>
    <h5 class="fw-bold mb-0 text-dark">DTR Details for {{ $dtr->employee->full_name }}</h5>
</div>

<div class="card shadow border-0 mb-4">
    <div class="card-body p-0 shadow-sm border rounded overflow-hidden">
        <!-- HEADER INFO -->
        <div class="row g-0 border-bottom dtr-header p-2 px-3 fw-bold">
            <div class="col-md-6 text-uppercase">Salary Rate: {{ $dtr->employee->salary_type }} ({{ number_format($dtr->employee->daily_rate, 2) }})</div>
            <div class="col-md-6 text-end">Date Employed: {{ $dtr->employee->date_hired ?? 'N/A' }}</div>
        </div>
        
        <table class="table table-bordered mb-0">
            <tr>
                <td class="bg-dtr-label text-muted small">Payroll Period</td>
                <td class="bg-dtr-value text-primary fw-bold">{{ $dtr->start_date->format('Y-m-d') }} to {{ $dtr->end_date->format('Y-m-d') }}</td>
                <td class="bg-dtr-label text-muted small">Department</td>
                <td class="bg-dtr-value">{{ $dtr->employee->department ?? 'N/A' }}</td>
                <td class="bg-dtr-label text-muted small">Employment</td>
                <td class="bg-dtr-value">{{ $dtr->employee->employment_status ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bg-dtr-label text-muted small">Employee ID</td>
                <td class="bg-dtr-value fw-bold">{{ $dtr->employee->employee_id }}</td>
                <td class="bg-dtr-label text-muted small">Section</td>
                <td class="bg-dtr-value">{{ $dtr->employee->section ?? 'N/A' }}</td>
                <td class="bg-dtr-label text-muted small">Classification</td>
                <td class="bg-dtr-value">STAFF</td>
            </tr>
            <tr>
                <td class="bg-dtr-label text-muted small">Name</td>
                <td class="bg-dtr-value fw-bold">{{ $dtr->employee->full_name }}</td>
                <td colspan="2"></td>
                <td class="bg-dtr-label text-muted small">Pay Type</td>
                <td class="bg-dtr-value">WEEKLY</td>
            </tr>
            <tr>
                <td class="bg-dtr-label text-muted small">Position</td>
                <td class="bg-dtr-value">{{ $dtr->employee->position }}</td>
                <td colspan="2"></td>
                <td class="bg-dtr-label text-muted small">Location</td>
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
                        <th rowspan="2" class="bg-danger text-white border-0">Shift Time<br><small>IN | OUT</small></th>
                        <th rowspan="2" class="bg-success text-white border-0">Actual Time<br><small>IN | OUT</small></th>
                        <th colspan="3">No. of Hours</th>
                        <th colspan="6">Overtime</th>
                        <th colspan="3">Filed Forms</th>
                    </tr>
                    <tr>
                        <th class="small">Late</th>
                        <th class="small">Over<br>Break</th>
                        <th class="small">UT</th>
                        <th class="small">Reg</th>
                        <th class="small">RD</th>
                        <th class="small">Holiday</th>
                        <th class="small">RD<br>Hol</th>
                        <th class="small">ND</th>
                        <th class="small">ATRO</th>
                        <th class="small">OB</th>
                        <th class="small">Leave</th>
                        <th class="small">UT</th>
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
                            <td class="fw-bold bg-light">{{ $date->format('m-d') }}</td>
                            <td class="bg-light">{{ $date->format('D') }}</td>
                            <td class="text-danger fw-bold" style="background-color: #fff0f0;">08:00 | 17:00</td>
                            <td class="text-success fw-bold" style="background-color: #f0fff0;">
                                {{ $log ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : '--:--' }} | {{ ($log && $log->time_out) ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : '--:--' }}
                            </td>
                            <td class="{{ ($log && $log->late_minutes > 0) ? 'text-danger fw-bold' : '' }}">
                                {{ ($log && $log->late_minutes > 0) ? $log->late_minutes : '' }}
                            </td>
                            <td></td>
                            <td class="{{ ($log && $log->undertime_minutes > 0) ? 'text-warning fw-bold' : '' }}">
                                {{ ($log && $log->undertime_minutes > 0) ? $log->undertime_minutes : '' }}
                            </td>
                            <td class="text-primary fw-bold">{{ $log ? '8.00' : '' }}</td>
                            <td></td>
                            <td class="text-center">
                                @if($date->isWeekend())
                                    <i class="bi bi-check2 text-primary"></i>
                                @endif
                            </td>
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered bg-white small mb-0">
                        <tr class="fw-bold bg-light">
                            <td width="40%">Description</td>
                            <td width="15%">Regular</td>
                            <td width="15%">OT</td>
                            <td width="15%">Late (m)</td>
                            <td width="15%">UT (m)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Totals</td>
                            <td class="fw-bold">{{ $dtr->total_regular_hours }}</td>
                            <td>0.00</td>
                            <td class="text-danger fw-bold">{{ $dtr->total_late_minutes }}</td>
                            <td class="text-warning fw-bold">{{ $dtr->total_undertime_minutes }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-end">
                    <div class="mt-2">
                        @if($dtr->status == 'finalized')
                             <span class="badge bg-success p-2 mb-2"><i class="bi bi-check-circle-fill me-1"></i> RECORD FINALIZED</span><br>
                        @endif
                        <button class="btn btn-dark btn-sm fw-bold ms-1 shadow-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print DTR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
