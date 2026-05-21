@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Group Members</h4>
            <p class="text-muted small">Employees assigned to <strong>{{ $scheduleGroup->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-person-plus-fill me-2"></i> Add Employee
            </a>
            <a href="{{ route('admin.settings.schedule-groups.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i> Back to Groups
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-dark fw-bold small uppercase">
                        <tr>
                            <th class="ps-4 py-3">Employee Name</th>
                            <th class="py-3">Designated Site</th>
                            <th class="py-3">Current Status</th>
                            <th class="py-3 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-weight: bold;">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold text-dark">{{ $employee->full_name }}</span>
                                        <span class="small text-muted">{{ $employee->employee_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-normal">{{ $employee->site->name ?? 'No Site' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $employee->status === 'Active' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $employee->status === 'Active' ? 'success' : 'danger' }} fw-normal">
                                    {{ $employee->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-light border text-primary rounded-pill px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-5 text-center text-muted italic">
                                No employees are currently assigned to this group's sites.
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