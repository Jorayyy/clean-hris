@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-800 tracking-tight text-primary mb-1">Edit Announcement</h4>
        <p class="text-muted mb-0 small"><a href="{{ route('announcements.index') }}" class="text-decoration-none">← Back to List</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <form action="{{ route('announcements.update', $announcement->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Announcement Title</label>
                    <input type="text" name="title" class="form-control form-control-lg rounded-3 border-light-subtle shadow-sm @error('title') is-invalid @enderror" placeholder="Ex: System Maintenance Schedule" value="{{ old('title', $announcement->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Alert Type</label>
                        <select name="type" class="form-select rounded-3 border-light-subtle shadow-sm @error('type') is-invalid @enderror" required>
                            <option value="info" {{ $announcement->type == 'info' ? 'selected' : '' }}>Info (Default - Blue)</option>
                            <option value="warning" {{ $announcement->type == 'warning' ? 'selected' : '' }}>Warning (Important - Yellow)</option>
                            <option value="danger" {{ $announcement->type == 'danger' ? 'selected' : '' }}>Urgent (Critical - Red)</option>
                            <option value="success" {{ $announcement->type == 'success' ? 'selected' : '' }}>Success (Celebration - Green)</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $announcement->is_active ? 'checked' : '' }}>
                            <label class="form-check-label ms-2 small fw-bold text-dark" for="isActive">Currently Published</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Message Content</label>
                    <textarea name="content" rows="6" class="form-control rounded-3 border-light-subtle shadow-sm @error('content') is-invalid @enderror" placeholder="Write your full message here..." required>{{ old('content', $announcement->content) }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-save-fill me-2"></i>Update Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 bg-light p-4 h-100">
            <h6 class="fw-800 text-dark mb-3"><i class="bi bi-calendar3 me-2"></i>Record Details</h6>
            <ul class="text-muted small lh-lg">
                <li><strong>Created on:</strong> {{ $announcement->created_at->format('M d, Y h:i A') }}</li>
                <li><strong>Last updated:</strong> {{ $announcement->updated_at->format('M d, Y h:i A') }}</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-4 { border-radius: 1rem !important; }
    .border-light-subtle { border-color: #f1f5f9 !important; }
</style>
@endsection