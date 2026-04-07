@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">Create Attendance Record</div>
            <div class="card-body">
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Time In</label>
                            <input type="time" name="time_in" class="form-control" value="08:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time Out</label>
                            <input type="time" name="time_out" class="form-control" value="17:00" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Save DTR</button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-link w-100 text-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
