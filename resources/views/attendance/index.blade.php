@extends("layouts.app")

@section("content")
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Attendance Management</h4>
            <p class="text-muted small mb-0">Select an employee to view their attendance history</p>
        </div>
        <a href="{{ route("attendance.create") }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm rounded-pill px-4">
            <i class="bi bi-plus-circle"></i>
            <span>Manual Entry</span>
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 font-monospace">TOTAL EMPLOYEES</p>
                            <h5 class="fw-bold mb-0 count-up">{{ $employees->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success">
                            <i class="bi bi-person-check-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 font-monospace">LOGGED TODAY</p>
                            <h5 class="fw-bold mb-0">{{ $employees->where("attendances_count", ">", 0)->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger">
                            <i class="bi bi-person-x-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 font-monospace">OFFLINE (ACTIVE)</p>
                            <h5 class="fw-bold mb-0">{{ $employees->where("status", "active")->where("attendances_count", 0)->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <form action="{{ route("attendance.index") }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <h5 class="card-title fw-bold mb-0">Employee Directory</h5>
                </div>
                <div class="col-md-4 ms-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="bi bi-search text-muted small"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-0" 
                               placeholder="Search name or ID..." value="{{ request("search") }}">
                        @if(request("search"))
                            <a href="{{ route("attendance.index") }}" class="btn btn-light border-0">
                                <i class="bi bi-x-circle text-muted"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-dark px-4">Search</button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Employee Details</th>
                            <th>Status (Today)</th>
                            <th>Info</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-primary bg-opacity-10 text-primary fw-bold rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 0.9rem; border: 1px solid rgba(13, 110, 253, 0.1);">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $employee->full_name }}</div>
                                        <div class="text-muted small font-monospace">{{ $employee->employee_id }} <i class="bi bi-dot"></i> {{ $employee->position }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($employee->attendances_count > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 border border-success border-opacity-25">
                                        <i class="bi bi-dot fs-4 align-middle"></i> LOGGED IN
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill px-3 border border-secondary border-opacity-10 fw-normal">
                                        NO LOGS
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium">{{ $employee->employment_type ?? "N/A" }}</span>
                                    <span class="text-muted x-small">{{ $employee->classification ?? "No Class" }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route("attendance.show", $employee->id) }}" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3">
                                    View Logs <i class="bi bi-chevron-right ms-1 small"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-secondary opacity-50">
                                    <i class="bi bi-search fs-1 mb-2 d-block"></i>
                                    <p class="mb-0">No matching employees found.</p>
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

<style>
    .x-small { font-size: 0.7rem; }
    .font-monospace { letter-spacing: -0.5px; }
    .btn-white { background-color: #fff; color: #212529; }
    .btn-white:hover { background-color: #f8f9fa; border-color: #dee2e6; }
    .avatar-circle { transition: all 0.2s ease; }
    tr:hover .avatar-circle { transform: scale(1.05); }
    .table td { border-bottom: 1px solid #f8f9fa; }
</style>
@endsection
