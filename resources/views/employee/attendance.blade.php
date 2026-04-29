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
    <div class="col-md-12 mb-3">
        <div class="alert alert-info py-2 shadow-sm border-0">
            <strong>Active Schedule:</strong> {{ $schedule->name ?? 'Regular' }} ({{ $schedule->time_in }} - {{ $schedule->time_out }}) on {{ is_array($schedule->days) ? implode(', ', $schedule->days) : $schedule->days }}
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
                            $isToday = $current->isToday();
                            $isOtherMonth = $current->month != $selectedDate->month;
                        @endphp
                        
                        <div class="calendar-day {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                            <span class="day-number {{ $isToday ? 'text-primary' : '' }}">{{ $current->day }}</span>
                            
                            @if($record)
                                <div class="attendance-info">
                                    @php
                                        $isRestDay = $schedule && is_array($schedule->days) && !in_array($current->format('l'), $schedule->days);
                                        $hasOT = isset($record->overtime_hours) && $record->overtime_hours > 0;
                                    @endphp

                                    @if($isRestDay && !$hasOT)
                                        <div class="text-muted small italic mb-1" style="font-size: 0.65rem;">Rest Day (Worked)</div>
                                    @elseif($isRestDay && $hasOT)
                                        <div class="text-primary small fw-bold mb-1" style="font-size: 0.65rem;">Rest Day (OT)</div>
                                    @endif

                                    @if($record->time_in && $record->time_in !== '00:00:00')
                                        <span class="badge bg-success badge-time text-truncate" title="In: {{ date('h:i A', strtotime($record->time_in)) }}">
                                            In: {{ date('h:i A', strtotime($record->time_in)) }}
                                        </span>
                                    @endif

                                    @if($record->break1_out && $record->break1_out !== '00:00:00')
                                        <span class="badge bg-info badge-time text-truncate" title="Lunch Out: {{ date('h:i A', strtotime($record->break1_out)) }}">
                                            L-Out: {{ date('h:i A', strtotime($record->break1_out)) }}
                                        </span>
                                    @endif

                                    @if($record->break1_in && $record->break1_in !== '00:00:00')
                                        <span class="badge bg-info badge-time text-truncate" title="Lunch In: {{ date('h:i A', strtotime($record->break1_in)) }}">
                                            L-In: {{ date('h:i A', strtotime($record->break1_in)) }}
                                        </span>
                                    @endif

                                    @if($record->time_out && $record->time_out !== '00:00:00')
                                        <span class="badge bg-secondary badge-time text-truncate" title="Out: {{ date('h:i A', strtotime($record->time_out)) }}">
                                            Out: {{ date('h:i A', strtotime($record->time_out)) }}
                                        </span>
                                    @endif

                                    @if($record->late_minutes > 0)
                                        <span class="badge bg-danger badge-time">Late: {{ $record->late_minutes }}m</span>
                                    @endif
                                    @if($record->undertime_minutes > 0)
                                        <span class="badge bg-warning text-dark badge-time">UT: {{ $record->undertime_minutes }}m</span>
                                    @endif
                                    @if(isset($record->overtime_hours) && $record->overtime_hours > 0)
                                        <span class="badge bg-info text-dark badge-time">OT: {{ $record->overtime_hours }}h</span>
                                    @endif
                                </div>
                            @else
                                @php
                                    $isRestDay = $schedule && is_array($schedule->days) && !in_array($current->format('l'), $schedule->days);
                                @endphp
                                
                                @if(!$isOtherMonth)
                                    @if($isRestDay)
                                        <div class="text-muted small italic opacity-75" style="font-size: 0.7rem;">Rest Day</div>
                                    @elseif($schedule && is_array($schedule->days) && in_array($current->format('l'), $schedule->days))
                                        <div class="text-muted small italic" style="font-size: 0.7rem;">Scheduled</div>
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
