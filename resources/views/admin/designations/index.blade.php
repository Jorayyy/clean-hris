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

    <div class="row">
        <!-- Positions -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Positions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.designations.position.store') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="New Position" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <div class="list-group list-group-flush">
                        @foreach($positions as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                            {{ $item->name }}
                            <form action="{{ route('admin.designations.position.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this position?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Classifications -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Classifications</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.designations.classification.store') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="New Classification" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <div class="list-group list-group-flush">
                        @foreach($classifications as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                            {{ $item->name }}
                            <form action="{{ route('admin.designations.classification.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this classification?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Levels -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Levels</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.designations.level.store') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="New Level" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <div class="list-group list-group-flush">
                        @foreach($levels as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                            {{ $item->name }}
                            <form action="{{ route('admin.designations.level.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this level?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
