@extends('layouts.app')

@section('content')
@php
    if (!function_exists('formatDtrMinutes')) {
        function formatDtrMinutes($totalMinutes) {
            if ($totalMinutes <= 0) return '0m';
            
            $days = floor($totalMinutes / (8 * 60));
            $remainingMinutes = $totalMinutes % (8 * 60);
            $hours = floor($remainingMinutes / 60);
            $minutes = $remainingMinutes % 60;
            
            $parts = [];
            if ($days > 0) $parts[] = $days . 'd';
            if ($hours > 0) $parts[] = $hours . 'h';
            if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';
            
            return implode(' ', $parts);
        }
    }
@endphp
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: -0.03em;">Daily Time Records</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Generate, verify, and lock attendance summaries before payroll.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div id="batchActions" style="display: none;" class="d-inline-flex gap-2">
            <div class="dropdown">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-shield-check me-1"></i> Batch Authorize
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_all')"><i class="bi bi-check-all me-2" style="color:#0071e3;"></i>Authorize All Extras</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_ot')"><i class="bi bi-clock-history me-2" style="color:#157347;"></i>Authorize OT Only</button></li>
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_nd')"><i class="bi bi-moon-stars me-2" style="color:#0b6a99;"></i>Authorize Night Diff Only</button></li>
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_holiday')"><i class="bi bi-calendar-event me-2" style="color:#995f00;"></i>Authorize Holiday Only</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item py-2" style="color:#d02f26;" onclick="submitBatchAuth('unauthorize_all')"><i class="bi bi-x-circle me-2"></i>Unauthorize All</button></li>
                </ul>
            </div>
            <button type="button" class="btn btn-light" id="batchVerifyBtn" data-bs-toggle="modal" data-bs-target="#batchVerifyModal">
                <i class="bi bi-check2-square me-1"></i> Verify Selected
            </button>
            <button type="button" class="btn btn-light" id="batchFinalizeBtn" data-bs-toggle="modal" data-bs-target="#batchFinalizeModal">
                <i class="bi bi-lock me-1"></i> Finalize Selected
            </button>
            <button type="button" class="btn btn-light" id="batchDeleteBtn" data-bs-toggle="modal" data-bs-target="#batchDeleteModal">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
        </div>
        <select name="period" class="form-select" style="width: auto; min-width: 200px;" onchange="const [start, end] = this.value.split('|'); if(start && end) { window.location.href = `{{ route('admin.dtrs.index') }}?start_date=${start}&end_date=${end}`; } else { window.location.href = `{{ route('admin.dtrs.index') }}`; }">
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
        <a href="{{ route('admin.dtrs.create') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-lg me-2"></i>Generate New DTR
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3 small text-muted">
            <span class="fw-medium" style="color:#494949;">Workflow:</span>
            <span class="stage-dot"></span><span>Draft</span>
            <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
            <span class="badge badge-blue">Verify</span>
            <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
            <span class="badge badge-green">Finalize</span>
            <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
            <span class="badge badge-orange">Payroll Batch</span>
        </div>
        <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-cash-stack me-1"></i> Open Payroll Workspace
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Employee</th>
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
                            <input type="checkbox" class="form-check-input dtr-checkbox" value="{{ $dtr->id }}" data-status="{{ $dtr->status }}">
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size: 0.92rem;">{{ $dtr->employee->full_name }}</div>
                            <small class="text-muted" style="font-size: 0.76rem;">{{ $dtr->employee->employee_id }}</small>
                        </td>
                        <td style="font-size: 0.88rem;">
                            {{ $dtr->start_date->format('M d') }} - {{ $dtr->end_date->format('M d, Y') }}
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary">Reg: {{ $dtr->total_regular_hours }}h</span>
                            @if($dtr->total_overtime_hours > 0)
                                <br/>
                                @if($dtr->is_ot_authorized)
                                    <span class="badge badge-green mt-1" title="Authorized OT">
                                        <i class="bi bi-shield-check me-1"></i> OT: {{ $dtr->total_overtime_hours }}h
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary mt-1" title="Unauthorized OT (Will not be paid)">
                                        <i class="bi bi-shield-slash me-1"></i> OT: {{ $dtr->total_overtime_hours }}h
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td style="font-size: 0.85rem;">
                            @if($dtr->total_regular_hours == 0)
                                @if($dtr->admin_notes && str_contains($dtr->admin_notes, 'incomplete'))
                                    <span class="badge"><i class="bi bi-exclamation-triangle-fill me-1"></i>INCOMPLETE LOGS</span>
                                @else
                                    <span class="text-muted">Absent / no logs</span>
                                @endif
                            @else
                                @if($dtr->total_late_minutes > 0)
                                    <span class="fw-semibold" style="color:#d02f26;">Late: {{ formatDtrMinutes($dtr->total_late_minutes) }}</span><br/>
                                @endif
                                @if($dtr->total_undertime_minutes > 0)
                                    <span class="fw-semibold" style="color:#995f00;">UT: {{ formatDtrMinutes($dtr->total_undertime_minutes) }}</span>
                                @endif
                                @if($dtr->total_late_minutes == 0 && $dtr->total_undertime_minutes == 0)
                                    <span class="fw-semibold" style="color:#157347;"><i class="bi bi-check2-circle me-1"></i>Perfect</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($dtr->status == 'draft')
                                <span class="badge badge-orange">Draft</span>
                            @elseif($dtr->status == 'verified')
                                <span class="badge badge-blue">Verified</span>
                            @else
                                <span class="badge badge-green">Finalized</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('admin.dtrs.show', $dtr->id) }}" class="btn btn-sm btn-light icon-btn" title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('payroll.index', ['start_date' => $dtr->start_date->format('Y-m-d'), 'end_date' => $dtr->end_date->format('Y-m-d')]) }}" class="btn btn-sm btn-light icon-btn" title="Open related payroll period">
                                    <i class="bi bi-cash-coin"></i>
                                </a>
                                @if($dtr->status !== 'finalized')
                                <button type="button" class="btn btn-sm btn-light icon-btn" data-bs-toggle="modal" data-bs-target="#editDtrModal{{ $dtr->id }}" title="Edit Record">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif
                                @if($dtr->status == 'draft')
                                <button type="button" class="btn btn-sm btn-primary btn-sm-pill"
                                    @if($dtr->total_regular_hours <= 0) disabled title="Empty records cannot be verified" @else data-bs-toggle="modal" data-bs-target="#verifyDtrModal{{ $dtr->id }}" @endif>
                                    Verify
                                </button>
                                @elseif($dtr->status == 'verified')
                                <button type="button" class="btn btn-sm btn-success btn-sm-pill" data-bs-toggle="modal" data-bs-target="#finalizeDtrModal{{ $dtr->id }}" title="Finalize DTR">
                                    Finalize
                                </button>
                                @endif

                                <button type="button" class="btn btn-sm btn-light icon-btn icon-danger" data-bs-toggle="modal" data-bs-target="#deleteDtrModal{{ $dtr->id }}" title="Delete record" {{ $dtr->status === 'finalized' && !Auth::user()->isAdmin() ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                                <!-- Verify Modal -->
                                <div class="modal fade" id="verifyDtrModal{{ $dtr->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Verify DTR Record</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.dtrs.verify', $dtr->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <div class="modal-body text-start">
                                                    <p>You are about to verify the DTR for <strong>{{ $dtr->employee->full_name }}</strong>.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Enter Security Password</label>
                                                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-info px-4">Confirm Verification</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Finalize Modal -->
                                <div class="modal fade" id="finalizeDtrModal{{ $dtr->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Finalize & Lock DTR</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.dtrs.finalize', $dtr->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <div class="modal-body text-start">
                                                    <div class="alert alert-warning small">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Finalizing will lock this record for payroll processing. 
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Enter Security Password</label>
                                                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success px-4">Finalize Record</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editDtrModal{{ $dtr->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit DTR Metrics</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.dtrs.update', $dtr->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body text-start">
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Regular Hours</label>
                                                            <input type="number" name="total_regular_hours" class="form-control" value="{{ $dtr->total_regular_hours }}" step="0.5" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Overtime Hours</label>
                                                            <input type="number" name="total_overtime_hours" class="form-control" value="{{ $dtr->total_overtime_hours }}" step="0.5" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Night Diff Hours</label>
                                                            <input type="number" name="total_night_diff_hours" class="form-control" value="{{ $dtr->total_night_diff_hours ?? 0 }}" step="0.5">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Holiday Hours</label>
                                                            <input type="number" name="total_holiday_hours" class="form-control" value="{{ $dtr->total_holiday_hours ?? 0 }}" step="0.5">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Late (Mins)</label>
                                                            <input type="number" name="total_late_minutes" class="form-control" value="{{ $dtr->total_late_minutes }}" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Undertime (Mins)</label>
                                                            <input type="number" name="total_undertime_minutes" class="form-control" value="{{ $dtr->total_undertime_minutes }}" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold">Incentives (Solds/Spiffs)</label>
                                                            <input type="number" name="incentives" class="form-control" value="{{ $dtr->incentives ?? 0 }}" step="0.01">
                                                        </div>
                                                        <div class="col-12 text-center py-2">
                                                            <span class="badge bg-secondary-subtle text-secondary px-3 mt-2">PAYMENT AUTHORIZATIONS</span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check form-switch bg-light p-2 rounded border h-100">
                                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_ot_authorized" value="1" id="authOt{{ $dtr->id }}" {{ $dtr->is_ot_authorized ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold small text-primary" for="authOt{{ $dtr->id }}">OT</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check form-switch bg-light p-2 rounded border h-100">
                                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_nd_authorized" value="1" id="authNd{{ $dtr->id }}" {{ $dtr->is_nd_authorized ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold small text-info" for="authNd{{ $dtr->id }}">Night Diff</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check form-switch bg-light p-2 rounded border h-100">
                                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_holiday_authorized" value="1" id="authHol{{ $dtr->id }}" {{ $dtr->is_holiday_authorized ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold small text-success" for="authHol{{ $dtr->id }}">Holiday</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <p class="small text-muted mb-0 mt-1">Checked items will be automatically calculated during payslip generation.</p>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold">Admin Notes</label>
                                                            <textarea name="admin_notes" class="form-control" rows="2">{{ $dtr->admin_notes }}</textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <hr>
                                                            <label class="form-label fw-bold text-danger">Identity Verification</label>
                                                            <input type="password" name="admin_password" class="form-control" placeholder="Enter System Security Password" required>
                                                            <small class="text-muted">Requires the specialized security password or your admin login password.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary px-4">Save Updates</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteDtrModal{{ $dtr->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm DTR Deletion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.dtrs.destroy', $dtr->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <div class="modal-body text-start">
                                                    <p class="mb-3">Deleting DTR for <strong>{{ $dtr->employee->full_name }}</strong> ({{ $dtr->start_date->format('M d') }} - {{ $dtr->end_date->format('M d, Y') }}).</p>
                                                    
                                                    @if($dtr->status === 'finalized')
                                                        <div class="alert alert-warning small">
                                                            <i class="bi bi-exclamation-triangle-fill"></i> This DTR is <strong>Finalized</strong>. Deleting it may affect payroll records.
                                                        </div>
                                                    @endif

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Enter Admin Password to Proceed:</label>
                                                        <input type="password" name="admin_password" class="form-control" placeholder="Required for audit trailing" required>
                                                    </div>
                                                    <p class="text-muted small"><em>All deletions are logged in the Transactions/Audit log for security monitoring.</em></p>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger px-4">Delete Record</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-file-earmark-check text-muted fs-2 d-block mb-2"></i>
                            <p class="text-muted small mb-3">No DTR records found for this period.</p>
                            <a href="{{ route('admin.dtrs.create') }}" class="btn btn-sm btn-primary">Generate your first DTR</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-4">
    {{ $dtrs->links() }}
</div>

<!-- Batch Verify Modal -->
<div class="modal fade" id="batchVerifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">Batch Verify DTRs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dtrs.batch-verify') }}" method="POST" id="batchVerifyForm">
                @csrf
                <div id="batchVerifyInputs"></div>
                <div class="modal-body">
                    <p>You are about to verify <strong id="batchVerifyCount">0</strong> selected DTR records.</p>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle-fill"></i> Only <strong>Draft</strong> records will be updated.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Enter Security Password</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info px-4">Verify All Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch Authorize Hidden Form -->
<form id="batchAuthForm" action="{{ route('admin.dtrs.batch-authorize') }}" method="POST" style="display: none;">
    @csrf
    <div id="batchAuthInputs"></div>
    <input type="hidden" name="action" id="batchAuthAction">
</form>

<!-- Batch Finalize Modal -->
<div class="modal fade" id="batchFinalizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">Batch Finalize DTRs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dtrs.batch-finalize') }}" method="POST" id="batchFinalizeForm">
                @csrf
                <div id="batchFinalizeInputs"></div>
                <div class="modal-body">
                    <p>You are about to finalize and lock <strong id="batchFinalizeCount">0</strong> selected DTR records.</p>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle-fill"></i> Only <strong>Verified</strong> records will be updated. Finalizing locks them for payroll.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Enter Security Password</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Finalize All Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch Delete Modal -->
<div class="modal fade" id="batchDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">Batch Delete DTRs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dtrs.batch-delete') }}" method="POST" id="batchDeleteForm">
                @csrf
                <div id="batchDeleteInputs"></div>
                <div class="modal-body">
                    <p>You are about to delete <strong id="batchDeleteCount">0</strong> selected DTR records.</p>
                    <div class="alert alert-danger small">
                        <i class="bi bi-exclamation-triangle-fill"></i> This action is permanent and cannot be undone. 
                        @if(!Auth::user()->isAdmin())
                            <br/><strong>Note:</strong> Finalized records will be skipped.
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Enter Security Password</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Required to proceed" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Delete All Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.dtr-checkbox');
    const batchActions = document.getElementById('batchActions');
    const batchVerifyBtn = document.getElementById('batchVerifyBtn');
    const batchFinalizeBtn = document.getElementById('batchFinalizeBtn');
    const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    
    const batchVerifyForm = document.getElementById('batchVerifyForm');
    const batchVerifyInputs = document.getElementById('batchVerifyInputs');
    const batchVerifyCount = document.getElementById('batchVerifyCount');
    
    const batchFinalizeForm = document.getElementById('batchFinalizeForm');
    const batchFinalizeInputs = document.getElementById('batchFinalizeInputs');
    const batchFinalizeCount = document.getElementById('batchFinalizeCount');

    const batchDeleteForm = document.getElementById('batchDeleteForm');
    const batchDeleteInputs = document.getElementById('batchDeleteInputs');
    const batchDeleteCount = document.getElementById('batchDeleteCount');

    const batchAuthForm = document.getElementById('batchAuthForm');
    const batchAuthInputs = document.getElementById('batchAuthInputs');
    const batchAuthAction = document.getElementById('batchAuthAction');

    window.submitBatchAuth = function(action) {
        const checkedBoxes = document.querySelectorAll('.dtr-checkbox:checked');
        if (checkedBoxes.length === 0) return;
        
        if (confirm(`Are you sure you want to ${action.replace('_', ' ')} for ${checkedBoxes.length} selected records?`)) {
            batchAuthInputs.innerHTML = '';
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                batchAuthInputs.appendChild(input);
            });
            batchAuthAction.value = action;
            batchAuthForm.submit();
        }
    };

    function updateBatchButtonVisibility() {
        const checkedBoxes = document.querySelectorAll('.dtr-checkbox:checked');
        const checkedCount = checkedBoxes.length;
        batchActions.style.display = checkedCount > 0 ? 'inline-flex' : 'none';
        
        let hasDraft = false;
        let hasVerified = false;

        // Prepare hidden inputs for forms
        batchVerifyInputs.innerHTML = '';
        batchFinalizeInputs.innerHTML = '';
        batchDeleteInputs.innerHTML = '';
        
        checkedBoxes.forEach(cb => {
            const status = cb.getAttribute('data-status');
            if (status === 'draft') hasDraft = true;
            if (status === 'verified') hasVerified = true;

            const inputV = document.createElement('input');
            inputV.type = 'hidden';
            inputV.name = 'ids[]';
            inputV.value = cb.value;
            batchVerifyInputs.appendChild(inputV);

            const inputF = document.createElement('input');
            inputF.type = 'hidden';
            inputF.name = 'ids[]';
            inputF.value = cb.value;
            batchFinalizeInputs.appendChild(inputF);

            const inputD = document.createElement('input');
            inputD.type = 'hidden';
            inputD.name = 'ids[]';
            inputD.value = cb.value;
            batchDeleteInputs.appendChild(inputD);
        });

        // Disable buttons based on the status of selected items
        batchVerifyBtn.disabled = !hasDraft;
        batchVerifyBtn.style.opacity = hasDraft ? '1' : '0.5';
        
        batchFinalizeBtn.disabled = !hasVerified;
        batchFinalizeBtn.style.opacity = hasVerified ? '1' : '0.5';

        batchVerifyCount.innerText = checkedCount;
        batchFinalizeCount.innerText = checkedCount;
        batchDeleteCount.innerText = checkedCount;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBatchButtonVisibility();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateBatchButtonVisibility();
            if(!this.checked) selectAll.checked = false;
            if(document.querySelectorAll('.dtr-checkbox:checked').length === checkboxes.length) selectAll.checked = true;
        });
    });
});
</script>
@endpush

<style>
    .badge-orange { background: #ffefd6 !important; color: #995f00 !important; }
    .badge-blue { background: rgba(0,113,227,0.1) !important; color: #0071e3 !important; }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
    .stage-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #0071e3; display: inline-block;
    }
    .icon-btn { padding: 0.3rem 0.55rem; line-height: 1.2; }
    .icon-btn.icon-danger:hover { background: #ffe5e3; color: #d02f26; }
    .btn-sm-pill { border-radius: 980px; padding-left: 0.9rem; padding-right: 0.9rem; font-size: 0.8rem; }
</style>
@endsection
