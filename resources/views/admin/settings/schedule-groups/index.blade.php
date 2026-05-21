@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('schedules.index') }}" class="btn btn-sm text-muted p-0 mb-3"><i class="bi bi-arrow-left me-1"></i> Back to Hub</a>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0">Schedule Groups</h2>
                <p class="text-muted">Manage pre-defined group schedules to assign to multiple accounts/sites.</p>
            </div>
            <a href="{{ route('admin.settings.schedule-groups.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Create New Group
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Group Name</th>
                            <th class="py-3">Description</th>
                            <th class="py-3 text-center">Assigned Sites</th>
                            <th class="py-3">Created</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary">{{ $group->name }}</div>
                            </td>
                            <td>{{ $group->description ?: 'No description' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill">{{ $group->sites_count }} Sites</span>
                            </td>
                            <td class="small text-muted">{{ $group->created_at->format('M d, Y') }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.settings.schedule-groups.edit', $group->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.settings.schedule-groups.destroy', $group->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Are you sure?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar3 fs-1 d-block mb-3"></i>
                                    <p>No schedule groups found. Create one to get started.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
