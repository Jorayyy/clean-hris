@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Add New Deduction Type</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.deductions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Code (e.g. CA, HMO)</label>
                        <input type="text" name="code" class="form-control" required placeholder="UNIQUE_CODE">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Deduction Name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Type</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Deduction Types Library</h6>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary text-decoration-none">Back to Settings</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light small">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($types as $type)
                            <tr>
                                <td class="fw-bold">{{ $type->code }}</td>
                                <td>{{ $type->name }}</td>
                                <td>
                                    @if($type->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-link py-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $type->id }}">Edit</button>
                                    
                                    <form action="{{ route('admin.settings.deductions.destroy', $type->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger py-0 border-0 bg-transparent" onclick="return confirm('Delete this type?')">Delete</button>
                                    </form>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $type->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content text-start">
                                                <form action="{{ route('admin.settings.deductions.update', $type->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit {{ $type->code }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $type->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Description</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $type->description }}</textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Status</label>
                                                            <select name="is_active" class="form-select">
                                                                <option value="1" {{ $type->is_active ? 'selected' : '' }}>Active</option>
                                                                <option value="0" {{ !$type->is_active ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
