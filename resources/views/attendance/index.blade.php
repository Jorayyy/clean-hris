@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Attendance Management</h4>
            <p class="text-muted small mb-0">Monitor and manage employee daily attendance records</p>
        </div>
        <a href="{{ route('attendance.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span>Add Attendance</span>
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded text-primary">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Total Records</p>
                            <h5 class="fw-bold mb-0">{{ $attendances->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded text-success">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Perfect Attendance</p>
                            <h5 class="fw-bold mb-0">{{ $attendances->where('late_minutes', 0)->where('undertime_minutes', 0)->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-10 p-3 rounded text-danger">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Late Cases</p>
                            <h5 class="fw-bold mb-0">{{ $attendances->where('late_minutes', '>', 0)->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded text-warning">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Undertime</p>
                            <h5 class="fw-bold mb-0">{{ $attendances->where('undertime_minutes', '>', 0)->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <form action="{{ route('attendance.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <h5 class="card-title fw-bold mb-0">Historical Logs</h5>
                </div>
                <div class="col-md-3 ms-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <input type="date" name="date" class="form-control bg-light border-start-0" value="{{ request('date') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-dark px-3">Filter</button>
                    @if(request('date'))
                        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Schedule</th>
                            <th>Date</th>
                            <th>Time In / Out</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $row)
                        @php
                            $sched = $row->employee->active_schedule;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-light text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ substr($row->employee->first_name, 0, 1) }}{{ substr($row->employee->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $row->employee->full_name }}</div>
                                        <div class="text-muted small">{{ $row->employee->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($sched)
                                    <div class="badge border border-info text-info fw-normal">
                                        {{ date('h:i A', strtotime($sched->time_in)) }} - {{ date('h:i A', strtotime($sched->time_out)) }}
                                    </div>
                                @else
                                    <span class="text-muted small italic">Standard 8-5</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ date('M d, Y', strtotime($row->date)) }}</div>
                                <div class="text-muted small">{{ date('l', strtotime($row->date)) }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-success"><i class="bi bi-arrow-right-short"></i> {{ date('h:i A', strtotime($row->time_in)) }}</span>
                                    <span class="text-danger"><i class="bi bi-arrow-left-short"></i> {{ date('h:i A', strtotime($row->time_out)) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ number_format($row->total_hours, 2) }} <small class="text-muted fw-normal">hrs</small></div>
                            </td>
                            <td>
                                @if($row->late_minutes > 0 || $row->undertime_minutes > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($row->late_minutes > 0)
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">
                                                Late: {{ $row->late_minutes }}m
                                            </span>
                                        @endif
                                        @if($row->undertime_minutes > 0)
                                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning">
                                                UT: {{ $row->undertime_minutes }}m
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success">
                                        <i class="bi bi-check2-circle me-1"></i> Perfect
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                                        <li>
                                            <form action="{{ route('attendance.destroy', $row->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Archive this record?')">
                                                    <i class="bi bi-trash me-2"></i> Delete Record
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-25" alt="No data">
                                <p class="text-muted">No attendance records found for this selection.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ensure the table doesn't clip the dropdown */
    .table-responsive {
        overflow: visible !important;
    }
    .bg-danger-subtle { background-color: #fee2e2; }
    .bg-warning-subtle { background-color: #fef3c7; }
    .bg-success-subtle { background-color: #dcfce7; }
    .text-warning-emphasis { color: #92400e; }
    .avatar-circle { font-size: 0.85rem; border: 1px solid #e2e8f0; }
    .table thead th { 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 0.025em; 
        font-weight: 700; 
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }
</style>
@endsection
