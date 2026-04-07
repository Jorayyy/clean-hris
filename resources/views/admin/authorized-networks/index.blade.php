@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Authorized Networks for Web Bundy</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addIpModal">
                    <i class="bi bi-plus-lg me-1"></i> Add New IP
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2">
                    <small><i class="bi bi-info-circle-fill me-2"></i>Only users connecting from these specific IP addresses will be allowed to use the Web Bundy system. <strong>Global Lockdown is ACTIVE.</strong></small>
                </div>

                @if(session('success'))
                    <div class="alert alert-success mt-3 small">{{ session('success') }}</div>
                @endif

                <div class="table-responsive mt-4">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name / Location</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($networks as $network)
                            <tr>
                                <td class="fw-semibold">{{ $network->name }}</td>
                                <td><code>{{ $network->ip_address }}</code></td>
                                <td>
                                    <form action="{{ route('authorized-networks.update', $network->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                                {{ $network->is_active ? 'checked' : '' }} 
                                                onchange="this.form.submit()">
                                            <span class="badge {{ $network->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $network->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('authorized-networks.destroy', $network->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this authorized IP?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No authorized networks found. Web Bundy is effectively locked for everyone.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add IP Modal -->
<div class="modal fade" id="addIpModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('authorized-networks.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Authorize New Network</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location/Network Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Main Office, Warehouse" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Static IP Address</label>
                        <input type="text" name="ip_address" class="form-control" placeholder="e.g. 192.168.1.1 or 203.111.x.x" required>
                        <div class="form-text mt-2 small">Your current IP is: <code>{{ request()->ip() }}</code></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-target="#addIpModal" data-bs-toggle="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save IP</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
