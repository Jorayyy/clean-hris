@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-0">Duty Schedule</h3>
            <p class="text-muted small">Viewing roster for {{ $selectedDate->format('F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employee.schedule', ['month' => $selectedDate->copy()->subMonth()->month, 'year' => $selectedDate->copy()->subMonth()->year]) }}" class="btn btn-outline-primary">&laquo; Prev</a>
            <a href="{{ route('employee.schedule', ['month' => $selectedDate->copy()->addMonth()->month, 'year' => $selectedDate->copy()->addMonth()->year]) }}" class="btn btn-outline-primary">Next &raquo;</a>
        </div>
    </div>

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
                    .schedule-info { font-size: 0.75rem; }
                    .badge-time { display: block; margin-bottom: 2px; text-align: left; white-space: normal; height: auto; padding: 4px 8px; }
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
                            $dateStr = $current->toDateString();
                            $dayData = $scheduleData[$dateStr] ?? null;
                            $isToday = $current->isToday();
                            $isOtherMonth = $current->month != $selectedDate->month;
                        @endphp
                        
                        <div class="calendar-day {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                            <span class="day-number {{ $isToday ? 'text-primary' : '' }}">{{ $current->day }}</span>
                            
                            @if($dayData)
                                <div class="schedule-info">
                                    @if($dayData['is_rest_day'])
                                        <span class="badge bg-light text-muted border badge-time">Rest Day</span>
                                    @elseif($dayData['schedule'])
                                        <span class="badge bg-primary badge-time">
                                            <strong>{{ $dayData['schedule']->name }}</strong><br>
                                            {{ date('h:i A', strtotime($dayData['schedule']->time_in)) }} - {{ date('h:i A', strtotime($dayData['schedule']->time_out)) }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger badge-time">No Sched</span>
                                    @endif
                                </div>
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