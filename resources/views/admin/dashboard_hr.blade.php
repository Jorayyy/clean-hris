@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Pulse Quick Actions -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4 bg-info text-white overflow-hidden">
            <div class="card-body p-4 d-flex justify-content-between align-items-center position-relative">
                <div class="z-1">
                    <h4 class="fw-800 mb-1 tracking-tight">HR Operations Center</h4>
                    <p class="mb-0 opacity-75">Workforce Management Dashboard: Track employees, attendance, and support requests.</p>
                </div>
                <div class="d-flex gap-2 z-1">
                    <a href="{{ route('employees.create') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-person-plus-fill me-2 text-info"></i>Add New Employee
                    </a>
                </div>
                <!-- Decorative Icon -->
                <i class="bi bi-people position-absolute end-0 top-50 translate-middle-y opacity-25" style="font-size: 8rem; margin-right: -2rem;"></i>
            </div>
        </div>
    </div>

    <!-- Critical HR To-Do's -->
    @if($pendingTickets > 0 || $pendingDtrs > 0)
    <div class="col-md-12">
        <div class="alert bg-white border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-0">
            <div class="bg-info-subtle text-info rounded-circle p-2 me-3">
                <i class="bi bi-bell-fill fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <span class="fw-bold text-dark small">PENDING HR ACTIONS:</span>
                <div class="d-inline-flex gap-3 ms-3">
                    @if($pendingTickets > 0)
                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill fw-bold">{{ $pendingTickets }} Open Tickets</span>
                    @endif
                    @if($pendingDtrs > 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill fw-bold">{{ $pendingDtrs }} DTRs Pending Review</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-info border-0 fw-bold">RESOLVE TICKETS <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">ACTIVE STAFF</span>
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $totalEmployees }}</h2>
                <div class="text-success small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Deployment Active</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">TODAY'S ATTENDANCE</span>
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $totalAttendanceToday }}</h2>
                <div class="text-primary small fw-bold">Present Today</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span class="fw-bold small tracking-wider">OPEN TICKETS</span>
                    <i class="bi bi-chat-dots fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1">{{ $pendingTickets }}</h2>
                <div class="text-info small fw-bold">Needs Resolution</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm rounded-4 border-0 h-100 overflow-hidden bg-info-subtle border-1 border-info border-opacity-25">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3 text-info">
                    <span class="fw-bold small tracking-wider">ANNOUNCEMENTS</span>
                    <i class="bi bi-megaphone-fill fs-5"></i>
                </div>
                <h2 class="fw-800 mb-1 text-info-emphasis">POST</h2>
                <a href="{{ route('announcements.create') }}" class="stretched-link text-info text-decoration-none small fw-bold">Broadcast to Staff</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Attendance Chart -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold border-start border-4 border-primary ps-2">Attendance Volume (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Calendar Area -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Workforce Events</h6>
            </div>
            <div class="card-body overflow-auto" style="max-height: 300px;">
                <p class="text-muted small fw-bold text-uppercase border-bottom pb-1">Upcoming Holidays</p>
                @forelse($upcomingHolidays as $holiday)
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>{{ $holiday->name }}</span>
                        <span class="text-primary fw-bold">{{ $holiday->date?->format('M d') ?? 'N/A' }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">No upcoming holidays</p>
                @endforelse

                <p class="text-muted small fw-bold text-uppercase border-bottom pb-1 mt-3">Employee Birthdays</p>
                @forelse($upcomingBirthdays as $emp)
                    <div class="d-flex align-items-center mb-2 small">
                        <div class="bg-info-subtle text-info rounded-circle me-2 p-1 px-2" style="font-size: 10px;">
                            <i class="bi bi-cake2"></i>
                        </div>
                        <span class="flex-grow-1">{{ $emp->name }}</span>
                        <span class="text-muted">{{ $emp->birthday ? Carbon\Carbon::parse($emp->birthday)->format('M d') : 'N/A' }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">None this month</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Recent Support Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Subject</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTickets as $ticket)
                            <tr>
                                <td class="ps-4">{{ $ticket->employee->name ?? 'System' }}</td>
                                <td>{{ Str::limit($ticket->subject, 20) }}</td>
                                <td class="text-end pe-4">
                                    <span class="badge @if($ticket->status == 'open') bg-danger @elseif($ticket->status == 'in_progress') bg-warning @else bg-success @endif rounded-pill">
                                        {{ str_replace('_', ' ', $ticket->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4">No recent tickets</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Distribution -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Staff Distribution</h6>
            </div>
            <div class="card-body">
                @foreach($groups as $group)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>{{ $group->name }}</span>
                        <span class="fw-bold">{{ $group->employees_count }} Emps</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ ($totalEmployees > 0) ? ($group->employees_count / $totalEmployees * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($attendanceLabels) !!},
                datasets: [{
                    label: 'Present Employees',
                    data: {!! json_encode($attendanceCounts) !!},
                    borderColor: '#0dcaf0',
                    backgroundColor: 'rgba(13, 202, 240, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0dcaf0',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endsection
