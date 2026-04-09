@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow rounded border-0 bg-dark text-white overflow-hidden">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">My Daily Time Record History</h3>
                <p class="mb-0 text-white-50">Select a payroll period to view your detailed DTR logs.</p>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow border-0 rounded overflow-hidden">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Payroll Periods</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Period Range</th>
                                <th>Total Regular Hours</th>
                                <th>Late (min)</th>
                                <th>Undertime (min)</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dtrs as $dtr)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $dtr->start_date->format('M d, Y') }} - {{ $dtr->end_date->format('M d, Y') }}</div>
                                    </td>
                                    <td>{{ number_format($dtr->total_regular_hours, 2) }}</td>
                                    <td>
                                        <span class="{{ $dtr->total_late_minutes > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $dtr->total_late_minutes }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ $dtr->total_undertime_minutes > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                                            {{ $dtr->total_undertime_minutes }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($dtr->status == 'finalized')
                                            <span class="badge bg-success">Finalized</span>
                                        @elseif($dtr->status == 'verified')
                                            <span class="badge bg-info">Verified</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('employee.dtr.show', $dtr->id) }}" class="btn btn-sm btn-primary px-3 shadow-sm fw-bold">
                                            <i class="bi bi-eye-fill me-1"></i> View DTR
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-1 opacity-25 d-block mb-3"></i>
                                        No DTR records found for your account yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $dtrs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
