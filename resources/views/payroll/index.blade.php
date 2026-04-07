@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <h5 class="mb-0">Payroll Periods</h5>
        <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm">Create Payroll Period</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light text-dark">
                    <tr>
                        <th>Period Code</th>
                        <th>Payroll Group</th>
                        <th>Coverage Period</th>
                        <th>Pay Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $row)
                    <tr>
                        <td><span class="fw-bold text-dark"><i class="bi bi-hash small text-muted"></i>{{ $row->payroll_code }}</span></td>
                        <td><span class="badge bg-secondary text-white fw-medium">{{ $row->payrollGroup->name ?? 'All Groups' }}</span></td>
                        <td><small class="text-muted"><i class="bi bi-calendar-range me-1"></i></small> {{ $row->start_date }} to {{ $row->end_date }}</td>
                        <td><small class="text-success fw-bold"><i class="bi bi-wallet2 me-1"></i></small> {{ $row->pay_date }}</td>
                        <td>
                            @if($row->status == 'draft')
                                <span class="badge rounded-pill bg-warning text-dark px-3 shadow-sm border"><i class="bi bi-pencil me-1"></i>Draft</span>
                            @else
                                <span class="badge rounded-pill bg-success text-white px-3 shadow-sm border"><i class="bi bi-check-circle me-1"></i>Processed</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('payroll.show', $row->id) }}" class="btn btn-sm btn-outline-primary" title="Details">
                                    <i class="bi bi-eye-fill"></i> View
                                </a>
                                <a href="{{ route('payroll.edit', $row->id) }}" class="btn btn-sm btn-outline-info" title="Edit Period">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('payroll.destroy', $row->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Permanently delete this period? This action cannot be undone.')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
