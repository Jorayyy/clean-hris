@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center text-white bg-dark">
        <h5>Attendance List</h5>
        <a href="{{ route('attendance.create') }}" class="btn btn-primary btn-sm">Add DTR</a>
    </div>
    <div class="card-body">
        <form class="row mb-3" method="GET">
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours</th>
                        <th>Late/Undertime</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $row)
                    <tr>
                        <td>{{ $row->employee->full_name }}</td>
                        <td>{{ $row->date }}</td>
                        <td>{{ $row->time_in }}</td>
                        <td>{{ $row->time_out }}</td>
                        <td>{{ number_format($row->total_hours, 2) }}</td>
                        <td>
                            @if($row->late_minutes > 0)
                                <span class="text-danger small">L:{{ $row->late_minutes }}m </span>
                            @endif
                            @if($row->undertime_minutes > 0)
                                <span class="text-danger small">U:{{ $row->undertime_minutes }}m</span>
                            @endif
                            @if(!$row->late_minutes && !$row->undertime_minutes)
                                Perfect
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('attendance.destroy', $row->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove?')">X</button>
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
