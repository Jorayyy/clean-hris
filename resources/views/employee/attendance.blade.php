@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-0">My Attendance Calendar</h3>
            <p class="text-muted small">Viewing records for {{ $selectedDate->format('F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employee.attendance', ['month' => $selectedDate->copy()->subMonth()->month, 'year' => $selectedDate->copy()->subMonth()->year]) }}" class="btn btn-outline-primary">&laquo; Prev</a>
            <a href="{{ route('employee.attendance', ['month' => $selectedDate->copy()->addMonth()->month, 'year' => $selectedDate->copy()->addMonth()->year]) }}" class="btn btn-outline-primary">Next &raquo;</a>
        </div>
    </div>

    @if($schedule)
    <div class="col-md-12 mb-4">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="alert alert-info py-3 shadow-sm border-0 mb-0 h-100 d-flex align-items-center">
                    <div>
                        <h6 class="mb-1 text-primary fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Active Schedule Pattern</h6>
                        <strong>{{ $schedule->name ?? 'Regular Shift' }}</strong> 
                        ({{ date('h:i A', strtotime($schedule->time_in)) }} - {{ date('h:i A', strtotime($schedule->time_out)) }})
                        <span class="ms-1 opacity-75">on {{ is_array($schedule->days) ? implode(', ', $schedule->days) : $schedule->days }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 d-flex justify-content-around align-items-center text-center">
                        <div>
                            <div class="text-muted small">Lates</div>
                            <div class="fw-bold text-danger h5 mb-0">{{ $attendances->where('late_minutes', '>', 0)->count() }}</div>
                        </div>
                        <div class="vr mx-2"></div>
                        <div>
                            <div class="text-muted small">UT</div>
                            <div class="fw-bold text-warning h5 mb-0">{{ $attendances->where('undertime_minutes', '>', 0)->count() }}</div>
                        </div>
                        <div class="vr mx-2"></div>
                        <div>
                            <div class="text-muted small">Worked</div>
                            <div class="fw-bold text-success h5 mb-0">{{ $attendances->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-12">
        <div class="card shadow border-0 overflow-hidden">
            <div class="card-body p-0">
                <style>
                    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border-top: 1px solid #dee2e6; border-left: 1px solid #dee2e6; }
                    .calendar-day-header { background: #f8f9fa; padding: 10px; text-align: center; font-weight: bold; border-right: 1px solid #dee2e6; border-bottom: 2px solid #dee2e6; }
                    .calendar-day { min-height: 120px; padding: 10px; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; background: #fff; }
                    .calendar-day.other-month { background: #f1f3f5; }
                    .calendar-day.today { background: #fffdf0; }
                    .day-number { font-weight: bold; margin-bottom: 5px; display: block; }
                    .attendance-info { font-size: 0.75rem; }
                    .badge-time { display: block; margin-bottom: 2px; text-align: left; }
                </style>
                
                <div class="calendar-grid">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                        <div class="calendar-day-header">{{ $day }}</div>
                    @endforeach

                    @php
                        $startOfMonth = $selectedDate->copy()->startOfMonth();
                        $endOfMonth = $selectedDate->copy()->endOfMonth();
                        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon\Carbon::MONDAY);
                        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon\Carbon::SUNDAY);
                        $current = $startOfCalendar->copy();
                    @endphp

                    @while($current <= $endOfCalendar)
                        @php
                            $dateStr = $current->format('Y-m-d');
                            $record = $attendances->get($dateStr);
                            $daySched = $daySchedules[$dateStr] ?? null;
                            $isToday = $current->isToday();
                            $isOtherMonth = $current->month != $selectedDate->month;
                        @endphp
                        
                        <div class="calendar-day {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="day-number {{ $isToday ? 'text-primary' : '' }}">{{ $current->day }}</span>
                                @if($daySched && !$daySched->is_rest_day)
                                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.55rem; letter-spacing: 0.05em;">
                                        {{ date('h:iA', strtotime($daySched->time_in)) }}-{{ date('h:iA', strtotime($daySched->time_out)) }}
                                    </span>
                                @endif
                            </div>
                            
                            @if($record)
                                <div class="attendance-info">
                                    @php
                                        $isRestDay = !$daySched || $daySched->is_rest_day;
                                        $hasOT = isset($record->overtime_hours) && $record->overtime_hours > 0;
                                        $isLate = isset($record->late_minutes) && $record->late_minutes > 0;
                                        $isUT = isset($record->undertime_minutes) && $record->undertime_minutes > 0;
                                    @endphp

                                    @if($isRestDay && !$hasOT)
                                        <div class="text-muted small italic mb-1" style="font-size: 0.6rem;">Rest Day (Worked)</div>
                                    @elseif($isRestDay && $hasOT)
                                        <div class="text-primary small fw-bold mb-1" style="font-size: 0.6rem;">Rest Day (OT)</div>
                                    @endif

                                    @if($record->time_in && $record->time_in !== '00:00:00')
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle badge-time flex-grow-1" style="font-size: 0.65rem;" title="In: {{ date('h:i A', strtotime($record->time_in)) }}">
                                                <i class="bi bi-box-arrow-in-right"></i> {{ date('h:i A', strtotime($record->time_in)) }}
                                            </span>
                                            @if($record->time_out && $record->time_out !== '00:00:00')
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle badge-time flex-grow-1" style="font-size: 0.65rem;" title="Out: {{ date('h:i A', strtotime($record->time_out)) }}">
                                                    <i class="bi bi-box-arrow-left"></i> {{ date('h:i A', strtotime($record->time_out)) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($isLate || $isUT)
                                        <div class="d-flex gap-1 mb-1">
                                            @if($isLate)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1" style="font-size: 0.55rem;" title="{{ $record->late_minutes }}m late">LATE: {{ $record->late_minutes }}m</span>
                                            @endif
                                            @if($isUT)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-1" style="font-size: 0.55rem;" title="{{ $record->undertime_minutes }}m undertime">UT: {{ $record->undertime_minutes }}m</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if(($record->break1_out && $record->break1_out !== '00:00:00') || ($record->lunch_out && $record->lunch_out !== '00:00:00'))
                                        <div class="border-top mt-1 pt-1 d-flex flex-column gap-1">
                                            @if($record->lunch_out && $record->lunch_out !== '00:00:00')
                                                <div class="d-flex justify-content-between align-items-center bg-light rounded px-1" style="font-size: 0.6rem;">
                                                    <span class="text-muted">Lunch:</span>
                                                    <span class="fw-bold text-info">{{ date('h:i', strtotime($record->lunch_out)) }}-{{ $record->lunch_in && $record->lunch_in !== '00:00:00' ? date('h:i', strtotime($record->lunch_in)) : '??' }}</span>
                                                </div>
                                            @endif
                                            @if($record->break1_out && $record->break1_out !== '00:00:00')
                                                <div class="d-flex justify-content-between align-items-center bg-light rounded px-1" style="font-size: 0.6rem;">
                                                    <span class="text-muted">B1:</span>
                                                    <span class="fw-bold text-info">{{ date('h:i', strtotime($record->break1_out)) }}-{{ $record->break1_in && $record->break1_in !== '00:00:00' ? date('h:i', strtotime($record->break1_in)) : '??' }}</span>
                                                </div>
                                            @endif
                                            @if($record->break2_out && $record->break2_out !== '00:00:00')
                                                <div class="d-flex justify-content-between align-items-center bg-light rounded px-1" style="font-size: 0.6rem;">
                                                    <span class="text-muted">B2:</span>
                                                    <span class="fw-bold text-info">{{ date('h:i', strtotime($record->break2_out)) }}-{{ $record->break2_in && $record->break2_in !== '00:00:00' ? date('h:i', strtotime($record->break2_in)) : '??' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if(isset($record->total_hours) && $record->total_hours > 0)
                                        <div class="text-end mt-1 d-flex justify-content-between align-items-center" style="font-size: 0.6rem;">
                                            @if($hasOT)
                                                <span class="text-primary fw-bold">OT: {{ number_format($record->overtime_hours, 1) }}</span>
                                            @else
                                                <span></span>
                                            @endif
                                            <span class="fw-bold text-dark">{{ number_format($record->total_hours, 1) }} hrs</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                @php
                                    $isRestDay = !$daySched || $daySched->is_rest_day;
                                @endphp
                                
                                @if(!$isOtherMonth)
                                    @if($isRestDay)
                                        <div class="text-muted small italic opacity-75" style="font-size: 0.7rem;">Rest Day</div>
                                    @endif
                                @endif
                            @endif
                        </div>
                        @php $current->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
