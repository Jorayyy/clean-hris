@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-800 tracking-tight text-primary mb-1">Company Announcements</h4>
        <p class="text-muted mb-0 small">Broadcast messages and updates to all employees.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('announcements.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Create New
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
    {{ session('success') }}
</div>
@endif

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light font-monospace small text-muted text-uppercase tracking-wider">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date Published</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $announcement->title }}</div>
                            <small class="text-muted text-truncate d-inline-block" style="max-width: 300px;">{{ Str::limit($announcement->content, 50) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $announcement->type == 'info' ? 'primary' : ($announcement->type == 'warning' ? 'warning' : 'danger') }}-subtle text-{{ $announcement->type == 'info' ? 'primary' : ($announcement->type == 'warning' ? 'warning-emphasis' : 'danger') }} rounded-pill px-3">
                                {{ ucfirst($announcement->type) }}
                            </span>
                        </td>
                        <td>
                            @if($announcement->is_active)
                                <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                            @else
                                <span class="text-muted small fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Draft</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $announcement->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-sm btn-light border rounded-pill me-2">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border rounded-pill">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-megaphone fs-1 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">No announcements found. Start by creating one!</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($announcements->hasPages())
    <div class="card-footer bg-white py-3 border-0">
        {{ $announcements->links() }}
    </div>
    @endif
</div>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-4 { border-radius: 1rem !important; }
    .bg-primary-subtle { background-color: #e0f2fe !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; }
    .bg-warning-subtle { background-color: #fef3c7 !important; }
</style>
@endsection