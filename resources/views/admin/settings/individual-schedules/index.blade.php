@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Individual Schedules</h4>
            <p class="text-muted small">Personalized schedule overrides that bypass Site Blueprints.</p>
        </div>
        <div>
            <a href="{{ route('admin.settings.individual-schedules.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-person-plus-fill me-2"></i> Create Override
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
            {{ session('info') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-dark fw-bold small uppercase">
                        <tr>
                            <th class="ps-4 py-3">Employee Name</th>
                            <th class="py-3">Designated Site</th>
                            <th class="py-3 text-center">Schedule Details</th>
                            <th class="py-3 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-weight: bold;">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold text-dark">{{ $employee->full_name }}</span>
                                        <span class="small text-muted">{{ $employee->employee_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-normal">{{ $employee->site->name ?? 'Unassigned' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    @php
                                        $days = ['M', 'T', 'W', 'Th', 'F', 'S', 'Su'];
                                    @endphp
                                    @foreach($days as $idx => $label)
                                        @php
                                            $sched = $employee->schedules->where('day_of_week', $idx)->first();
                                            $isRest = $sched ? $sched->is_rest_day : true;
                                        @endphp
                                        <div class="d-flex flex-column align-items-center" style="width: 25px;">
                                            <span class="fw-bold text-dark mb-1" style="font-size: 0.75rem;">{{ $label }}</span>
                                            <div class="rounded-circle {{ $isRest ? 'bg-secondary bg-opacity-20' : 'bg-success' }}" 
                                                 style="width: 10px; height: 10px;" 
                                                 title="{{ $isRest ? 'Rest Day' : ($sched->shift->code ?? 'Work') }}"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-normal align-self-center me-2" style="font-size: 0.7rem;">
                                        INDIVIDUAL OVERRIDE
                                    </span>
                                    <a href="{{ route('admin.settings.individual-schedules.edit', $employee->id) }}" class="btn btn-sm btn-light border text-primary rounded-pill px-3">
                                        <i class="bi bi-pencil-fill me-1"></i> Modify
                                    </a>
                                    <form action="{{ route('admin.settings.individual-schedules.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove override? This employee will revert to their Site Schedule.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger rounded-pill px-3">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-5 text-center text-muted italic">
                                <div class="mb-3"><i class="bi bi-person-check" style="font-size: 3rem;"></i></div>
                                No individual overrides found. All employees are currently following their <strong>Site Schedules</strong>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
