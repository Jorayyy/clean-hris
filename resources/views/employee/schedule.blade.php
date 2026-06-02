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

    @php
        $activeSched = $employee->active_schedule;
    @endphp

    @if($activeSched)
        <div class="col-md-12 mb-3">
            <div class="alert alert-info py-2 shadow-sm border-0 d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Active Schedule:</strong> {{ $activeSched->name ?? 'Regular' }} 
                    ({{ date('h:i A', strtotime($activeSched->time_in)) }} - {{ date('h:i A', strtotime($activeSched->time_out)) }})
                    on {{ is_array($activeSched->days) ? implode(', ', $activeSched->days) : ($activeSched->day_of_week !== null ? \Carbon\Carbon::create()->dayOfWeek((int)$activeSched->day_of_week)->format('l') : 'All Days') }}
                </div>
                <div class="small opacity-75 fw-bold">PROCESSED</div>
            </div>
        </div>
    @endif

    <div class="col-md-12">
        <div class="card shadow border-0 overflow-hidden">
            <div class="card-body p-0">
                <style>
                    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border-top: 1px solid #dee2e6; border-left: 1px solid #dee2e6; }
                    .calendar-day-header { background: #f8f9fa; padding: 10px; text-align: center; font-weight: bold; border-right: 1px solid #dee2e6; border-bottom: 2px solid #dee2e6; }
                    .calendar-day { min-height: 120px; padding: 10px; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; background: #fff; position: relative; }
                    .calendar-day.other-month { background: #f1f3f5; }
                    .calendar-day.today { background: #fffdf0; }
                    .calendar-day.today::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: #0d6efd; }
                    .day-number { font-weight: bold; margin-bottom: 8px; display: block; font-size: 1.1rem; }
                    .schedule-info { font-size: 0.75rem; }
                    .badge-time { display: block; margin-bottom: 4px; text-align: left; white-space: normal; height: auto; padding: 8px; border-radius: 8px; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
                    .badge-time strong { font-size: 0.7rem; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
                    .badge-time i { margin-right: 4px; opacity: 0.8; }
                </style>
                
                <div class="calendar-grid">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                        <div class="calendar-day-header small text-uppercase tracking-wider text-muted py-2">{{ $day }}</div>
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
                            <span class="day-number {{ $isToday ? 'text-primary' : '' }} opacity-50">{{ $current->day }}</span>
                            
                            @if($dayData)
                                <div class="schedule-info mt-1">
                                    @if($dayData['is_rest_day'])
                                        <div class="p-2 border rounded-3 bg-light text-center">
                                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 1px;">Rest Day</span>
                                        </div>
                                    @elseif($dayData['schedule'])
                                        <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 badge-time">
                                            <strong class="text-primary">{{ $dayData['schedule']->name }}</strong>
                                            <div class="d-flex align-items-center mt-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-clock-fill"></i>
                                                {{ date('h:i A', strtotime($dayData['schedule']->time_in)) }} - {{ date('h:i A', strtotime($dayData['schedule']->time_out)) }}
                                            </div>
                                            @if($dayData['schedule']->remarks)
                                                <div class="mt-1 small opacity-75 italic" style="font-size: 0.65rem;">
                                                    <i class="bi bi-info-circle"></i> {{ $dayData['schedule']->remarks }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-2 border border-danger border-opacity-25 rounded-3 bg-danger bg-opacity-10 text-center">
                                            <span class="text-danger small fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 1px;">No Schedule</span>
                                        </div>
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