@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-3">
        <a href="{{ route('schedules.index') }}" class="btn btn-sm text-muted p-0 mb-3"><i class="bi bi-arrow-left me-1"></i> Back to Hub</a>
    </div>

    <!-- Header similar to the image -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Mancao Electronic Connect Business Solutions OPC</h6>
            <a href="{{ route('admin.settings.schedule-groups.create') }}" class="text-success fs-4" title="Add Group">
                <i class="bi bi-plus-circle-fill"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 0.9rem;">
                    <thead class="bg-light text-dark fw-bold">
                        <tr>
                            <th class="py-3" style="width: 100px;">Group ID</th>
                            <th class="py-3 text-start ps-4">Group Name</th>
                            <th class="py-3">Created By</th>
                            <th class="py-3">Status</th>
                            <th class="py-3" style="width: 250px;">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td class="text-start ps-4 fw-medium">{{ strtoupper($group->name) }}</td>
                            <td class="text-muted">{{ $group->creator ? strtoupper($group->creator->name) : 'SYSTEM' }}</td>
                            <td>
                                <span class="text-{{ $group->status === 'Active' ? 'success' : 'danger' }}">{{ $group->status }}</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-3 fs-5">
                                    <!-- Deactivate/Power Icon -->
                                    <form action="{{ route('admin.settings.schedule-groups.toggle-status', $group->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn p-0 text-danger border-0" title="Click to Disable/Deactivate">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>

                                    <!-- View/Edit Plot (Folder Icon) -->
                                    <a href="{{ route('admin.settings.schedule-groups.plot', $group->id) }}" class="text-info" style="color: #00bcd4 !important;" title="Plot Schedule">
                                        <i class="bi bi-folder-fill"></i>
                                    </a>

                                    <!-- Edit Name/Pencil Icon -->
                                    <a href="{{ route('admin.settings.schedule-groups.edit', $group->id) }}" class="text-warning" title="Edit Group Name">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    <!-- Delete/Trash Icon -->
                                    <form action="{{ route('admin.settings.schedule-groups.destroy', $group->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn p-0 text-primary border-0" onclick="return confirm('Are you sure?')" title="Delete Group">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-5 text-muted italic small text-center">No schedule groups found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Matching the vintage/tabular feel */
.table th { border-bottom: 1px solid #dee2e6 !important; }
.card-header h6 { letter-spacing: 0.5px; }
.btn:hover { transform: scale(1.1); transition: 0.2s; }
</style>
@endsection
