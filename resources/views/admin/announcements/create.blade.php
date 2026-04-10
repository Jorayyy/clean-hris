@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-800 tracking-tight text-primary mb-1">Create New Announcement</h4>
        <p class="text-muted mb-0 small"><a href="{{ route('announcements.index') }}" class="text-decoration-none">← Back to List</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <form action="{{ route('announcements.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Announcement Title</label>
                    <input type="text" name="title" class="form-control form-control-lg rounded-3 border-light-subtle shadow-sm @error('title') is-invalid @enderror" placeholder="Ex: System Maintenance Schedule" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Alert Type</label>
                        <select name="type" class="form-select rounded-3 border-light-subtle shadow-sm @error('type') is-invalid @enderror" required>
                            <option value="info">Info (Default - Blue)</option>
                            <option value="warning">Warning (Important - Yellow)</option>
                            <option value="danger">Urgent (Critical - Red)</option>
                            <option value="success">Success (Celebration - Green)</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                            <label class="form-check-label ms-2 small fw-bold text-dark" for="isActive">Publish Immediately</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">Message Content</label>
                    <textarea name="content" rows="6" class="form-control rounded-3 border-light-subtle shadow-sm @error('content') is-invalid @enderror" placeholder="Write your full message here..." required>{{ old('content') }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Post Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 bg-light p-4 h-100">
            <h6 class="fw-800 text-dark mb-3"><i class="bi bi-eye-fill me-2"></i>Quick Tips</h6>
            <ul class="text-muted small lh-lg">
                <li><strong>Info:</strong> Regular updates like policy changes or general information.</li>
                <li><strong>Warning:</strong> Use for reminders like DTR deadlines or payroll cutoff.</li>
                <li><strong>Danger:</strong> Reserved for urgent issues like system downtime or emergency news.</li>
                <li><strong>Success:</strong> Perfect for company wins, holiday greetings, or celebrations.</li>
            </ul>
            <div class="mt-auto bg-white p-3 rounded-4 shadow-sm opacity-50 border">
                 <div class="d-flex align-items-center mb-2">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-2">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <span class="fw-bold text-dark small">Preview Placeholder</span>
                 </div>
                 <h6 class="mb-1 fw-bold small">Draft title will appear here...</h6>
                 <p class="mb-0 text-muted" style="font-size: 0.7rem;">Content preview is disabled in this draft view.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-4 { border-radius: 1rem !important; }
    .bg-primary-subtle { background-color: #e0f2fe !important; }
    .border-light-subtle { border-color: #f1f5f9 !important; }
</style>
@endsection