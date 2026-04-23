@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Generate Daily Time Records (DTR)</h4>
    </div>
    <div class="col-auto">
        <div class="btn-group me-2" id="batchActions" style="display: none;">
            <div class="dropdown me-1">
                <button type="button" class="btn btn-outline-primary fw-bold dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-shield-check me-1"></i> Batch Authorize
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_all')"><i class="bi bi-check-all text-primary me-2"></i>Authorize All Extras</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_ot')"><i class="bi bi-clock-history text-success me-2"></i>Authorize OT Only</button></li>
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_nd')"><i class="bi bi-moon-stars text-info me-2"></i>Authorize Night Diff Only</button></li>
                    <li><button type="button" class="dropdown-item py-2" onclick="submitBatchAuth('authorize_holiday')"><i class="bi bi-calendar-event text-warning me-2"></i>Authorize Holiday Only</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item py-2 text-danger" onclick="submitBatchAuth('unauthorize_all')"><i class="bi bi-x-circle me-2"></i>Unauthorize All</button></li>
                </ul>
            </div>
            <button type="button" class="btn btn-info fw-bold" id="batchVerifyBtn" data-bs-toggle="modal" data-bs-target="#batchVerifyModal">
                <i class="bi bi-check2-square me-1"></i> Verify Selected
            </button>
            <button type="button" class="btn btn-success fw-bold" id="batchFinalizeBtn" data-bs-toggle="modal" data-bs-target="#batchFinalizeModal">
                <i class="bi bi-lock-fill me-1"></i> Finalize Selected
            </button>
            <button type="button" class="btn btn-danger fw-bold" id="batchDeleteBtn" data-bs-toggle="modal" data-bs-target="#batchDeleteModal">
                <i class="bi bi-trash-fill me-1"></i> Delete Selected
            </button>
        </div>
        <form action="{{ route('admin.dtrs.index') }}" method="GET" class="d-inline-flex align-items-center bg-white rounded shadow-sm border p-1" style="min-width: 300px;">
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
                            <div class="fw-bold">{{ $dtr->employee->full_name }}</div>
                            <small class="text-muted">{{ $dtr->employee->employee_id }}</small>
                        </td>
                        <td>
                            <i class="bi bi-calendar3 small text-muted me-1"></i>
                            {{ $dtr->start_date->format('M d') }} - {{ $dtr->end_date->format('M d, Y') }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark fw-normal border text-uppercase">Reg: {{ $dtr->total_regular_hours }}h</span>
                            @if($dtr->total_overtime_hours > 0)
                                <br/>
                                @if($dtr->is_ot_authorized)
                                    <span class="badge bg-success-subtle text-success fw-bold border border-success border-opacity-25 text-uppercase mt-1" title="Authorized OT">
                                        <i class="bi bi-shield-check me-1"></i> OT: {{ $dtr->total_overtime_hours }}h
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary fw-normal border border-secondary border-opacity-25 text-uppercase mt-1" title="Unauthorized OT (Will not be paid)">
                                        <i class="bi bi-shield-slash me-1"></i> OT: {{ $dtr->total_overtime_hours }}h
                                    </span>
                                @endif
                            @endif
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
                                <div class="btn-group">
                                    <a href="{{ route('admin.dtrs.show', $dtr->id) }}" class="btn btn-sm btn-outline-primary" title="Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if($dtr->status !== 'finalized')
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" data-bs-toggle="modal" data-bs-target="#editDtrModal{{ $dtr->id }}" title="Edit Record">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    @endif
                                    @if($dtr->status == 'draft')
                                    <button type="button" class="btn btn-sm btn-outline-info fw-bold border-2 ms-1" data-bs-toggle="modal" data-bs-target="#verifyDtrModal{{ $dtr->id }}" title="Verify DTR">
                                        <i class="bi bi-check2-square"></i> Verify
                                    </button>
                                    @elseif($dtr->status == 'verified')
                                    <button type="button" class="btn btn-sm btn-outline-success fw-bold border-2 ms-1" data-bs-toggle="modal" data-bs-target="#finalizeDtrModal{{ $dtr->id }}" title="Finalize DTR">
                                        <i class="bi bi-lock-fill"></i> Finalize
                                    </button>
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#deleteDtrModal{{ $dtr->id }}" title="Delete record" {{ $dtr->status === 'finalized' && Auth::user()->role !== 'admin' ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Verify Modal -->
                                <div class="modal fade" id="verifyDtrModal{{ $dtr->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-info text-white">
                                                <h5 class="modal-title">Verify DTR Record</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">Finalize & Lock DTR</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title">Edit DTR Metrics</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                                                            <span class="badge bg-dark px-3 mt-2">PAYMENT AUTHORIZATIONS</span>
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
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Confirm DTR Deletion</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<!-- Batch Verify Modal -->
<div class="modal fade" id="batchVerifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Batch Verify DTRs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Batch Finalize DTRs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Batch Delete DTRs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dtrs.batch-delete') }}" method="POST" id="batchDeleteForm">
                @csrf
                <div id="batchDeleteInputs"></div>
                <div class="modal-body">
                    <p>You are about to delete <strong id="batchDeleteCount">0</strong> selected DTR records.</p>
                    <div class="alert alert-danger small">
                        <i class="bi bi-exclamation-triangle-fill"></i> This action is permanent and cannot be undone. 
                        @if(Auth::user()->role !== 'admin')
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
@endsection
