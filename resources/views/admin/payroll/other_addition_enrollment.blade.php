@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-0">Other Addition Employee Enrollment</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payroll-settings.index') }}">Payroll Settings</a></li>
                    <li class="breadcrumb-item active">Other Addition Enrollment</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <form action="{{ route('admin.payroll.other-addition-enrollment.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4 row">
                            <label class="col-sm-4 col-form-label fw-bold">Pay Type</label>
                            <div class="col-sm-8">
                                <select name="pay_type" class="form-select bg-light border-0">
                                    <option value="Weekly">Weekly</option>
                                    <option value="Semi-Monthly">Semi-Monthly</option>
                                    <option value="Monthly">Monthly</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label class="col-sm-4 col-form-label fw-bold">Employee Group</label>
                            <div class="col-sm-8">
                                <select name="payroll_group_id" class="form-select bg-light border-0">
                                    <option value="">Select Group</option>
                                    @foreach($payrollGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label class="col-sm-4 col-form-label fw-bold">Payroll Period</label>
                            <div class="col-sm-8">
                                <select name="payroll_period" class="form-select bg-light border-0">
                                    <option value="{{ now()->startOfWeek()->format('M d Y') }} to {{ now()->endOfWeek()->format('M d Y') }}">
                                        {{ now()->startOfWeek()->format('M d Y') }} to {{ now()->endOfWeek()->format('M d Y') }} (This Week)
                                    </option>
                                    <option value="{{ now()->subWeek()->startOfWeek()->format('M d Y') }} to {{ now()->subWeek()->endOfWeek()->format('M d Y') }}">
                                        {{ now()->subWeek()->startOfWeek()->format('M d Y') }} to {{ now()->subWeek()->endOfWeek()->format('M d Y') }} (Last Week)
                                    </option>
                                    <!-- Dynamic range can be improved here -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label class="col-sm-4 col-form-label fw-bold">Select Option</label>
                            <div class="col-sm-8">
                                <select name="action" class="form-select bg-light border-0">
                                    <option value="Reset other addition">Reset other addition</option>
                                    <option value="Keep existing">Keep existing</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label class="col-sm-4 col-form-label fw-bold">ACTION</label>
                            <div class="col-sm-8">
                                <select class="form-select bg-light border-0" disabled>
                                    <option>Upload and Save</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label class="col-sm-4 col-form-label fw-bold">UPLOAD TEMPLATE</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="file" name="upload_template" class="form-control" id="uploadTemplate">
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold mb-3 w-100">
                                <i class="bi bi-cloud-upload me-2"></i> Import
                            </button>
                            <a href="{{ route('admin.payroll.other-addition-enrollment.download-template') }}" class="btn btn-primary rounded-pill px-5 py-2 fw-bold w-100">
                                <i class="bi bi-download me-2"></i> Download Template Now
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($recentEnrollments->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Recent Enrollments</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Employee</th>
                                <th>Addition Type</th>
                                <th>Amount</th>
                                <th>Period</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEnrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2">
                                            <div class="fw-bold text-dark">{{ $enrollment->employee->full_name }}</div>
                                            <div class="text-muted small">ID: {{ $enrollment->employee->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $enrollment->allowanceType->name }}</td>
                                <td class="fw-bold text-primary">₱{{ number_format($enrollment->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($enrollment->payroll_period_start)->format('M d') }} - {{ \Carbon\Carbon::parse($enrollment->payroll_period_end)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $enrollment->status == 'pending' ? 'warning' : 'success' }} rounded-pill px-3">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
