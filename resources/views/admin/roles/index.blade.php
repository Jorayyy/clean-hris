@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>Roles & Permissions</h5>
                    <p class="text-muted small mb-0 mt-1">Define roles and assign specific permissions to each role.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="bi bi-plus-lg me-1"></i> Add New Role
                </button>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 200px;">Role Name</th>
                                <th>Active Users</th>
                                <th>Role Description</th>
                                <th>Remarks</th>
                                <th style="width: 150px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td>
                                    <span class="fw-bold text-uppercase">{{ $role->name }}</span>
                                    @if($role->name === 'super-admin' || $role->name === 'admin')
                                        <i class="bi bi-patch-check-fill text-primary ms-1" title="System Role"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($role->users->count() > 0)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border rounded-pill px-3 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-person-circle me-1 text-primary"></i> 
                                                <strong>{{ $role->users->count() }}</strong> {{ $role->users->count() > 1 ? 'Users' : 'User' }}
                                            </button>
                                            <ul class="dropdown-menu shadow border-0 py-2 mt-1" style="max-height: 250px; overflow-y: auto; min-width: 250px;">
                                                <li class="dropdown-header text-uppercase pb-1" style="font-size: 0.7rem;">Assigned Emails</li>
                                                <li><hr class="dropdown-divider mt-0"></li>
                                                @foreach($role->users as $u)
                                                    <li class="px-3 py-1">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                                                {{ strtoupper(substr($u->name ?? $u->email, 0, 1)) }}
                                                            </div>
                                                            <span class="small fw-medium">{{ $u->email }}</span>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted small italic text-sm">No users assigned</span>
                                    @endif
                                </td>
                                <td>{{ $role->description ?? 'N/A' }}</td>
                                <td>{{ $role->remarks ?: '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#assignUsersModal{{ $role->id }}"
                                                title="Assign Users">
                                            <i class="bi bi-people"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPermissionsModal{{ $role->id }}"
                                                title="Manage Permissions">
                                            <i class="bi bi-shield-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editRoleModal{{ $role->id }}"
                                                title="Edit Role">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($role->name !== 'super-admin' && $role->name !== 'admin')
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Role">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>

                                    <!-- Assign Users Modal -->
                                    <div class="modal fade" id="assignUsersModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Assign Users to: {{ $role->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.roles.assign-users', $role) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p class="text-muted small mb-3">Select users to assign to this role. Unselected users currently in this role will be removed.</p>
                                                        <div class="list-group" style="max-height: 300px; overflow-y: auto;">
                                                            @foreach($users as $user)
                                                            <label class="list-group-item">
                                                                <input class="form-check-input me-1" type="checkbox" name="users[]" value="{{ $user->id }}" 
                                                                    {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                                                <span class="small fw-medium">{{ $user->name }}</span>
                                                                <div class="text-muted" style="font-size: 0.75rem; margin-left: 1.5rem;">{{ $user->email }}</div>
                                                            </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save Assignments</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Role Modal -->
                                    <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Role: {{ $role->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Role Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Role Description</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $role->description }}</textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Remarks</label>
                                                            <input type="text" name="remarks" class="form-control" value="{{ $role->remarks }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Update Role</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Permissions Modal -->
                                    <div class="modal fade" id="editPermissionsModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Manage Permissions: {{ ucfirst($role->name) }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <p class="text-muted small mb-4">Select the specific actions this role is allowed to perform.</p>
                                                        <div class="row g-3">
                                                            @php
                                                                $groupedPermissions = $permissions->groupBy(function($item) {
                                                                    $parts = explode(' ', $item->name);
                                                                    return $parts[1] ?? 'other';
                                                                });
                                                            @endphp
                                                            
                                                            @foreach($groupedPermissions as $group => $perms)
                                                            <div class="col-md-6 mb-3">
                                                                <h6 class="fw-bold border-bottom pb-2 text-primary">{{ ucfirst($group) }}</h6>
                                                                @foreach($perms as $permission)
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                                           value="{{ $permission->name }}" 
                                                                           id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                    <label class="form-check-label small" for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                                        {{ str_replace('-', ' ', ucwords($permission->name, '-')) }}
                                                                    </label>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
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

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. HR Staff, Payroll Manager" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Describe what this role does"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Internal notes">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
