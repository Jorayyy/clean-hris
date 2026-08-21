@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $canSeePay = $user->is_super_admin || $user->hasRole('Accounting Admin');
    $hiredAt = $employee->date_employed ? \Carbon\Carbon::parse($employee->date_employed) : null;
    $tenure = $hiredAt ? ($hiredAt->diffInYears(now()) >= 1 ? $hiredAt->diffInYears(now()) . ' yr ' . floor($hiredAt->copy()->addYears((int) $hiredAt->diffInYears(now()))->diffInMonths(now())) . ' mo' : $hiredAt->diffInMonths(now()) . ' mo') : null;
    $birthday = $employee->birthday ? \Carbon\Carbon::parse($employee->birthday) : null;
    $todaySchedule = $employee->activeSchedule;
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left me-1"></i>Back</a>
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary px-4"><i class="bi bi-pencil me-2"></i>Edit Profile</a>
</div>

<div class="card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center gap-4">
            @if($employee->photo)
                <img src="{{ asset('storage/' . $employee->photo) }}" class="profile-photo" alt="">
            @else
                <div class="profile-photo profile-initial">{{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name ?? '', 0, 1)) }}</div>
            @endif
            <div class="flex-grow-1 min-width-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 class="fw-bold mb-0" style="font-size: 1.6rem; letter-spacing: -0.03em;">{{ $employee->full_name }}</h2>
                    @if($employee->status == 'active')
                        <span class="badge badge-green">Active</span>
                    @else
                        <span class="badge">{{ ucfirst($employee->status) }}</span>
                    @endif
                </div>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.95rem;">
                    {{ $employee->position ?? 'No position' }}
                    @if($employee->site)
                        &middot; {{ $employee->site->name }}
                    @endif
                    @if($tenure)
                        &middot; {{ $tenure }} with the company
                    @endif
                </p>
            </div>
        </div>

        <div class="row g-3 mt-4 pt-1">
            @if($canSeePay)
                <div class="col-6 col-md-3">
                    <div class="fact-tile">
                        <span class="fact-label">Daily Rate</span>
                        <span class="fact-value">&#8369;{{ number_format($employee->daily_rate, 2) }}</span>
                    </div>
                </div>
            @endif
            <div class="col-6 {{ $canSeePay ? 'col-md-3' : 'col-md-4' }}">
                <div class="fact-tile">
                    <span class="fact-label">Employment Type</span>
                    <span class="fact-value text-capitalize">{{ str_replace('_', ' ', $employee->employment_type ?? '—') }}</span>
                </div>
            </div>
            <div class="col-6 {{ $canSeePay ? 'col-md-3' : 'col-md-4' }}">
                <div class="fact-tile">
                    <span class="fact-label">Date Employed</span>
                    <span class="fact-value">{{ $hiredAt?->format('M d, Y') ?? '—' }}</span>
                </div>
            </div>
            <div class="col-6 {{ $canSeePay ? 'col-md-3' : 'col-md-4' }}">
                <div class="fact-tile">
                    <span class="fact-label">Today's Schedule</span>
                    @if($todaySchedule && ($todaySchedule->is_rest_day ?? false))
                        <span class="fact-value text-muted" style="font-size: 0.95rem;">Rest day</span>
                    @elseif($todaySchedule)
                        <span class="fact-value" style="font-size: 0.95rem;">{{ $todaySchedule->name ?? $todaySchedule->shift?->name ?? 'Custom times' }}</span>
                        @if($todaySchedule->time_in && $todaySchedule->time_out)
                            <span class="text-muted d-block" style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($todaySchedule->time_in)->format('g:i A') }} &ndash; {{ \Carbon\Carbon::parse($todaySchedule->time_out)->format('g:i A') }}</span>
                        @endif
                    @else
                        <span class="fact-value text-muted" style="font-size: 0.95rem;">None</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-briefcase me-2"></i>Employment</h6>
                <div class="detail-row"><span>Employee ID</span><strong>{{ $employee->employee_id }}</strong></div>
                <div class="detail-row"><span>Position</span><strong>{{ $employee->position ?? '—' }}</strong></div>
                <div class="detail-row"><span>Site</span><strong>{{ $employee->site->name ?? 'Unassigned' }}</strong></div>
                <div class="detail-row"><span>Payroll Group</span><strong>{{ $employee->payrollGroup->name ?? 'None' }}</strong></div>
                <div class="detail-row"><span>Classification</span><strong>{{ $employee->classification ?? '—' }}</strong></div>
                <div class="detail-row"><span>Level</span><strong>{{ $employee->level ?? '—' }}</strong></div>
                <div class="detail-row"><span>Reports To</span><strong>{{ $employee->report_to ?? '—' }}</strong></div>
                <div class="detail-row"><span>Pay Type</span><strong class="text-capitalize">{{ str_replace('_', ' ', $employee->pay_type ?? '—') }}</strong></div>
                @if($canSeePay)
                    <div class="detail-row"><span>Tax Code</span><strong>{{ $employee->tax_code ?? '—' }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-person-lines-fill me-2"></i>Contact & Personal</h6>
                <div class="detail-row"><span>Email</span><strong class="text-break">{{ $employee->email ?? '—' }}</strong></div>
                <div class="detail-row"><span>Mobile</span><strong>+63 {{ $employee->mobile_no_1 ?? '—' }}@if($employee->mobile_no_2) &nbsp;&middot;&nbsp; +63 {{ $employee->mobile_no_2 }}@endif</strong></div>
                @if($employee->tel_no_1 || $employee->tel_no_2)
                    <div class="detail-row"><span>Telephone</span><strong>{{ $employee->tel_no_1 }}@if($employee->tel_no_2) &middot; {{ $employee->tel_no_2 }}@endif</strong></div>
                @endif
                <div class="detail-row"><span>Birthday</span><strong>{{ $birthday?->format('M d, Y') ?? '—' }}@if($birthday) <span class="text-muted fw-normal">({{ $birthday->age }})</span>@endif</strong></div>
                <div class="detail-row"><span>Gender</span><strong class="text-capitalize">{{ $employee->gender ?? '—' }}</strong></div>
                <div class="detail-row"><span>Civil Status</span><strong class="text-capitalize">{{ $employee->civil_status ?? '—' }}</strong></div>
                @if($employee->place_of_birth)
                    <div class="detail-row"><span>Place of Birth</span><strong>{{ $employee->place_of_birth }}</strong></div>
                @endif
                @if($employee->blood_type || $employee->citizenship || $employee->religion)
                    <div class="detail-row"><span>Other</span><strong>{{ collect([$employee->blood_type, $employee->citizenship, $employee->religion])->filter()->implode(' · ') }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    @if($canSeePay)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h6 class="section-title"><i class="bi bi-shield-lock me-2"></i>Government IDs &amp; Banking</h6>
                    <div class="detail-row"><span>TIN</span><strong class="mono">{{ $employee->tin_no ?? '—' }}</strong></div>
                    <div class="detail-row"><span>SSS</span><strong class="mono">{{ $employee->sss_no ?? '—' }}</strong></div>
                    <div class="detail-row"><span>PhilHealth</span><strong class="mono">{{ $employee->philhealth_no ?? '—' }}</strong></div>
                    <div class="detail-row"><span>Pag-IBIG</span><strong class="mono">{{ $employee->pagibig_no ?? '—' }}</strong></div>
                    <div class="detail-row"><span>Main Bank</span><strong>{{ $employee->bank_name ?? '—' }}@if($employee->account_no) <span class="mono text-muted fw-normal ms-1">{{ $employee->account_no }}</span>@endif</strong></div>
                    @if($employee->rcbc_no)
                        <div class="detail-row"><span>RCBC</span><strong class="mono">{{ $employee->rcbc_no }}</strong></div>
                    @endif
                    @if($employee->palawan_pay_no)
                        <div class="detail-row"><span>PalawanPay</span><strong class="mono">{{ $employee->palawan_pay_no }}</strong></div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="col-lg-{{ $canSeePay ? '6' : '12' }}">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-house-door me-2"></i>Addresses</h6>
                <div class="detail-row align-items-start">
                    <span>Permanent</span>
                    <strong class="text-end">@if($employee->permanent_address_brgy || $employee->permanent_address_province){{ trim($employee->permanent_address_brgy . ', ' . $employee->permanent_address_province, ', ') }}@else—@endif</strong>
                </div>
                <div class="detail-row align-items-start">
                    <span>Present</span>
                    <strong class="text-end">@if($employee->present_address_brgy || $employee->present_address_province){{ trim($employee->present_address_brgy . ', ' . $employee->present_address_province, ', ') }}@else—@endif</strong>
                </div>
                @if($employee->other_information)
                    <div class="detail-row align-items-start mb-0">
                        <span>Notes</span>
                        <strong class="text-end fw-normal" style="max-width: 60%;">{{ $employee->other_information }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body p-3 d-flex justify-content-end">
        <a href="{{ route('attendance.employee.show', ['employee' => $employee->id]) }}" class="btn btn-light"><i class="bi bi-calendar-check me-2"></i>View Attendance History</a>
    </div>
</div>

<style>
    .profile-photo {
        width: 96px; height: 96px; flex-shrink: 0;
        border-radius: 50%; object-fit: cover;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .profile-initial {
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #0071e3, #0058b0);
    }
    .min-width-0 { min-width: 0; }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
    .badge:not([class*="badge-"]):not(.bg-white) { background: #ffe5e3 !important; color: #d02f26 !important; }
    .fact-tile {
        background: #f5f5f7; border-radius: 14px;
        padding: 0.85rem 1rem; height: 100%;
    }
    .fact-label {
        display: block; font-size: 0.7rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.04em;
        color: #86868b; margin-bottom: 0.25rem;
    }
    .fact-value {
        font-size: 1.05rem; font-weight: 600; color: #1d1d1f;
        letter-spacing: -0.01em;
    }
    .section-title {
        font-size: 0.78rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.06em; color: #86868b;
        padding-bottom: 0.75rem; margin-bottom: 0.35rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .detail-row {
        display: flex; justify-content: space-between; align-items: center;
        gap: 1rem; padding: 0.65rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.045);
        font-size: 0.88rem;
    }
    .detail-row:last-child { border-bottom: none; margin-bottom: 0; }
    .detail-row > span { color: #6e6e73; flex-shrink: 0; }
    .mono {
        font-family: ui-monospace, "SF Mono", SFMono-Regular, Menlo, monospace;
        font-size: 0.82rem; letter-spacing: 0.02em;
    }
</style>
@endsection
