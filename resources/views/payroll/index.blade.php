@extends('layouts.app')

@section('content')
@php
    $draftCount = $payrolls->where('status', 'draft')->count();
    $processedCount = $payrolls->where('status', 'processed')->count();
    $approvedCount = $payrolls->where('status', 'approved')->count();
    $hasPeriodFilter = request('start_date') && request('end_date');
@endphp

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">Payroll</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">{{ $payrolls->count() }} period{{ $payrolls->count() != 1 ? 's' : '' }} &middot; {{ $approvedCount }} approved &middot; {{ $draftCount }} in draft</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payroll-settings.index') }}" class="btn btn-light px-4" title="Payroll Settings"><i class="bi bi-gear me-1"></i>Settings</a>
        <a href="{{ route('payroll.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Create Payroll</a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <div class="dropdown" style="min-width: 240px;">
            <button class="btn btn-light dropdown-toggle w-100 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="text-truncate me-2">
                    @if($hasPeriodFilter)
                        {{ \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') }}
                    @else
                        All Periods
                    @endif
                </span>
            </button>
            <ul class="dropdown-menu py-2" style="min-width: 240px;">
                <li>
                    <a class="dropdown-item py-2 fw-medium {{ !$hasPeriodFilter ? 'active' : '' }}" href="{{ route('payroll.index') }}">
                        All Periods
                    </a>
                </li>
                @foreach($periods as $period)
                    @php
                        $isActive = request('start_date') == $period->start_date && request('end_date') == $period->end_date;
                    @endphp
                    <li>
                        <a class="dropdown-item py-2 fw-medium {{ $isActive ? 'active' : '' }}"
                           href="{{ route('payroll.index', ['start_date' => $period->start_date, 'end_date' => $period->end_date]) }}">
                            {{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="d-inline-flex gap-1 p-1 rounded-pill ms-auto" style="background: rgba(0,0,0,0.05);">
            <span class="filter-chip active">Draft <span class="opacity-75">{{ $draftCount }}</span></span>
            <span class="filter-chip">Processed <span class="opacity-75">{{ $processedCount }}</span></span>
            <span class="filter-chip">Approved <span class="opacity-75">{{ $approvedCount }}</span></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Period</th>
                    <th>Scope</th>
                    <th>Coverage</th>
                    <th>Pay Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $row)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold code-chip">{{ $row->payroll_code }}</div>
                        </td>
                        <td style="font-size: 0.88rem;">
                            @if($row->employee_id)
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person text-muted"></i>{{ $row->employee->full_name ?? 'Individual' }}
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-people text-muted"></i>{{ $row->payrollGroup->name ?? 'All Groups' }}
                                </div>
                            @endif
                        </td>
                        <td style="font-size: 0.88rem;">
                            {{ \Carbon\Carbon::parse($row->start_date)->format('M d') }} &ndash; {{ \Carbon\Carbon::parse($row->end_date)->format('M d, Y') }}
                        </td>
                        <td style="font-size: 0.88rem;">{{ \Carbon\Carbon::parse($row->pay_date)->format('M d, Y') }}</td>
                        <td class="text-center">
                            @if($row->status == 'draft')
                                <span class="badge badge-orange">Draft</span>
                            @elseif($row->status == 'processed')
                                <span class="badge badge-blue">Processed</span>
                            @elseif($row->status == 'approved')
                                <span class="badge badge-green">Approved</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($row->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('payroll.show', $row->id) }}" class="btn btn-sm btn-light icon-btn" title="View details"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('payroll.edit', $row->id) }}" class="btn btn-sm btn-light icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('payroll.destroy', $row->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light icon-btn icon-danger" title="Delete" onclick="return confirm('Permanently delete this period? This action cannot be undone.')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-wallet2 text-muted fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-3">@if($hasPeriodFilter) No payroll runs for this period. @else No payroll periods yet. Create one to run your first payroll. @endif</p>
                            <a href="{{ route('payroll.create') }}" class="btn btn-sm btn-primary">Create payroll</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .code-chip {
        display: inline-block;
        font-family: ui-monospace, "SF Mono", SFMono-Regular, Menlo, monospace;
        font-size: 0.78rem;
        background: rgba(0,0,0,0.05);
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        letter-spacing: 0.01em;
    }
    .badge-orange { background: #ffefd6 !important; color: #995f00 !important; }
    .badge-blue { background: rgba(0,113,227,0.1) !important; color: #0071e3 !important; }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
    .filter-chip {
        padding: 0.35rem 0.95rem; border-radius: 980px;
        font-size: 0.82rem; font-weight: 500; color: #6e6e73;
    }
    .filter-chip.active {
        background: #fff; color: #1d1d1f;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .icon-btn { padding: 0.3rem 0.55rem; line-height: 1.2; }
    .icon-btn.icon-danger:hover { background: #ffe5e3; color: #d02f26; }
</style>
@endsection
