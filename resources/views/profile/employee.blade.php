@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-primary text-white rounded-circle p-2 me-3 shadow-sm">
                <i class="bi bi-person-badge fs-4"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0">My Employee Profile</h4>
                <p class="text-muted small mb-0">Manage your personal and employment information</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- Sidebar: Photo & Account Access -->
                <div class="col-md-3">
                    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body text-center p-4">
                            <div class="mb-4 position-relative d-inline-block">
                                @if($employee && $employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="rounded-circle img-thumbnail shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 150px; height: 150px;">
                                        <i class="bi bi-person text-secondary" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                                <label for="photo" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm border border-2 border-white" style="cursor: pointer; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-camera-fill small"></i>
                                </label>
                                <input type="file" name="photo" id="photo" class="d-none" accept="image/*">
                            </div>
                            <h6 class="fw-bold mb-1">{{ Auth::user()->name }}</h6>
                            <p class="text-muted small mb-0">{{ $employee->position ?? 'Employee' }}</p>
                            <div class="mt-3">
                                <span class="badge {{ ($employee->status ?? '') == 'active' ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-pill small">
                                    {{ strtoupper($employee->status ?? 'ACTIVE') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
                        <h6 class="fw-bold small text-muted mb-4 tracking-wider"><i class="bi bi-shield-lock me-2"></i>ACCOUNT ACCESS</h6>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted mb-1">Portal Name</label>
                            <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="form-control form-control-sm border-0 shadow-none bg-white py-2 px-3 fw-medium">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="form-control form-control-sm border-0 shadow-none bg-white py-2 px-3 fw-medium">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted mb-1 text-danger">Bundy PIN (DTR)</label>
                            <input type="password" name="web_bundy_code" value="{{ $employee->web_bundy_code ?? '' }}" class="form-control form-control-sm border border-danger bg-white py-2 px-3 fw-bold text-danger">
                            <small class="text-muted mt-1 d-block" style="font-size: 0.65rem;">Code for Web Bundy login</small>
                        </div>
                    </div>
                </div>

                <!-- Main Content: Information Sections -->
                <div class="col-md-9">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-5">
                                <h5 class="mb-4 text-primary pb-3 border-bottom fw-800 tracking-tight d-flex align-items-center">
                                    <i class="bi bi-person-lines-fill me-3 fs-4"></i>Personal Information
                                </h5>
                                
                                <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">First Name</label>
                                        <input type="text" name="first_name" class="form-control border-0 bg-light p-3 rounded-3 fw-medium" value="{{ old('first_name', $employee->first_name ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control border-0 bg-light p-3 rounded-3 fw-medium" value="{{ old('middle_name', $employee->middle_name ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Last Name</label>
                                        <input type="text" name="last_name" class="form-control border-0 bg-light p-3 rounded-3 fw-medium" value="{{ old('last_name', $employee->last_name ?? '') }}">
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Birthday</label>
                                        <input type="date" name="birthday" class="form-control border-0 bg-light p-3 rounded-3 fw-medium" value="{{ old('birthday', $employee->birthday ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Gender</label>
                                        <select name="gender" class="form-select border-0 bg-light p-3 rounded-3 fw-medium">
                                            <option value="Male" {{ ($employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ ($employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Civil Status</label>
                                        <select name="civil_status" class="form-select border-0 bg-light p-3 rounded-3 fw-medium">
                                            <option value="Single" {{ ($employee->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Married" {{ ($employee->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                            <option value="Widowed" {{ ($employee->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                            <option value="Separated" {{ ($employee->civil_status ?? '') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted mb-2">Religion</label>
                                        <input type="text" name="religion" class="form-control border-0 bg-light p-3 rounded-3 fw-medium" value="{{ old('religion', $employee->religion ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted mb-2">Place of Birth</label>
                                        <input type="text" name="place_of_birth" class="form-control border-0 bg-light p-3 rounded-3 fw-medium" value="{{ old('place_of_birth', $employee->place_of_birth ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h5 class="mb-4 text-primary pb-3 border-bottom fw-800 tracking-tight d-flex align-items-center mt-2">
                                    <i class="bi bi-briefcase-fill me-3 fs-4"></i>Employment Details
                                </h5>
                                
                                <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Employee ID</label>
                                        <input type="text" class="form-control border-0 bg-white p-3 rounded-3 fw-bold text-dark" value="{{ $employee->employee_id ?? 'N/A' }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Position</label>
                                        <input type="text" class="form-control border-0 bg-white p-3 rounded-3 fw-bold text-dark" value="{{ $employee->position ?? 'N/A' }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted mb-2">Location / Site</label>
                                        <input type="text" class="form-control border-0 bg-white p-3 rounded-3 fw-bold text-dark" value="{{ $employee->location ?? 'N/A' }}" readonly>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted mb-2">Employment Type</label>
                                        <div class="p-3 rounded-3 bg-white d-flex align-items-center border">
                                            <i class="bi bi-tag-fill text-primary me-2"></i>
                                            <span class="fw-bold">{{ strtoupper($employee->employment_type ?? 'N/A') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted mb-2">Date Hired</label>
                                        <input type="text" class="form-control border-0 bg-white p-3 rounded-3 fw-bold text-dark" value="{{ $employee->hired_date ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Update Section -->
                            <div class="p-4 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark mb-4 d-flex align-items-center">
                                    <i class="bi bi-shield-check me-2 text-primary fs-5"></i>
                                    UPDATE SYSTEM PASSWORD
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted mb-1">New Password</label>
                                        <input type="password" name="new_password" class="form-control border-0 shadow-none py-2 px-3 bg-white" placeholder="Leave blank to keep current">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted mb-1">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" class="form-control border-0 shadow-none py-2 px-3 bg-white">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 text-end">
                                <button type="submit" class="btn btn-primary px-5 py-3 fw-800 rounded-3 shadow-sm tracking-wider">
                                    SAVE PROFILE CHANGES
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .rounded-4 { border-radius: 1rem !important; }
    .bg-light { background-color: #f8fafc !important; }
    .form-control:focus, .form-select:focus { 
        background-color: #fff !important; 
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        border-color: #3b82f6 !important;
    }
</style>
@endsection
