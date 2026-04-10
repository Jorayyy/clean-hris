@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-auto">
        <div class="dropdown">
            <button class="btn btn-white shadow-sm border rounded-3 px-3 py-2 dropdown-toggle fw-bold text-dark d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 200px;">
                <span id="selectedPeriodLabel" class="me-2">
                    @if(request('start_date') && request('end_date'))
                        {{ \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') }}
                    @else
                        All Periods
                    @endif
                </span>
            </button>
            <ul class="dropdown-menu shadow border-0 rounded-3 mt-2 py-2" style="min-width: 200px;">
                <li>
                    <a class="dropdown-item py-2 fw-medium {{ !request('start_date') ? 'active bg-primary text-white' : '' }}" href="{{ route('payroll.index') }}">
                        All Periods
                    </a>
                </li>
                <li><hr class="dropdown-divider opacity-50"></li>
                @foreach($periods as $period)
                    @php
                        $isActive = request('start_date') == $period->start_date && request('end_date') == $period->end_date;
                    @endphp
                    <li>
                        <a class="dropdown-item py-2 fw-medium {{ $isActive ? 'active bg-primary text-white' : '' }}" 
                           href="{{ route('payroll.index', ['start_date' => $period->start_date, 'end_date' => $period->end_date]) }}">
                            {{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
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
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('payroll.show', $row->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="Details">
                                    <i class="bi bi-eye-fill"></i> View
                                </a>
                                <a href="{{ route('payroll.edit', $row->id) }}" class="btn btn-sm btn-outline-info shadow-sm" title="Edit Period">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('payroll.destroy', $row->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger shadow-sm" title="Delete" onclick="return confirm('Permanently delete this period? This action cannot be undone.')">
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
