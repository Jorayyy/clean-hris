@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">{{ $employee->full_name }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Attendance Logs</h4>
        </div>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" id="view-list-btn">
                    <i class="bi bi-list-ul"></i> List
                </button>
                <button type="button" class="btn btn-outline-primary" id="view-calendar-btn">
                    <i class="bi bi-calendar3"></i> Calendar
                </button>
            </div>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('attendance.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Manual Entry
            </a>
        </div>
    </div>

    <!-- Employee Profile Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm me-4" style="width: 64px; height: 64px; font-size: 1.25rem;">
                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                </div>
                <div>
                    <h5 class="fw-bold mb-1">{{ $employee->full_name }}</h5>
                    <p class="text-muted mb-0 small">ID: {{ $employee->employee_id }} | {{ $employee->position }}</p>
                </div>
                <div class="ms-auto">
                    <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view-container">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('attendance.show', $employee->id) }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold">Filter Date:</label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                        <input type="hidden" name="view" value="list">
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Time In</th>
                                <th>Time Out</th>
                                <th>Total Hours</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $row)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-success"><i class="bi bi-box-arrow-in-right me-2"></i>{{ date('h:i A', strtotime($row->time_in)) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger"><i class="bi bi-box-arrow-left me-2"></i>{{ $row->time_out ? date('h:i A', strtotime($row->time_out)) : '---' }}</span>
                                </td>
                                <td>{{ number_format($row->total_hours, 2) }} hrs</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('attendance.edit', $row->id) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('attendance.destroy', $row->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">No records found for this date.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div id="calendar-view-container" class="d-none">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div id="full-calendar"></div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold" id="modalDate">Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center" id="modalBody">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #full-calendar { min-height: 600px; }
    .fc .fc-button-primary { background-color: #0d6efd; border-color: #0d6efd; }
    .fc-event { cursor: pointer; padding: 6px 10px; border-radius: 4px; border: none; font-size: 0.85rem; font-weight: 600; }
    
    /* Solid Colors for Calendar Events */
    .event-present { background-color: #198754 !important; color: #ffffff !important; border: none !important; }
    .event-absent { background-color: #dc3545 !important; color: #ffffff !important; border: none !important; }
    .event-rest { background-color: #6c757d !important; color: #ffffff !important; border: none !important; }
    
    .fc-daygrid-day-number { font-weight: bold; padding: 10px !important; text-decoration: none !important; color: #1e293b; }
</style>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('list-view-container');
    const calendarContainer = document.getElementById('calendar-view-container');
    const viewListBtn = document.getElementById('view-list-btn');
    const viewCalendarBtn = document.getElementById('view-calendar-btn');
    let calendar = null;

    function initCalendar() {
        if (calendar) return;
        const calendarEl = document.getElementById('full-calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: async function(info, successCallback, failureCallback) {
                const midDate = new Date((info.start.getTime() + info.end.getTime()) / 2);
                try {
                    const response = await fetch(`{{ route('attendance.monthly', $employee->id) }}?year=${midDate.getFullYear()}&month=${midDate.getMonth() + 1}`);
                    const data = await response.json();
                    const events = Object.entries(data).map(([date, info]) => ({
                        title: info.status === 'present' ? `${info.logs[0].time_in}` : info.status.toUpperCase(),
                        start: date,
                        allDay: true,
                        extendedProps: info,
                        className: info.status === 'present' ? 'event-present' : (info.status === 'absent' ? 'event-absent' : 'event-rest')
                    }));
                    successCallback(events);
                } catch (e) { 
                    console.error(e);
                    failureCallback(e); 
                }
            },
            eventClick: function(info) {
                const props = info.event.extendedProps;
                const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                document.getElementById('modalDate').innerText = info.event.start.toDateString();
                
                let html = `<h4 class="fw-bold mb-3 ${props.status === 'present' ? 'text-success' : 'text-danger'}">${props.status.toUpperCase()}</h4>`;
                
                if (props.status === 'present') {
                    html += `<div class="p-3 bg-light rounded-3 mb-3">
                                <div class="small text-muted">Total Hours</div>
                                <div class="fs-3 fw-bold">${props.total_hours.toFixed(2)}</div>
                             </div>`;
                    props.logs.forEach(log => {
                        html += `<div class="d-flex justify-content-between border-bottom py-2">
                                    <span>In: <strong>${log.time_in}</strong></span>
                                    <span>Out: <strong>${log.time_out || '---'}</strong></span>
                                 </div>`;
                    });
                } else {
                    html += `<p class="text-muted">No attendance activity recorded.</p>`;
                }
                
                document.getElementById('modalBody').innerHTML = html;
                modal.show();
            }
        });
        calendar.render();
    }

    viewListBtn.addEventListener('click', () => {
        listContainer.classList.remove('d-none');
        calendarContainer.classList.add('d-none');
        viewListBtn.classList.add('active');
        viewCalendarBtn.classList.remove('active');
    });

    viewCalendarBtn.addEventListener('click', () => {
        listContainer.classList.add('d-none');
        calendarContainer.classList.remove('d-none');
        viewCalendarBtn.classList.add('active', 'btn-primary');
        viewCalendarBtn.classList.remove('btn-outline-primary');
        viewListBtn.classList.remove('active', 'btn-primary');
        viewListBtn.classList.add('btn-outline-primary');
        initCalendar();
        setTimeout(() => calendar.updateSize(), 100);
    });
});
</script>
@endsection

