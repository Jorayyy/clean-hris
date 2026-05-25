@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Group Members</h4>
            <p class="text-muted small">Employees assigned to <strong>{{ $scheduleGroup->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-person-plus-fill me-2"></i> Add Employee
            </button>
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
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-light border text-primary rounded-pill px-3">
                                        View Profile
                                    </a>
                                    <form action="{{ route('admin.settings.schedule-groups.remove-member', [$scheduleGroup->id, $employee->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this employee from the group?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            Remove
                                        </button>
                                    </form>
                                </div>
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

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addEmployeeModalLabel">Assign Employee to Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.settings.schedule-groups.add-member', $scheduleGroup->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">1. Select Employee</label>
                        <select name="employee_id" class="form-select border-primary" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($unassignedEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small italic">This will assign the schedule group directly to this employee.</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">2. Update Site (Optional)</label>
                        <select name="site_id" class="form-select border-primary">
                            <option value="">-- No Change --</option>
                            @foreach($allSites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Assign to Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection