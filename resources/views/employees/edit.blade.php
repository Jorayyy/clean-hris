@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($employee) ? 'Edit' : 'Add' }} Employee</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($employee) ? route('employees.update', $employee->id) : route('employees.store') }}" method="POST">
                    @csrf
                    @if(isset($employee)) @method('PUT') @endif
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" value="{{ $employee->employee_id ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $employee->email ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $employee->first_name ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $employee->last_name ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control" value="{{ $employee->position ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Daily Rate</label>
                            <input type="number" name="daily_rate" class="form-control" value="{{ $employee->daily_rate ?? '' }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Employee</button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
