@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-primary"></i>Security & Permissions Management</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    @foreach($roles as $role)
                    <div class="col-md-6">
                        <div class="border rounded p-4 h-100 {{ $role->name == 'admin' ? 'border-primary' : 'border-info' }}">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold text-uppercase mb-0">{{ $role->name }} Role</h6>
                                <span class="badge {{ $role->name == 'admin' ? 'bg-primary' : 'bg-info' }}">Active</span>
                            </div>
                            
                            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    @foreach($permissions as $permission)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                               id="perm_{{ $role->id }}_{{ $permission->id }}"
                                               {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm_{{ $role->id }}_{{ $permission->id }}">
                                            {{ str_replace('-', ' ', ucwords($permission->name, '-')) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>

                                <button type="submit" class="btn {{ $role->name == 'admin' ? 'btn-primary' : 'btn-info text-white' }} w-100 btn-sm fw-bold">
                                    Update {{ ucfirst($role->name) }} Permissions
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 p-3 bg-light rounded border-start border-warning border-4">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-3 h4"></i>
                        <div>
                            <strong class="d-block mb-1">Warning: Security Impact</strong>
                            <p class="small text-muted mb-0">Changes take effect immediately. Be careful when removing permissions from the "Admin" role, as you may lock yourself out of management features.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
