@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Employee List</h5>
        <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">Add Employee</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Site</th>
                        <th>Group</th>
                        @php
                            $user = auth()->user();
                            $isAuthorized = $user->is_super_admin || $user->hasRole('Accounting Admin');
                        @endphp
                        @if($isAuthorized)
                            <th>Daily Rate</th>
                        @endif
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->position }}</td>
                        <td>
                            <span class="badge bg-light text-dark border shadow-xs">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $employee->site->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td>{{ $employee->payrollGroup->name ?? 'None' }}</td>
                        @if($isAuthorized)
                            <td>{{ number_format($employee->daily_rate, 2) }}</td>
                        @endif
                        <td>
                            <span class="badge {{ $employee->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-outline-info shadow-sm" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="Edit Employee">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('Archive employee?')" type="submit" title="Delete">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
