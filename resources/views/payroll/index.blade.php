@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <h5 class="mb-0">Payroll Batches</h5>
        <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm">Create New Batch</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light text-dark">
                    <tr>
                        <th>Batch Code</th>
                        <th>Payroll Group</th>
                        <th>Coverage Period</th>
                        <th>Pay Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $row)
                    <tr>
                        <td><strong>{{ $row->payroll_code }}</strong></td>
                        <td><span class="badge bg-info text-dark">{{ $row->payrollGroup->name ?? 'All Groups' }}</span></td>
                        <td>{{ $row->start_date }} to {{ $row->end_date }}</td>
                        <td>{{ $row->pay_date }} (Friday)</td>
                        <td>
                            @if($row->status == 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                            @else
                                <span class="badge bg-success">Processed</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('payroll.show', $row->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if($row->status == 'draft')
                                    <form action="{{ route('payroll.process', $row->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Process this payroll batch now?')">Run Payroll</button>
                                    </form>
                                @endif
                                <form action="{{ route('payroll.destroy', $row->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Archive?')">X</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
