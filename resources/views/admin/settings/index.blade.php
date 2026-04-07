@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-gear-fill me-2 text-primary"></i>System Settings</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row items-center align-items-center mb-4">
                        <div class="col-md-4 text-center border-end">
                            <label class="form-label d-block fw-bold text-muted small text-uppercase">Current Logo</label>
                            @if($settings->app_logo)
                                <img src="{{ asset('storage/' . $settings->app_logo) }}" alt="App Logo" class="img-fluid rounded mb-3" style="max-height: 120px; object-fit: contain;">
                            @else
                                <div class="bg-light rounded p-4 mb-3 text-muted">
                                    <i class="bi bi-image h1"></i><br>
                                    <small>No Logo Set</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8 ps-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">System Name</label>
                                <input type="text" name="app_name" class="form-control" value="{{ $settings->app_name }}" required>
                                <small class="text-muted">This name will appear in the navigation bar and page titles.</small>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label fw-bold">Update Logo</label>
                                <input type="file" name="app_logo" class="form-control">
                                <small class="text-muted">Recommended: Transparent PNG or SVG (Max 2MB)</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
