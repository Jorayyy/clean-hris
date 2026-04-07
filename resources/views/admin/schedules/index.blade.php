@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Employee Schedules</h2>
        <a href="{{ route('schedules.create') }}" class="btn btn-primary">Create New Schedule</a>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Schedule Name</th>
                            <th>Target</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Days</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $s)
                        <tr>
                            <td>{{ $s->name ?? 'Regular Shift' }}</td>
                            <td>
                                @if($s->employee)
                                    <span class="badge bg-info">Individual: {{ $s->employee->full_name }}</span>
                                @elseif($s->payrollGroup)
                                    <span class="badge bg-secondary">Group: {{ $s->payrollGroup->name }}</span>
                                @endif
                            </td>
                            <td>{{ $s->time_in }}</td>
                            <td>{{ $s->time_out }}</td>
                            <td>
                                <small>{{ is_array($s->days) ? implode(', ', $s->days) : $s->days }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('schedules.edit', $s->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this schedule?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No schedules found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
