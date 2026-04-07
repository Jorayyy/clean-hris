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
                        <th>Schedule</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours</th>
                        <th>Late/UT</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $row)
                    @php
                        $sched = $row->employee->active_schedule;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $row->employee->full_name }}</strong><br>
                            <small class="text-muted">{{ $row->employee->employee_id }}</small>
                        </td>
                        <td>
                            @if($sched)
                                <small>{{ $sched->time_in }} - {{ $sched->time_out }}</small>
                            @else
                                <small class="text-muted">Standard 8-5</small>
                            @endif
                        </td>
                        <td>{{ $row->date }}</td>
                        <td>{{ date('h:i A', strtotime($row->time_in)) }}</td>
                        <td>{{ date('h:i A', strtotime($row->time_out)) }}</td>
                        <td>{{ number_format($row->total_hours, 2) }}</td>
                        <td>
                            @if($row->late_minutes > 0)
                                <span class="badge bg-danger">L:{{ $row->late_minutes }}m</span>
                            @endif
                            @if($row->undertime_minutes > 0)
                                <span class="badge bg-warning text-dark">U:{{ $row->undertime_minutes }}m</span>
                            @endif
                            @if(!$row->late_minutes && !$row->undertime_minutes)
                                <span class="badge bg-success">Perfect</span>
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
