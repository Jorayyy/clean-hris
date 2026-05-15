@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 mt-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0">Edit User: {{ $user->name }}</h4>
                <p class="text-muted small mb-0">Manage account details.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control border shadow-none rounded-3 px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control border shadow-none rounded-3 px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted text-danger">Update Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control border shadow-none rounded-3 px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">Confirm Password Update</label>
                <input type="password" name="password_confirmation" class="form-control border shadow-none rounded-3 px-3 py-2">
            </div>

            <!-- Role Management -->
            <div class="border-top pt-4 mt-4">
                <h6 class="fw-bold text-primary mb-3">System Roles</h6>
                <div class="row g-2">
                    @php
                        $availableRoles = \Spatie\Permission\Models\Role::all();
                    @endphp
                    @foreach($availableRoles as $role)
                        <div class="col-md-6">
                            <div class="form-check border rounded-3 px-3 py-2">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role{{ $role->id }}"
                                       {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                <label class="form-check-label small d-block" for="role{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Site Access Control -->
            <div class="border-top pt-4 mt-4">
                <h6 class="fw-bold text-primary mb-3">Site Access Permissions</h6>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="can_access_all_sites" id="accessAll" value="1" {{ $user->can_access_all_sites ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-success" for="accessAll">
                        Authority to View All Sites
                    </label>
                </div>

                <div id="siteSelection" style="{{ $user->can_access_all_sites ? 'display:none;' : '' }}">
                    <label class="form-label fw-bold small text-muted d-block mb-2">Select Accessible Site(s)</label>
                    <div class="row g-2">
                        @foreach($sites as $site)
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 px-3 py-2">
                                    <input class="form-check-input" type="checkbox" name="accessible_sites[]" value="{{ $site->id }}" id="site{{ $site->id }}"
                                           {{ in_array($site->id, (array)($user->accessible_sites ?? [])) || ($user->employee && $user->employee->site_id == $site->id) ? 'checked' : '' }}>
                                    <label class="form-check-label small d-block" for="site{{ $site->id }}">
                                        {{ $site->name }}
                                        @if($user->employee && $user->employee->site_id == $site->id)
                                            <span class="badge bg-info text-white ms-1" style="font-size: 0.6rem;">Primary Site</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('accessAll').addEventListener('change', function() {
                    document.getElementById('siteSelection').style.display = this.checked ? 'none' : 'block';
                });
            </script>

            <div class="d-grid shadow-sm rounded-3 overflow-hidden mt-4">
                <button type="submit" class="btn btn-primary py-3 fw-bold">UPDATE USER ACCOUNT</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
