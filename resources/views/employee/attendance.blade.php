@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <h3 class="fw-bold">My Attendance (DTR)</h3>
        <p class="text-muted small">View your detailed time records below.</p>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <form action="{{ route('employee.attendance') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Morning In</th>
                                <th>Lunch Out</th>
                                <th>Lunch In</th>
                                <th>Afternoon Out</th>
                                <th>OT Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $record)
                                <tr>
                                    <td>{{ $record->date }}</td>
                                    <td>{{ date('D', strtotime($record->date)) }}</td>
                                    <td>{{ $record->am_in ? date('h:i A', strtotime($record->am_in)) : '-' }}</td>
                                    <td>{{ $record->am_out ? date('h:i A', strtotime($record->am_out)) : '-' }}</td>
                                    <td>{{ $record->pm_in ? date('h:i A', strtotime($record->pm_in)) : '-' }}</td>
                                    <td>{{ $record->pm_out ? date('h:i A', strtotime($record->pm_out)) : '-' }}</td>
                                    <td class="text-primary fw-bold">{{ $record->overtime_hours > 0 ? $record->overtime_hours . ' hrs' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No attendance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $attendances->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
