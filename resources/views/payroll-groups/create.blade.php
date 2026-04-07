@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">Create Payroll Group</div>
            <div class="card-body">
                <form action="{{ route('payroll-groups.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Group Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Regular Staff" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Group</button>
                    <a href="{{ route('payroll-groups.index') }}" class="btn btn-link w-100 text-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
