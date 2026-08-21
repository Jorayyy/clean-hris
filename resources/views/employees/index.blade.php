@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $isAuthorized = $user->is_super_admin || $user->hasRole('Accounting Admin');
    $activeCount = $employees->where('status', 'active')->count();
    $inactiveCount = $employees->count() - $activeCount;
@endphp

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">Employees</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">{{ $employees->count() }} people &middot; {{ $activeCount }} active</p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary px-4"><i class="bi bi-person-plus me-2"></i>Add Employee</a>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <div class="position-relative flex-grow-1" style="max-width: 320px;">
            <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: #86868b; font-size: 0.85rem;"></i>
            <input type="text" id="empSearch" class="form-control ps-5" placeholder="Search name, ID, position..." style="border-radius: 980px;">
        </div>
        <div class="d-inline-flex gap-1 p-1 rounded-pill ms-auto" style="background: rgba(0,0,0,0.05);">
            <button type="button" class="filter-chip active" data-filter="all">All <span class="opacity-75">{{ $employees->count() }}</span></button>
            <button type="button" class="filter-chip" data-filter="active">Active <span class="opacity-75">{{ $activeCount }}</span></button>
            <button type="button" class="filter-chip" data-filter="inactive">Inactive <span class="opacity-75">{{ $inactiveCount }}</span></button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="empTable">
            <thead>
                <tr>
                    <th class="ps-4">Employee</th>
                    <th>Position</th>
                    <th>Site</th>
                    <th>Group</th>
                    @if($isAuthorized)
                        <th class="text-end">Daily Rate</th>
                    @endif
                    <th class="text-center">Status</th>
                    <th class="text-end pe-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr data-status="{{ $employee->status }}" data-search="{{ strtolower($employee->full_name . ' ' . $employee->employee_id . ' ' . $employee->position . ' ' . ($employee->site->name ?? '')) }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="avatar-initial p-0" style="object-fit: cover;" alt="">
                                @else
                                    <div class="avatar-initial">{{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name ?? '', 0, 1)) }}</div>
                                @endif
                                <div>
                                    <div class="fw-semibold" style="font-size: 0.92rem;">{{ $employee->full_name }}</div>
                                    <div class="text-muted" style="font-size: 0.76rem;">{{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size: 0.88rem;">{{ $employee->position ?? '—' }}</td>
                        <td style="font-size: 0.88rem;">{{ $employee->site->name ?? 'Unassigned' }}</td>
                        <td style="font-size: 0.88rem;">{{ $employee->payrollGroup->name ?? 'None' }}</td>
                        @if($isAuthorized)
                            <td class="text-end fw-semibold" style="font-size: 0.88rem;">&#8369;{{ number_format($employee->daily_rate, 2) }}</td>
                        @endif
                        <td class="text-center">
                            @if($employee->status == 'active')
                                <span class="badge badge-green">Active</span>
                            @else
                                <span class="badge">{{ ucfirst($employee->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-light icon-btn" title="View details"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-light icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light icon-btn icon-danger" onclick="return confirm('Archive this employee?')" type="submit" title="Archive"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAuthorized ? 7 : 6 }}" class="text-center py-5">
                            <i class="bi bi-people text-muted fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-3">No employees yet.</p>
                            <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary">Add your first employee</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-none justify-content-between align-items-center" id="empTableFooter">
        <span class="text-muted small" id="empVisibleCount"></span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('empSearch');
    var chips = document.querySelectorAll('.filter-chip');
    var rows = document.querySelectorAll('#empTable tbody tr[data-status]');
    var footer = document.getElementById('empTableFooter');
    var countEl = document.getElementById('empVisibleCount');
    var currentFilter = 'all';

    function applyFilters() {
        var term = (searchInput.value || '').trim().toLowerCase();
        var visible = 0;
        rows.forEach(function(row) {
            var okFilter = currentFilter === 'all' || row.dataset.status === currentFilter;
            var okSearch = !term || row.dataset.search.indexOf(term) !== -1;
            var show = okFilter && okSearch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        countEl.textContent = 'Showing ' + visible + ' of ' + rows.length + ' employees';
        footer.classList.toggle('d-none', visible === rows.length);
        footer.classList.toggle('d-flex', visible !== rows.length);
    }

    searchInput.addEventListener('input', applyFilters);
    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('active'); });
            chip.classList.add('active');
            currentFilter = chip.dataset.filter;
            applyFilters();
        });
    });
});
</script>

<style>
    .avatar-initial {
        width: 34px; height: 34px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 0.72rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #0071e3, #0058b0);
    }
    .avatar-initial:nth-child(even) { background: linear-gradient(135deg, #8e8e93, #636366); }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
    .badge:not([class*="badge-"]):not(.bg-white) { background: #ffe5e3 !important; color: #d02f26 !important; }
    .filter-chip {
        border: none; background: transparent;
        padding: 0.35rem 0.95rem; border-radius: 980px;
        font-size: 0.82rem; font-weight: 500; color: #6e6e73;
        transition: all 0.18s cubic-bezier(0.25,0.1,0.25,1);
    }
    .filter-chip.active {
        background: #fff; color: #1d1d1f;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .icon-btn { padding: 0.3rem 0.55rem; line-height: 1.2; }
    .icon-btn.icon-danger:hover { background: #ffe5e3; color: #d02f26; }
</style>
@endsection
