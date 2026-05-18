@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.schedule-groups.index') }}">Schedule Groups</a></li>
                <li class="breadcrumb-item active">Create Group</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Create Schedule Group</h2>
        <p class="text-muted">Define a template schedule that can be used by multiple accounts.</p>
    </div>

    <form action="{{ route('admin.settings.schedule-groups.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Group Details</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Group Name</label>
                            <input type="text" name="name" class="form-control rounded-pill @error('name') is-invalid @enderror" placeholder="e.g. Standard 8-5 Shift" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control rounded-4 @error('description') is-invalid @enderror" rows="3" placeholder="Describe when this schedule applies...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 mt-3">
                            Save Schedule Group
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Daily Schedule Plotting</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle">
                                <thead>
                                    <tr class="text-muted small uppercase">
                                        <th style="width: 150px">Day</th>
                                        <th>Select Schedule Template</th>
                                        <th class="text-center">Is REST Day?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($days as $day)
                                    <tr class="border-bottom">
                                        <td><span class="fw-bold">{{ $day }}</span></td>
                                        <td>
                                            <select name="schedule[{{ $day }}][id]" class="form-select rounded-pill">
                                                <option value="">-- No Schedule --</option>
                                                @foreach($schedules as $sched)
                                                    <option value="{{ $sched->id }}" {{ old("schedule.$day.id") == $sched->id ? 'selected' : '' }}>
                                                        {{ $sched->name }} ({{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="schedule[{{ $day }}][is_rest_day]" value="1" {{ old("schedule.$day.is_rest_day") ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
