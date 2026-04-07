@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Generate Daily Time Records (DTR)</h4>
    </div>
    <div class="col-auto">
        <form action="{{ route('admin.dtrs.index') }}" method="GET" class="d-flex align-items-center bg-white rounded shadow-sm border p-1" style="min-width: 300px;">
            <select name="period" class="form-select border-0 shadow-none fw-bold" onchange="const [start, end] = this.value.split('|'); if(start && end) { window.location.href = `{{ route('admin.dtrs.index') }}?start_date=${start}&end_date=${end}`; } else { window.location.href = `{{ route('admin.dtrs.index') }}`; }">
                <option value="">All Periods</option>
                @foreach($periods as $period)
                    @php
                        $start = $period->start_date->format('Y-m-d');
                        $end = $period->end_date->format('Y-m-d');
                        $isSelected = request('start_date') == $start && request('end_date') == $end;
                    @endphp
                    <option value="{{ $start }}|{{ $end }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="col text-end">
        <a href="{{ route('admin.dtrs.create') }}" class="btn btn-primary shadow-sm px-4 fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Generate New DTR
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Coverage Period</th>
                        <th>Metrics (Hrs)</th>
                        <th>Deficit (Mins)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dtrs as $dtr)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $dtr->employee->full_name }}</div>
                            <small class="text-muted">{{ $dtr->employee->employee_id }}</small>
                        </td>
                        <td>
                            <i class="bi bi-calendar3 small text-muted me-1"></i>
                            {{ $dtr->start_date->format('M d') }} - {{ $dtr->end_date->format('M d, Y') }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark fw-normal border text-uppercase">Reg: {{ $dtr->total_regular_hours }}h</span>
                        </td>
                        <td>
                            @if($dtr->total_late_minutes > 0)
                                <span class="text-danger small fw-bold">Late: {{ $dtr->total_late_minutes }}m</span><br/>
                            @endif
                            @if($dtr->total_undertime_minutes > 0)
                                <span class="text-warning small fw-bold">UT: {{ $dtr->total_undertime_minutes }}m</span>
                            @endif
                            @if($dtr->total_late_minutes == 0 && $dtr->total_undertime_minutes == 0)
                                <span class="text-success small fw-bold"><i class="bi bi-check2-circle"></i> Perfect</span>
                            @endif
                        </td>
                        <td>
                            @if($dtr->status == 'draft')
                                <span class="badge rounded-pill bg-warning text-dark px-3 mt-1 shadow-sm border"><i class="bi bi-pencil me-1"></i>Draft</span>
                            @elseif($dtr->status == 'verified')
                                <span class="badge rounded-pill bg-info text-dark px-3 mt-1 shadow-sm border"><i class="bi bi-shield-check me-1"></i>Verified</span>
                            @else
                                <span class="badge rounded-pill bg-success text-white px-3 mt-1 shadow-sm border"><i class="bi bi-check-circle-fill me-1"></i>Finalized</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('admin.dtrs.show', $dtr->id) }}" class="btn btn-sm btn-outline-primary" title="Details">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if($dtr->status == 'draft')
                                <form action="{{ route('admin.dtrs.verify', $dtr->id) }}" method="POST" class="d-inline ms-1">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-info fw-bold border-2" title="Verify DTR">
                                        <i class="bi bi-check2-square"></i> Verify
                                    </button>
                                </form>
                                @elseif($dtr->status == 'verified')
                                <form action="{{ route('admin.dtrs.finalize', $dtr->id) }}" method="POST" class="d-inline ms-1">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success fw-bold border-2" title="Finalize DTR" onclick="return confirm('Finalizing this record will lock it for payroll use. Proceed?')">
                                        <i class="bi bi-lock-fill"></i> Finalize
                                    </button>
                                </form>
                                @endif
                                
                                <form action="{{ route('admin.dtrs.destroy', $dtr->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to delete this DTR record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete record" {{ $dtr->status === 'finalized' ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No generated DTR records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-4">
    {{ $dtrs->links() }}
</div>
@endsection
