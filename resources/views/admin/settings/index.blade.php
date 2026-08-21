@extends('layouts.app')

@section('content')
@php
    $appName = is_array($settings) ? ($settings['app_name'] ?? '') : ($settings->app_name ?? '');
    $logo = is_array($settings) ? ($settings['app_logo'] ?? null) : ($settings->app_logo ?? null);
    if ($logo) {
        $logo = str_starts_with($logo, 'logos/') ? $logo : 'logos/' . $logo;
    }
    $sssRate = (is_array($settings) ? ($settings['sss_rate'] ?? 0.045) : ($settings->sss_rate ?? 0.045)) * 100;
    $pagibigRate = (is_array($settings) ? ($settings['pagibig_rate'] ?? 0.02) : ($settings->pagibig_rate ?? 0.02)) * 100;
    $philhealthRate = (is_array($settings) ? ($settings['philhealth_rate'] ?? 0.05) : ($settings->philhealth_rate ?? 0.05)) * 100;
    $nightDiffRate = (is_array($settings) ? ($settings['night_diff_rate'] ?? 0.10) : ($settings->night_diff_rate ?? 0.10)) * 100;
    $lateRate = is_array($settings) ? ($settings['late_rate'] ?? 1) : ($settings->late_rate ?? 1);
    $undertimeRate = is_array($settings) ? ($settings['undertime_rate'] ?? 1) : ($settings->undertime_rate ?? 1);
@endphp

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">Settings</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Branding, security, and payroll computation defaults.</p>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3 g-md-4">
        <div class="col-lg-8 d-flex flex-column gap-3 gap-md-4">

            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">Branding</h6></div>
                <div class="card-body p-0">
                    <div class="setting-row">
                        <div class="me-4">
                            <label for="app_name" class="fw-semibold d-block" style="font-size: 0.92rem;">System Name</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Shown in the sidebar, login screen, and reports.</span>
                        </div>
                        <div style="width: 280px;">
                            <input type="text" name="app_name" id="app_name" class="form-control" value="{{ $appName }}" required>
                        </div>
                    </div>
                    <div class="setting-row border-bottom-0">
                        <div class="me-4">
                            <label for="app_logo" class="fw-semibold d-block" style="font-size: 0.92rem;">Company Logo</label>
                            <span class="text-muted" style="font-size: 0.8rem;">PNG or SVG recommended &middot; max 2MB.</span>
                        </div>
                        <div style="width: 280px;">
                            <input type="file" name="app_logo" id="app_logo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">Security</h6></div>
                <div class="card-body p-0">
                    <div class="setting-row border-bottom-0">
                        <div class="me-4">
                            <label for="dtr_edit_password" class="fw-semibold d-block" style="font-size: 0.92rem;">DTR Edit Password</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Required for sensitive DTR overrides. Leave blank to keep the current one.</span>
                        </div>
                        <div style="width: 280px;">
                            <input type="password" name="dtr_edit_password" id="dtr_edit_password" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Statutory Rates</h6>
                    <span class="badge badge-blue">Fallback rates</span>
                </div>
                <div class="card-body p-0">
                    <div class="px-4 py-3 small text-muted" style="background: #f5f5f7; border-bottom: 1px solid rgba(0,0,0,0.06);">
                        <i class="bi bi-info-circle me-1"></i>
                        These apply only when no contribution brackets are seeded. Bracket-based tables take priority.
                    </div>
                    <div class="setting-row">
                        <div class="me-4">
                            <label for="sss_rate" class="fw-semibold d-block" style="font-size: 0.92rem;">SSS Contribution</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Employee share, % of monthly salary.</span>
                        </div>
                        <div class="input-group percent-group" style="width: 180px;">
                            <input type="number" step="0.01" min="0" max="100" name="sss_rate_pct" id="sss_rate" class="form-control text-end" value="{{ number_format($sssRate, 2) }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="me-4">
                            <label for="philhealth_rate" class="fw-semibold d-block" style="font-size: 0.92rem;">PhilHealth Contribution</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Employee share, % of monthly salary.</span>
                        </div>
                        <div class="input-group percent-group" style="width: 180px;">
                            <input type="number" step="0.01" min="0" max="100" name="philhealth_rate_pct" id="philhealth_rate" class="form-control text-end" value="{{ number_format($philhealthRate, 2) }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="me-4">
                            <label for="pagibig_rate" class="fw-semibold d-block" style="font-size: 0.92rem;">Pag-IBIG Contribution</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Employee share, % of monthly salary.</span>
                        </div>
                        <div class="input-group percent-group" style="width: 180px;">
                            <input type="number" step="0.01" min="0" max="100" name="pagibig_rate_pct" id="pagibig_rate" class="form-control text-end" value="{{ number_format($pagibigRate, 2) }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="setting-row border-bottom-0">
                        <div class="me-4">
                            <label for="night_diff_rate" class="fw-semibold d-block" style="font-size: 0.92rem;">Night Differential</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Premium on hours worked between 10:00 PM and 6:00 AM.</span>
                        </div>
                        <div class="input-group percent-group" style="width: 180px;">
                            <input type="number" step="0.01" min="0" max="100" name="night_diff_rate_pct" id="night_diff_rate" class="form-control text-end" value="{{ number_format($nightDiffRate, 2) }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">Deduction Multipliers</h6></div>
                <div class="card-body p-0">
                    <div class="setting-row">
                        <div class="me-4">
                            <label for="late_rate" class="fw-semibold d-block" style="font-size: 0.92rem;">Late Deduction</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Multiplier on hourly rate per minute late (1.0 = exact).</span>
                        </div>
                        <div style="width: 180px;">
                            <input type="number" step="0.0001" min="0" name="late_rate" id="late_rate" class="form-control text-end" value="{{ $lateRate }}">
                        </div>
                    </div>
                    <div class="setting-row border-bottom-0">
                        <div class="me-4">
                            <label for="undertime_rate" class="fw-semibold d-block" style="font-size: 0.92rem;">Undertime Deduction</label>
                            <span class="text-muted" style="font-size: 0.8rem;">Multiplier on hourly rate per minute short (1.0 = exact).</span>
                        </div>
                        <div style="width: 180px;">
                            <input type="number" step="0.0001" min="0" name="undertime_rate" id="undertime_rate" class="form-control text-end" value="{{ $undertimeRate }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 pb-2">
                <span class="text-muted small d-none d-sm-inline">Changes apply immediately after saving.</span>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card text-center">
                <div class="card-body p-4">
                    <p class="text-muted fw-semibold mb-3" style="font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;">Current Logo</p>
                    <div class="logo-preview mx-auto d-flex align-items-center justify-content-center mb-3">
                        @if($logo)
                            <img src="/{{ $logo }}" alt="App Logo" id="logoPreview" style="max-height: 110px; max-width: 100%; object-fit: contain;">
                        @else
                            <i class="bi bi-image text-muted fs-1" id="logoPreviewIcon"></i>
                        @endif
                    </div>
                    <p class="text-muted small mb-0">Appears in the sidebar and on the login screen.</p>
                </div>
            </div>

            <div class="card mt-3 mt-md-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-2" style="font-size: 0.92rem;"><i class="bi bi-sliders me-2" style="color: #0071e3;"></i>Payroll Configuration</h6>
                    <p class="text-muted small mb-3">Contribution brackets, withholding tax tables, and payroll group rules live in their own section.</p>
                    <a href="{{ route('admin.payroll-settings.index') }}" class="btn btn-light btn-sm w-100">Open Payroll Settings<i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>

            <div class="card mt-3 mt-md-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-2" style="font-size: 0.92rem;"><i class="bi bi-shield-check me-2" style="color: #34c759;"></i>Audit Trail</h6>
                    <p class="text-muted small mb-3">Every change to these settings is recorded in the audit log with the previous values.</p>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light btn-sm w-100">View Audit Logs<i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('app_logo');
    if (!input) return;
    input.addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        var container = this.closest('.card-body') ? document.querySelector('.logo-preview') : null;
        if (!container) return;
        var img = document.getElementById('logoPreview');
        if (!img) {
            container.innerHTML = '<img id="logoPreview" style="max-height: 110px; max-width: 100%; object-fit: contain;">';
            img = document.getElementById('logoPreview');
        }
        img.src = URL.createObjectURL(file);
    });
});
</script>

<style>
    .setting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.35rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        gap: 1rem;
    }
    .setting-row:first-child { border-top: 0; }
    .percent-group .input-group-text {
        background: #f5f5f7;
        font-weight: 500;
        color: #6e6e73;
        font-size: 0.9rem;
    }
    .percent-group .form-control { border-radius: 12px 0 0 12px; }
    .percent-group .input-group-text { border-radius: 0 12px 12px 0; }
    .logo-preview {
        width: 160px; height: 130px;
        background: #f5f5f7;
        border-radius: 16px;
        overflow: hidden;
        padding: 12px;
    }
    .badge-blue { background: rgba(0,113,227,0.1); color: #0071e3; }
    @media (max-width: 575.98px) {
        .setting-row { flex-direction: column; align-items: stretch; }
        .setting-row > div[style*="width"] { width: 100% !important; }
    }
</style>
@endsection
