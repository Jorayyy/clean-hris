@extends('layouts.app')

@section('content')
@php
    $formatTime = function ($value, $fallback = '--:--') {
        if (!$value || $value === '00:00:00') {
            return $fallback;
        }

        return \Carbon\Carbon::parse($value)->format('H:i');
    };
@endphp

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ url('attendance') }}">Attendance</a></li>
                <li class="breadcrumb-item active">{{ $employee->full_name }}</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0" style="font-size: 1.9rem; letter-spacing: -0.03em;">Attendance Logs</h2>
    </div>
    <div class="d-flex gap-2">
        <div class="d-inline-flex gap-1 p-1 rounded-pill" style="background: rgba(0,0,0,0.05);" role="group">
            <button type="button" class="view-toggle active" id="view-list-btn"><i class="bi bi-list-ul me-1"></i>List</button>
            <button type="button" class="view-toggle" id="view-calendar-btn"><i class="bi bi-calendar3 me-1"></i>Calendar</button>
        </div>
        <a href="{{ url('attendance/create?employee_id=' . $employee->id) }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Manual Entry</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
        @if($employee->photo)
            <img src="{{ asset('storage/' . $employee->photo) }}" class="profile-photo" alt="">
        @else
            <div class="profile-photo profile-initial">{{ strtoupper(substr($employee->first_name ?? 'E', 0, 1)) }}{{ strtoupper(substr($employee->last_name ?? 'P', 0, 1)) }}</div>
        @endif
        <div class="flex-grow-1 min-width-0">
            <h5 class="fw-bold mb-0">{{ $employee->first_name }} {{ $employee->last_name }}</h5>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ $employee->employee_id }} &middot; {{ $employee->position ?? 'No position' }}</p>
        </div>
        @if($employee->status == 'active')
            <span class="badge badge-green">Active</span>
        @else
            <span class="badge">{{ ucfirst($employee->status) }}</span>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-body p-3">
                <h6 class="section-title">Calendar Layers</h6>
                <label class="d-flex align-items-center py-2 layer-row">
                    <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-schedule" id="toggle-schedule" checked>
                    <span class="small fw-medium">Schedules</span>
                </label>
                <label class="d-flex align-items-center py-2 layer-row">
                    <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-attendance" id="toggle-attendance" checked>
                    <span class="small fw-medium">Attendance</span>
                </label>
                <label class="d-flex align-items-center py-2 layer-row mb-0">
                    <input class="form-check-input me-3 event-toggle" type="checkbox" value="view-breaks" id="toggle-breaks">
                    <span class="small fw-medium">Breaks</span>
                </label>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-3">
                <h6 class="section-title">Legend</h6>
                <div class="legend-row"><span class="legend-dot" style="background:#800040;"></span>Individually Plotted</div>
                <div class="legend-row"><span class="legend-dot" style="background:#ff0000;"></span>Group Plotted</div>
                <div class="legend-row"><span class="legend-dot" style="background:#808080;"></span>Fixed Schedule</div>
                <div class="legend-row"><span class="legend-dot" style="background:#198754;"></span>Punch In</div>
                <div class="legend-row"><span class="legend-dot" style="background:#dc3545;"></span>Punch Out / Absent</div>
                <div class="legend-row mb-0"><span class="legend-dot" style="background:#0dcaf0;"></span>Breaks</div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div id="list-view-container">
            <div class="card mb-4">
                <div class="card-body p-3">
                    <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0 fw-medium small text-muted">Period</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select" style="width: auto;">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="year" class="form-select" style="width: auto;">
                                @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm px-4">Apply</button>
                            <a href="{{ url()->current() }}" class="btn btn-light btn-sm px-4 ms-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>In</th>
                                <th>Breaks</th>
                                <th>Out</th>
                                <th>Total</th>
                                <th class="text-end pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $row)
                            <tr>
                                <td class="ps-4 fw-medium" style="font-size: 0.88rem;">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                                <td><span class="fw-semibold" style="color: #157347; font-size: 0.88rem;">{{ $formatTime($row->time_in, '---') }}</span></td>
                                <td>
                                    <div class="small" style="font-size: 0.78rem;">
                                        @if(($row->break1_out && $row->break1_out !== '00:00:00') || ($row->break1_in && $row->break1_in !== '00:00:00'))
                                            <div><span class="text-muted">B1:</span> {{ $formatTime($row->break1_out) }} - {{ $formatTime($row->break1_in) }}</div>
                                        @endif
                                        @if(($row->lunch_out && $row->lunch_out !== '00:00:00') || ($row->lunch_in && $row->lunch_in !== '00:00:00'))
                                            <div><span class="text-muted">Lunch:</span> {{ $formatTime($row->lunch_out) }} - {{ $formatTime($row->lunch_in) }}</div>
                                        @endif
                                        @if(($row->break2_out && $row->break2_out !== '00:00:00') || ($row->break2_in && $row->break2_in !== '00:00:00'))
                                            <div><span class="text-muted">B2:</span> {{ $formatTime($row->break2_out) }} - {{ $formatTime($row->break2_in) }}</div>
                                        @endif
                                        @if((!$row->break1_out || $row->break1_out === '00:00:00') && (!$row->break1_in || $row->break1_in === '00:00:00') && (!$row->lunch_out || $row->lunch_out === '00:00:00') && (!$row->lunch_in || $row->lunch_in === '00:00:00') && (!$row->break2_out || $row->break2_out === '00:00:00') && (!$row->break2_in || $row->break2_in === '00:00:00'))
                                            <span class="text-muted">No breaks recorded</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="fw-semibold" style="color: #d02f26; font-size: 0.88rem;">{{ $formatTime($row->time_out, '---') }}</span></td>
                                <td class="fw-semibold" style="font-size: 0.88rem;">{{ number_format($row->total_hours, 2) }} hrs</td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ url('attendance/' . $row->id . '/edit') }}" class="btn btn-sm btn-light icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ url('attendance/' . $row->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light icon-btn icon-danger" onclick="return confirm('Delete this record?')" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-calendar-x text-muted fs-2 d-block mb-2"></i>
                                    <p class="text-muted small mb-0">No records found for this period.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="calendar-view-container" class="d-none">
            <div class="card overflow-hidden">
                <div class="card-body p-4">
                    <div id="full-calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalDate">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3" id="modalBody"></div>
        </div>
    </div>
</div>

<style>
    .profile-photo {
        width: 56px; height: 56px; flex-shrink: 0;
        border-radius: 50%; object-fit: cover;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .profile-initial {
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.15rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #0071e3, #0058b0);
    }
    .min-width-0 { min-width: 0; }
    .badge-green { background: #d9f4e3 !important; color: #157347 !important; }
    .badge:not([class*="badge-"]):not(.bg-white) { background: #ffe5e3 !important; color: #d02f26 !important; }
    .section-title {
        font-size: 0.72rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.06em; color: #86868b;
        padding-bottom: 0.5rem; margin-bottom: 0.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .layer-row { cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.045); }
    .layer-row:last-child { border-bottom: none; }
    .legend-row {
        display: flex; align-items: center; gap: 0.65rem;
        padding: 0.45rem 0; font-size: 0.82rem; color: #494949;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .legend-dot { width: 12px; height: 12px; border-radius: 4px; flex-shrink: 0; }
    .view-toggle {
        border: none; background: transparent;
        padding: 0.4rem 1.1rem; border-radius: 980px;
        font-size: 0.84rem; font-weight: 500; color: #6e6e73;
        transition: all 0.18s cubic-bezier(0.25,0.1,0.25,1);
    }
    .view-toggle.active {
        background: #fff; color: #1d1d1f;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .icon-btn { padding: 0.3rem 0.55rem; line-height: 1.2; }
    .icon-btn.icon-danger:hover { background: #ffe5e3; color: #d02f26; }

    #full-calendar { min-height: 600px; }
    #full-calendar .fc .fc-button {
        border-radius: 10px !important;
        font-weight: 500 !important;
        text-transform: capitalize !important;
        padding: 0.32rem 0.8rem !important;
    }
    #full-calendar .fc .fc-button-primary {
        background: #e8e8ed !important;
        border-color: #e8e8ed !important;
        color: #1d1d1f !important;
    }
    #full-calendar .fc .fc-button-primary:not(:disabled).fc-button-active,
    #full-calendar .fc .fc-button-primary:not(:disabled):active {
        background: #0071e3 !important;
        border-color: #0071e3 !important;
        color: #fff !important;
    }
    #full-calendar .fc .fc-button-primary:hover { background: #dcdce1 !important; border-color: #dcdce1 !important; }
    #full-calendar .fc .fc-button-primary:not(:disabled).fc-button-active:hover { background: #0077ed !important; border-color: #0077ed !important; }
    #full-calendar .fc .fc-toolbar-title {
        font-size: 1.15rem !important; font-weight: 700 !important;
        letter-spacing: -0.02em; color: #1d1d1f;
    }
    #full-calendar .fc .fc-col-header-cell-cushion {
        font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.05em; color: #86868b; padding: 0.6rem 0;
    }
    #full-calendar .fc .fc-daygrid-day-number {
        font-size: 0.8rem; font-weight: 500; color: #1d1d1f;
        padding: 6px 8px !important;
    }
    #full-calendar .fc .fc-day-today { background: rgba(0,113,227,0.06) !important; }
    #full-calendar .fc .fc-day-today .fc-daygrid-day-number { color: #0071e3; font-weight: 700; }
    #full-calendar .fc-theme-standard td, #full-calendar .fc-theme-standard th,
    #full-calendar .fc-theme-standard .fc-scrollgrid { border-color: rgba(0,0,0,0.06); }
    #full-calendar .fc-event {
        cursor: pointer; border: none; border-radius: 6px;
        font-size: 0.72rem; font-weight: 600; padding: 1px 4px;
    }
    #full-calendar .fc-daygrid-event { margin-top: 2px; }
    #full-calendar .fc-daygrid-day-frame { min-height: 92px; }
</style>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('list-view-container');
    const calendarContainer = document.getElementById('calendar-view-container');
    const viewListBtn = document.getElementById('view-list-btn');
    const viewCalendarBtn = document.getElementById('view-calendar-btn');
    let calendar = null;

    const localISO = (d) => d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');

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
                try {
                    const response = await fetch(`{{ url("attendance") }}/{{ $employee->id }}/monthly?start=${localISO(info.start)}&end=${localISO(new Date(info.end.getTime() - 86400000))}`);
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const data = await response.json();

                    const showSchedule = document.getElementById('toggle-schedule').checked;
                    const showAttendance = document.getElementById('toggle-attendance').checked;
                    const showBreaks = document.getElementById('toggle-breaks').checked;

                    let events = [];
                    const todayStr = localISO(new Date());

                    Object.entries(data).forEach(([date, dayData]) => {
                        if (showSchedule && dayData.schedule) {
                            let color = '#808080';
                            if (dayData.schedule.source === 'individual') color = '#800040';
                            if (dayData.schedule.source === 'group') color = '#ff0000';

                            events.push({
                                title: dayData.schedule.name ? `${dayData.schedule.name} ${dayData.schedule.time_in}-${dayData.schedule.time_out}` : `SHIFT: ${dayData.schedule.time_in}-${dayData.schedule.time_out}`,
                                start: date,
                                backgroundColor: color,
                                borderColor: color,
                                textColor: '#ffffff',
                                allDay: true,
                                extendedProps: { type: 'schedule', ...dayData.schedule }
                            });
                        }

                        if (dayData.attendance) {
                            dayData.attendance.logs.forEach(function(log, idx) {
                                if (showAttendance) {
                                    if (log.time_in) {
                                        events.push({
                                            title: `IN: ${log.time_in}`,
                                            start: date,
                                            backgroundColor: '#198754',
                                            borderColor: '#198754',
                                            textColor: '#ffffff',
                                            allDay: true,
                                            extendedProps: { type: 'attendance', ...dayData.attendance }
                                        });
                                    }
                                    if (log.time_out) {
                                        events.push({
                                            title: `OUT: ${log.time_out}`,
                                            start: date,
                                            backgroundColor: '#dc3545',
                                            borderColor: '#dc3545',
                                            textColor: '#ffffff',
                                            allDay: true,
                                            extendedProps: { type: 'attendance', ...dayData.attendance }
                                        });
                                    }
                                }

                                if (showBreaks) {
                                    [['break1_out', 'LO'], ['break1_in', 'LI'], ['lunch_out', 'LU'], ['lunch_in', 'LI'], ['break2_out', 'BO'], ['break2_in', 'BI']].forEach(function(pair) {
                                        if (log[pair[0]]) {
                                            events.push({
                                                title: `${pair[1]}: ${log[pair[0]]}`,
                                                start: date,
                                                backgroundColor: '#0dcaf0',
                                                borderColor: '#0dcaf0',
                                                textColor: '#000000',
                                                allDay: true,
                                                extendedProps: { type: 'attendance', ...dayData.attendance }
                                            });
                                        }
                                    });
                                }
                            });
                        } else if (showAttendance && dayData.schedule && date < todayStr) {
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

                    successCallback(events);
                } catch (e) {
                    console.error("Calendar fetch error:", e);
                    failureCallback(e);
                }
            },
            eventClick: function(info) {
                const props = info.event.extendedProps;
                const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                document.getElementById('modalDate').innerText = info.event.start.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                let html = '';
                if (props.type === 'attendance') {
                    html = `<h6 class="fw-bold mb-3" style="color:#157347;"><i class="bi bi-check-circle me-1"></i>Present &middot; ${Number(props.total_hours || 0).toFixed(2)} hrs total</h6>`;
                    props.logs.forEach(log => {
                        html += `<div class="p-3 rounded-4 mb-2" style="background:#f5f5f7;">
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted small">In</span><strong>${log.time_in || '---'}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted small">Out</span><strong>${log.time_out || '---'}</strong>
                                    </div>
                                    ${[['Break 1', log.break1_out, log.break1_in], ['Lunch', log.lunch_out, log.lunch_in], ['Break 2', log.break2_out, log.break2_in]].filter(b => b[1] || b[2]).map(b => `
                                    <div class="d-flex justify-content-between py-1 border-top hairline-top">
                                        <span class="text-muted small">${b[0]}</span><strong>${b[1] || '?'} - ${b[2] || '?'}</strong>
                                    </div>`).join('')}
                                 </div>`;
                    });
                } else if (props.type === 'schedule') {
                    const sourceLabel = { individual: 'Individually Plotted', group: 'Group Plotted', fixed: 'Fixed Schedule' }[props.source] || props.source;
                    html = `<h6 class="fw-bold mb-3" style="color:#0071e3;"><i class="bi bi-calendar-check me-1"></i>Scheduled Shift</h6>
                            <div class="p-3 rounded-4" style="background:#f5f5f7;">
                                <p class="mb-1 text-muted small">${sourceLabel}</p>
                                <p class="mb-0 fs-5 fw-bold">${props.time_in} - ${props.time_out}${props.name ? ` &middot; ${props.name}` : ''}</p>
                            </div>`;
                } else {
                    html = `<h6 class="fw-bold mb-3" style="color:#d02f26;"><i class="bi bi-x-circle me-1"></i>Absent</h6>
                            <p class="text-muted small mb-0">A shift was scheduled but no attendance was recorded.</p>`;
                }

                document.getElementById('modalBody').innerHTML = html;
                modal.show();
            }
        });
        calendar.render();

        document.querySelectorAll('.event-toggle').forEach(el => {
            el.addEventListener('change', () => calendar.refetchEvents());
        });
    }

    function setView(showCalendar) {
        listContainer.classList.toggle('d-none', showCalendar);
        calendarContainer.classList.toggle('d-none', !showCalendar);
        viewListBtn.classList.toggle('active', !showCalendar);
        viewCalendarBtn.classList.toggle('active', showCalendar);
        if (showCalendar) {
            initCalendar();
            setTimeout(() => calendar.updateSize(), 100);
        }
    }

    viewListBtn.addEventListener('click', () => setView(false));
    viewCalendarBtn.addEventListener('click', () => setView(true));
});
</script>
<style>
    .hairline-top { border-color: rgba(0,0,0,0.06) !important; }
</style>
@endsection
