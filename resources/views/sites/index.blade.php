@extends('layouts.app')

@section('header', 'Manage Sites')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">{{ isset($site) ? 'Edit Site' : 'Add New Site' }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ isset($site) ? route('sites.update', $site) : route('sites.store') }}" method="POST">
                        @csrf
                        @if(isset($site)) @method('PUT') @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Site Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $site->name ?? '') }}" placeholder="e.g. Tacloban Branch" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Location</label>
                            <input type="text" name="location" class="form-control" 
                                   value="{{ old('location', $site->location ?? '') }}" placeholder="e.g. Real St., Tacloban City">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($site) ? 'Update Site' : 'Save Site' }}
                            </button>
                            @if(isset($site))
                                <a href="{{ route('sites.index') }}" class="btn btn-light">Cancel</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Existing Sites</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Site Name</th>
                                    <th>Location</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sites as $s)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $s->name }}</td>
                                    <td>{{ $s->location ?? '--' }}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('sites.edit', $s) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('sites.destroy', $s) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Delete this site? Employees assigned to it will be unassigned.')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No sites found.</td>
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
