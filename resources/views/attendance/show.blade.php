@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ url('attendance') }}">Attendance</a></li>
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
            <a href="{{ url('attendance') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ url('attendance/create?employee_id=' . $employee->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Manual Entry
            </a>
        </div>
    </div>

    <!-- Employee Profile Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm me-4" style="width: 64px; height: 64px; font-size: 1.25rem;">
                    {{ substr($employee->first_name ?? 'E', 0, 1) }}{{ substr($employee->last_name ?? 'P', 0, 1) }}
                </div>
                <div>
                    <h5 class="fw-bold mb-1">{{ $employee->first_name ?? 'N/A' }} {{ $employee->last_name ?? '' }}</h5>
                    <p class="text-muted mb-0 small">ID: {{ $employee->employee_id ?? 'Unknown' }} | {{ $employee->position ?? 'No Position' }}</p>
                </div>
                <div class="ms-auto">
                    <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Dashboard Layout -->
    <div class="row">
        <!-- Sidebar Options (Left) -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold fw-bold text-center">Viewing Options</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-bottom">
                        <label class="list-group-item d-flex align-items-center py-3 border-0">
                            <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-schedule" id="toggle-schedule" checked>
                            <span class="small fw-semibold">View Schedules</span>
                        </label>
                        <label class="list-group-item d-flex align-items-center py-3 border-0">
                            <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-attendance" id="toggle-attendance" checked>
                            <span class="small fw-semibold">View Attendance</span>
                        </label>
                        <label class="list-group-item d-flex align-items-center py-3 border-0">
                            <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-approved" id="toggle-approved">
                            <span class="small fw-semibold">View Approved Forms</span>
                        </label>
                        <label class="list-group-item d-flex align-items-center py-3 border-0">
                            <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-breaks" id="toggle-breaks">
                            <span class="small fw-semibold">View Breaks</span>
                        </label>
                    </div>

                    <div class="p-3">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr class="bg-success bg-opacity-10">
                                    <th class="small fw-bold text-center">Legend</th>
                                    <th class="small fw-bold text-center">Color Code</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td class="text-muted">Individually Plotted</td>
                                    <td class="text-center"><div class="rounded mx-auto shadow-sm" style="width: 40px; height: 18px; background-color: #800040;"></div></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Group Plotted</td>
                                    <td class="text-center"><div class="rounded mx-auto shadow-sm" style="width: 40px; height: 18px; background-color: #ff0000;"></div></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Fixed Schedule</td>
                                    <td class="text-center"><div class="rounded mx-auto shadow-sm" style="width: 40px; height: 18px; background-color: #808080;"></div></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Attendance (Punch)</td>
                                    <td class="text-center"><div class="rounded mx-auto shadow-sm" style="width: 40px; height: 18px; background-color: #198754;"></div></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Absent</td>
                                    <td class="text-center"><div class="rounded mx-auto shadow-sm" style="width: 40px; height: 18px; background-color: #dc3545;"></div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Calendar Area (Right) -->
        <div class="col-md-9">
            <!-- List View -->
            <div id="list-view-container">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label mb-0 fw-bold">Filter Month/Year:</label>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <select name="month" class="form-select border-0 bg-light">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="year" class="form-select border-0 bg-light border-start">
                                        @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                                            <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary px-4">Filter</button>
                                </div>
                            </div>
                            <div class="col-auto ms-auto">
                                <a href="{{ url()->current() }}" class="btn btn-light rounded-pill px-4">Reset</a>
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
                                        <th class="ps-4">Date</th>
                                        <th>Time In</th>
                                        <th>Breaks</th>
                                        <th>Time Out</th>
                                        <th>Total Hours</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendances as $row)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                                        <td>
                                            <span class="fw-bold text-success"><i class="bi bi-box-arrow-in-right me-2"></i>{{ \Carbon\Carbon::parse($row->time_in)->format('H:i') }}</span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                @if($row->break1_out || $row->break1_in)
                                                    <div>
                                                        <span class="text-muted small">Lunch Break:</span> 
                                                        <span class="fw-bold text-info">
                                                            {{ $row->break1_out ? \Carbon\Carbon::parse($row->break1_out)->format('H:i') : '--:--' }} - 
                                                            {{ $row->break1_in ? \Carbon\Carbon::parse($row->break1_in)->format('H:i') : '--:--' }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if($row->break2_out || $row->break2_in)
                                                    <div>
                                                        <span class="text-muted small">2nd Break:</span> 
                                                        <span class="fw-bold text-info">
                                                            {{ $row->break2_out ? \Carbon\Carbon::parse($row->break2_out)->format('H:i') : '--:--' }} - 
                                                            {{ $row->break2_in ? \Carbon\Carbon::parse($row->break2_in)->format('H:i') : '--:--' }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if(!$row->break1_out && !$row->break1_in && !$row->break2_out && !$row->break2_in)
                                                    <span class="text-muted small">-- | --</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger"><i class="bi bi-box-arrow-left me-2"></i>{{ $row->time_out ? \Carbon\Carbon::parse($row->time_out)->format('H:i') : '---' }}</span>
                                        </td>
                                        <td>{{ number_format($row->total_hours, 2) }} hrs</td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ url('attendance/' . $row->id . '/edit') }}" class="btn btn-outline-primary border-0"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ url('attendance/' . $row->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger border-0" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No records found for this period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div id="calendar-view-container" class="d-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div id="full-calendar"></div>
                    </div>
                </div>
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
    .event-scheduled { background-color: #f8fafc !important; color: #64748b !important; border: 1px solid #e2e8f0 !important; }
    
    /* Force FullCalendar Override */
    .fc-daygrid-event { border-radius: 4px !important; margin-top: 2px !important; }
    .fc-event-title { font-weight: 700 !important; }
    
    .fc-daygrid-day-number { font-weight: bold; padding: 10px !important; text-decoration: none !important; color: #1e293b; }
    
    .list-group-item:hover { background-color: #f8fafc; transition: 0.2s; cursor: pointer; }
    .form-check-input:checked { background-color: #0d6efd; border-color: #0d6efd; }
    .card-header { border-bottom: 1px solid rgba(0,0,0,.08); }
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
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            events: async function(info, successCallback, failureCallback) {
                // Use a wider range to avoid edge cases
                const startStr = info.start.toISOString().split('T')[0];
                const endStr = info.end.toISOString().split('T')[0];
                const midDate = new Date((info.start.getTime() + info.end.getTime()) / 2);
                
                try {
                    console.log("Fetching attendance for:", midDate.getFullYear(), midDate.getMonth() + 1);
                    const response = await fetch(`{{ url("attendance") }}/{{ $employee->id }}/monthly?year=${midDate.getFullYear()}&month=${midDate.getMonth() + 1}`);
                    const data = await response.json();
                    
                    const showSchedule = document.getElementById('toggle-schedule').checked;
                    const showAttendance = document.getElementById('toggle-attendance').checked;
                    const showBreaks = document.getElementById('toggle-breaks').checked;
                    
                    let events = [];
                    const todayStr = new Date().toISOString().split('T')[0];
                    
                    Object.entries(data).forEach(([date, dayData]) => {
                        // Add Schedule Event
                        if (showSchedule && dayData.schedule) {
                            let color = '#808080'; // Default fixed
                            if (dayData.schedule.source === 'individual') color = '#800040';
                            if (dayData.schedule.source === 'group') color = '#ff0000';

                            events.push({
                                title: `SHIFT: ${dayData.schedule.time_in}-${dayData.schedule.time_out}`,
                                start: date,
                                backgroundColor: color,
                                borderColor: color,
                                textColor: '#ffffff',
                                allDay: true,
                                extendedProps: { type: 'schedule', ...dayData.schedule }
                            });
                        }

                        // Add Attendance Event
                        if (dayData.attendance) {
                            const mainLog = dayData.attendance.logs[0];
                            
                            if (showAttendance) {
                                // IN Event
                                events.push({
                                    title: `IN: ${mainLog.time_in}`,
                                    start: date,
                                    backgroundColor: '#198754',
                                    borderColor: '#198754',
                                    textColor: '#ffffff',
                                    allDay: true,
                                    extendedProps: { type: 'attendance', ...dayData.attendance }
                                });

                                // OUT Event (if exists)
                                if (mainLog.time_out && mainLog.time_out !== '--:--') {
                                    events.push({
                                        title: `OUT: ${mainLog.time_out}`,
                                        start: date,
                                        backgroundColor: '#dc3545',
                                        borderColor: '#dc3545',
                                        textColor: '#ffffff',
                                        allDay: true,
                                        extendedProps: { type: 'attendance', ...dayData.attendance }
                                    });
                                }
                            }

                            // BREAK Events
                            if (showBreaks) {
                                if (mainLog.break1_out) {
                                    events.push({
                                        title: `LO: ${mainLog.break1_out}`,
                                        start: date,
                                        backgroundColor: '#0dcaf0',
                                        borderColor: '#0dcaf0',
                                        textColor: '#000000',
                                        allDay: true,
                                        extendedProps: { type: 'break', ...dayData.attendance }
                                    });
                                }
                                if (mainLog.break1_in) {
                                    events.push({
                                        title: `LI: ${mainLog.break1_in}`,
                                        start: date,
                                        backgroundColor: '#0dcaf0',
                                        borderColor: '#0dcaf0',
                                        textColor: '#000000',
                                        allDay: true,
                                        extendedProps: { type: 'break', ...dayData.attendance }
                                    });
                                }
                            }
                        } else if (showAttendance && !dayData.attendance && dayData.schedule && date < todayStr) {
                            // Only show ABSENT for past dates that had a schedule
                            events.push({
                                title: 'ABSENT',
                                start: date,
                                backgroundColor: '#dc3545',
                                borderColor: '#dc3545',
                                textColor: '#ffffff',
                                allDay: true,
                                extendedProps: { type: 'absent' }
                            });
                        }
                    });
                    
                    console.log("Events to render:", events.length);
                    successCallback(events);
                } catch (e) { 
                    console.error("Calendar Fetch Error:", e);
                    failureCallback(e); 
                }
            },
            eventClick: function(info) {
                const props = info.event.extendedProps;
                const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                document.getElementById('modalDate').innerText = info.event.start.toDateString();
                
                let html = '';
                if (props.type === 'attendance') {
                    html = `<h4 class="fw-bold mb-3 text-success">PRESENT</h4>`;
                    props.logs.forEach(log => {
                        html += `<div class="d-flex justify-content-between border-bottom py-2">
                                    <span>In: <strong>${log.time_in}</strong></span>
                                    <span>Out: <strong>${log.time_out || '---'}</strong></span>
                                 </div>`;
                    });
                } else if (props.type === 'schedule') {
                    html = `<h4 class="fw-bold mb-3 text-primary">SCHEDULED SHIFT</h4>
                            <div class="p-3 bg-light rounded-3">
                                <p class="mb-1 text-muted small">Source: <span class="text-dark fw-bold">${props.source.toUpperCase()}</span></p>
                                <p class="mb-0 fs-5 fw-bold">${props.time_in} - ${props.time_out}</p>
                            </div>`;
                } else {
                    html = `<h4 class="fw-bold mb-3 text-danger">ABSENT</h4><p class="text-muted">No attendance activity recorded.</p>`;
                }
                
                document.getElementById('modalBody').innerHTML = html;
                modal.show();
            }
        });
        calendar.render();

        // Add refresh listeners to checkboxes
        document.querySelectorAll('.event-toggle').forEach(el => {
            el.addEventListener('change', () => calendar.refetchEvents());
        });
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

