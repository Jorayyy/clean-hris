@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">{{ $employee->full_name }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Attendance Logs</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('attendance.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i>
                <span>Manual Entry</span>
            </a>
        </div>
    </div>

    <!-- Employee Profile Header -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="d-flex align-items-stretch">
                <div class="bg-primary p-4 d-flex align-items-center justify-content-center" style="width: 120px;">
                    <div class="avatar-circle bg-white text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 70px; height: 70px; font-size: 1.5rem;">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                </div>
                <div class="p-4 flex-grow-1">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-1">{{ $employee->full_name }}</h5>
                            <p class="text-muted small mb-0"><i class="bi bi-card-text me-1"></i> ID: {{ $employee->employee_id }} | <i class="bi bi-briefcase me-1"></i> {{ $employee->position }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('attendance.show', $employee->id) }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="form-label mb-0 fw-bold"><i class="bi bi-filter me-1"></i> Filter Date:</label>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-dark px-4">Apply</button>
                    @if(request('date'))
                        <a href="{{ route('attendance.show', $employee->id) }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Daily Logs for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4">Time In</th>
                            <th>Time Out</th>
                            <th>Total Hours</th>
                            <th>Late (min)</th>
                            <th>Undertime (min)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $row)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 bg-success bg-opacity-10 rounded text-success">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </div>
                                    <span class="fw-bold">{{ date('h:i A', strtotime($row->time_in)) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 bg-danger bg-opacity-10 rounded text-danger">
                                        <i class="bi bi-box-arrow-left"></i>
                                    </div>
                                    <span class="fw-bold">{{ date('h:i A', strtotime($row->time_out)) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark fw-normal border">
                                    {{ number_format($row->total_hours, 2) }} hrs
                                </span>
                            </td>
                            <td>
                                @if($row->late_minutes > 0)
                                    <span class="text-danger fw-bold"><i class="bi bi-clock-history me-1"></i>{{ $row->late_minutes }}m</span>
                                @else
                                    <span class="text-success small">--</span>
                                @endif
                            </td>
                            <td>
                                @if($row->undertime_minutes > 0)
                                    <span class="text-warning fw-bold"><i class="bi bi-hourglass-split me-1"></i>{{ $row->undertime_minutes }}m</span>
                                @else
                                    <span class="text-success small">--</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm shadow-sm">
                                    <a href="{{ route('attendance.edit', $row->id) }}" class="btn btn-white" title="Edit">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('attendance.destroy', $row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-white" title="Delete">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x fs-1 mb-3 d-block"></i>
                                    <p class="mb-0">No attendance records found for this date.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
