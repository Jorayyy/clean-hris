@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">BPO Designations</h4>
            <p class="text-muted small mb-0">Manage Positions, Classifications, and Levels</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Positions -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold">Positions</h6>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('admin.designations.position.store') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label small fw-bold text-muted">ADD NEW POSITION</label>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <input type="text" name="name" class="form-control border-0 bg-light" placeholder="Manager, Lead, etc." required>
                            <button class="btn btn-primary border-0" type="submit"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </form>

                    <label class="form-label small fw-bold text-muted">EXISTING POSITIONS</label>
                    <div class="dropdown">
                        <button class="btn btn-light w-100 d-flex justify-content-between align-items-center border shadow-sm rounded-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="bi bi-list-task me-2 text-primary"></i> View All ({{ count($positions) }})</span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                        <ul class="dropdown-menu w-100 shadow border-0 mt-2 py-2" style="max-height: 300px; overflow-y: auto;">
                            @forelse($positions as $item)
                                <li class="px-3 py-2 d-flex justify-content-between align-items-center">
                                    <span class="small fw-medium text-dark">{{ $item->name }}</span>
                                    <form action="{{ route('admin.designations.position.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this position?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="dropdown-item small text-muted text-center py-3">No positions found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classifications -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold">Classifications</h6>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('admin.designations.classification.store') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label small fw-bold text-muted">ADD NEW CLASSIFICATION</label>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <input type="text" name="name" class="form-control border-0 bg-light" placeholder="Operations, HR, etc." required>
                            <button class="btn btn-primary border-0" type="submit"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </form>

                    <label class="form-label small fw-bold text-muted">EXISTING CLASSIFICATIONS</label>
                    <div class="dropdown">
                        <button class="btn btn-light w-100 d-flex justify-content-between align-items-center border shadow-sm rounded-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="bi bi-tags me-2 text-primary"></i> View All ({{ count($classifications) }})</span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                        <ul class="dropdown-menu w-100 shadow border-0 mt-2 py-2" style="max-height: 300px; overflow-y: auto;">
                            @forelse($classifications as $item)
                                <li class="px-3 py-2 d-flex justify-content-between align-items-center">
                                    <span class="small fw-medium text-dark">{{ $item->name }}</span>
                                    <form action="{{ route('admin.designations.classification.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this classification?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="dropdown-item small text-muted text-center py-3">No classifications found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Levels -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold">Levels</h6>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('admin.designations.level.store') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label small fw-bold text-muted">ADD NEW LEVEL</label>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <input type="text" name="name" class="form-control border-0 bg-light" placeholder="Staff, Senior, etc." required>
                            <button class="btn btn-primary border-0" type="submit"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </form>

                    <label class="form-label small fw-bold text-muted">EXISTING LEVELS</label>
                    <div class="dropdown">
                        <button class="btn btn-light w-100 d-flex justify-content-between align-items-center border shadow-sm rounded-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="bi bi-bar-chart me-2 text-primary"></i> View All ({{ count($levels) }})</span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                        <ul class="dropdown-menu w-100 shadow border-0 mt-2 py-2" style="max-height: 300px; overflow-y: auto;">
                            @forelse($levels as $item)
                                <li class="px-3 py-2 d-flex justify-content-between align-items-center">
                                    <span class="small fw-medium text-dark">{{ $item->name }}</span>
                                    <form action="{{ route('admin.designations.level.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this level?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="dropdown-item small text-muted text-center py-3">No levels found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
