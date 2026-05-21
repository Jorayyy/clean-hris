@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Individual Schedules</h4>
            <p class="text-muted small">Personalized schedule overrides that bypass Site Blueprints.</p>
        </div>
        <div>
            <a href="{{ route('admin.settings.individual-schedules.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-person-plus-fill me-2"></i> Create Override
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
            {{ session('info') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-dark fw-bold small uppercase">
                        <tr>
                            <th class="ps-4 py-3">Employee</th>
                            <th class="py-3">Current Site</th>
                            <th class="py-3">Schedule Status</th>
                            <th class="py-3 text-end pe-4">Actions</th>
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
                                <span class="badge bg-light text-dark border fw-normal">{{ $employee->site->name ?? 'Unassigned' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-warning fw-normal">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Individual Override Active
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.settings.individual-schedules.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary rounded-circle p-2" title="Edit Override">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('admin.settings.individual-schedules.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove override? This employee will revert to their Site Schedule.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Delete Override">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-5 text-center text-muted italic">
                                <div class="mb-3"><i class="bi bi-person-check" style="font-size: 3rem;"></i></div>
                                No individual overrides found. All employees are currently following their <strong>Site Schedules</strong>.
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
