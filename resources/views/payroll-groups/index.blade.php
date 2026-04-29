@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payroll Groups</h5>
        @if(auth()->user()->role === 'super-admin')
            <a href="{{ route('payroll-groups.create') }}" class="btn btn-primary btn-sm">Add Group</a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Employees</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                    <tr>
                        <td><strong>{{ $group->name }}</strong></td>
                        <td>{{ $group->description }}</td>
                        <td>{{ $group->employees_count }}</td>
                        <td>
                            @if(auth()->user()->role === 'super-admin')
                                <a href="{{ route('payroll-groups.edit', $group->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('payroll-groups.destroy', $group->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Delete group?')">Delete</button>
                                </form>
                            @else
                                <span class="badge bg-secondary">View Only</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
