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

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Assign to Sites (Optional)</label>
                            <div class="p-3 border rounded-1 bg-light" style="max-height: 200px; overflow-y: auto;">
                                @foreach($sites as $site)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="site_ids[]" value="{{ $site->id }}" id="site_{{ $site->id }}">
                                        <label class="form-check-label small" for="site_{{ $site->id }}">
                                            {{ $site->name }} 
                                            @if($site->scheduleGroup)
                                                <span class="text-muted italic">(Currently: {{ $site->scheduleGroup->name }})</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">You can select one or more sites to immediately apply this blueprint to.</small>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right-circle-fill"></i> Save & Plot Schedule
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
