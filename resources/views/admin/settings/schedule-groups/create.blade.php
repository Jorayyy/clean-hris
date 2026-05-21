@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border shadow-sm">
                <!-- Header with Back Arrow Icon -->
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        Mancao Electronic Connect Business Solutions OPC 
                        <span class="text-muted fw-normal small">(add group)</span>
                    </h6>
                    <a href="{{ route('admin.settings.schedule-groups.index') }}" class="text-secondary fs-4">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.schedule-groups.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Group name</label>
                            <input type="text" name="name" class="form-control rounded-1 @error('name') is-invalid @enderror" placeholder="group name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-danger px-4 d-inline-flex align-items-center gap-2" style="background-color: #d9534f; border: none;">
                                <i class="bi bi-floppy-fill"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Mimicking the simple, boxed look from the image */
.form-control::placeholder { color: #ccc; }
.card-header h6 { font-size: 0.95rem; }
</style>
@endsection
