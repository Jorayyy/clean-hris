@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary">Add New Allowance/Add-on Type</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.allowances.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Code (e.g. ATT_BONUS, FOOD_ALLOW)</label>
                            <input type="text" name="code" class="form-control" required placeholder="UNIQUE_CODE">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Allowance Name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Type</label>
                            <select name="type" class="form-select">
                                <option value="fixed">Fixed (Per Pay Period)</option>
                                <option value="daily">Daily (Per Day Present)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Default Amount</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">₱</span>
                                <input type="number" step="0.01" name="default_amount" class="form-control" value="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_taxable" value="1" id="is_taxable">
                                <label class="form-check-label small fw-bold" for="is_taxable">Taxable Allowance</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Allowance Type</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Allowance Library</h6>
                    <a href="{{ route('admin.payroll-settings.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to Payroll Settings
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light small">
                                <tr>
                                    <th class="ps-4">Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Default Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $type->code }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ ucfirst($type->type) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">₱{{ number_format($type->default_amount, 2) }}</td>
                                    <td>
                                        @if($type->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link py-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $type->id }}">Edit</button>
                                        
                                        <form action="{{ route('admin.settings.allowances.destroy', $type->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger py-0 border-0 bg-transparent" onclick="return confirm('Delete this allowance type?')">Delete</button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal{{ $type->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-start rounded-4 shadow">
                                                    <form action="{{ route('admin.settings.allowances.update', $type->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title fw-bold">Edit {{ $type->code }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body py-4">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Name</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $type->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Type</label>
                                                                <select name="type" class="form-select">
                                                                    <option value="fixed" {{ $type->type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                                    <option value="daily" {{ $type->type == 'daily' ? 'selected' : '' }}>Daily</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Default Amount</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">₱</span>
                                                                    <input type="number" step="0.01" name="default_amount" class="form-control" value="{{ $type->default_amount }}">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="is_taxable" value="1" id="tax{{ $type->id }}" {{ $type->is_taxable ? 'checked' : '' }}>
                                                                    <label class="form-check-label small fw-bold" for="tax{{ $type->id }}">Taxable Allowance</label>
                                                                </div>
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
                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Allowance</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">No allowance types found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection