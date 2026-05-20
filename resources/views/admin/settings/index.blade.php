@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-4 px-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="bi bi-display me-2 text-primary"></i>System Branding Settings</h5>
                <p class="text-muted small mb-0 mt-1">Manage your application identity and appearance.</p>
            </div>
            <div class="card-body p-4 pt-0">
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-center mb-4 p-4 bg-light rounded-4 mx-0">
                        <div class="col-md-4 text-center border-end border-2">
                            <label class="form-label d-block fw-bold text-muted small text-uppercase mb-3">Company Logo</label>
                            @php $logo = is_array($settings) ? ($settings['app_logo'] ?? null) : ($settings->app_logo ?? null); @endphp
                            @if($logo)
                                <img src="/logos/{{ $logo }}" alt="App Logo" class="img-fluid rounded shadow-sm mb-3" style="max-height: 120px; object-fit: contain;" onerror="this.src='{{ asset('storage/' . $logo) }}'">
                            @else
                                <div class="bg-white rounded shadow-sm p-4 mb-3 text-muted">
                                    <i class="bi bi-image h1"></i><br>
                                    <small>No Logo Set</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8 ps-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold">System Name</label>
                                <input type="text" name="app_name" class="form-control form-control-lg border-0 shadow-sm" value="{{ is_array($settings) ? ($settings['app_name'] ?? '') : ($settings->app_name ?? '') }}" required>
                                <small class="text-muted">This name appears in the navigation bar and reports.</small>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label fw-bold">Update Logo</label>
                                <input type="file" name="app_logo" class="form-control border-0 shadow-sm">
                                <small class="text-muted">Max size: 2MB (PNG/SVG recommended)</small>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-dark text-white rounded-4 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-primary"></i>Security</h6>
                                <p class="small text-white-50 mb-0">Password required for sensitive payroll edits (DTR Overrides).</p>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white bg-opacity-10 border-0 text-white"><i class="bi bi-key"></i></span>
                                    <input type="password" name="dtr_edit_password" class="form-control bg-white bg-opacity-25 border-0 text-white placeholder-white" placeholder="Change password...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-2">
                        <button type="submit" class="btn btn-primary px-5 btn-lg rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i>Save Branding
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <p class="text-muted small">Looking for payroll specific settings? <a href="{{ route('admin.payroll-settings.index') }}" class="fw-bold link-primary">Go to Payroll Configuration</a></p>
        </div>
    </div>
</div>
@endsection

