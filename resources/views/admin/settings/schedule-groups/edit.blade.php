@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm p-4" style="background-color: #fdfdfd;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Edit Group Name</h5>
                    <a href="{{ route('admin.settings.schedule-groups.index') }}" class="text-secondary fs-4">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                    </a>
                </div>
                <form action="{{ route('admin.settings.schedule-groups.update', $scheduleGroup->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">Group name</label>
                        <input type="text" name="name" class="form-control rounded-0 @error('name') is-invalid @enderror" value="{{ old('name', $scheduleGroup->name) }}" style="border: 1px solid #ddd; padding: 10px;" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-danger px-4 rounded-1 d-inline-flex align-items-center gap-2" style="background-color: #d9534f; border: none; padding: 8px 20px;">
                            <i class="bi bi-floppy"></i> Modify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
