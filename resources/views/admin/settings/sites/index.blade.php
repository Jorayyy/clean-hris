@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-0">Accounts Management</h4>
            <p class="text-muted">Manage site-specific settings and fixed schedules.</p>
        </div>
    </div>

    <div class="row">
        @foreach($sites as $site)
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary me-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $site->name }}</h5>
                            <span class="badge bg-light text-muted border">{{ $site->location }}</span>
                        </div>
                    </div>
                    <p class="small text-muted mb-4">Manage the specific daily schedules and special attendance policies for this account.</p>
                    <a href="{{ route('admin.settings.sites.show', $site->id) }}" class="btn btn-outline-primary rounded-pill w-100 fw-bold">
                        Configure Account
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
