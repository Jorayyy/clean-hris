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
                        <th>Group</th>
                        <th>Daily Rate</th>
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
                        <td>{{ $employee->payrollGroup->name ?? 'None' }}</td>
                        <td>{{ number_format($employee->daily_rate, 2) }}</td>
                        <td>
                            <span class="badge {{ $employee->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Archive?')" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
