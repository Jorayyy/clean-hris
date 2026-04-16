@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-0">User Management</h4>
        <p class="text-muted small mb-0">Manage system users and their assigned roles</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="bi bi-person-plus-fill me-2"></i>Create New User
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">User</th>
                        <th>Email</th>
                        <th>Roles</th>
                        @if(auth()->user()->role === 'super-admin' || auth()->user()->hasRole('Super Admin'))
                        <th>Password</th>
                        @endif
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        @if(auth()->user()->role === 'super-admin' || auth()->user()->hasRole('Super Admin'))
                        <td>
                            <code class="text-primary">{{ $user->plain_password ?? 'N/A' }}</code>
                        </td>
                        @endif
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-white border">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-white border text-danger" onclick="return confirm('Ensure you want to delete this user?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $users->links() }}
    </div>
    @endif
</div>

<style>
    .btn-white { background-color: #fff; color: #212529; }
    .btn-white:hover { background-color: #f8f9fa; }
</style>
@endsection
