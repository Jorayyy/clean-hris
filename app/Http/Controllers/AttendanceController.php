<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $service)
    {
        $this->payrollService = $service;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $employees = Employee::query()
            ->when($search, function($query, $search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
            })
            ->withCount(['attendances' => function($query) {
                $query->whereDate('date', today());
            }])
            ->get();

        return view('attendance.index', compact('employees'));
    }

    public function show(Request $request, Employee $employee)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->when($date, function($query, $date) {
                $query->where('date', $date);
            })
            ->latest()
            ->get();

        return view('attendance.show', compact('employee', 'attendances', 'date'));
    }

    public function getMonthlyAttendance(Request $request, Employee $employee)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('date');

        $schedule = $employee->active_schedule;
        $workDays = $schedule ? (is_array($schedule->days) ? $schedule->days : []) : [];
        
        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $formatted = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayName = \Carbon\Carbon::parse($dateString)->format('l');
            
            $logs = $attendances->get($dateString);
            $hasAttendance = $logs && $logs->count() > 0;
            $isWorkDay = in_array($dayName, $workDays);

            $status = 'rest-day';
            if ($hasAttendance) {
                $status = 'present';
            } elseif ($isWorkDay && strtotime($dateString) <= time()) {
                $status = 'absent';
            }

            $formatted[$dateString] = [
                'status' => $status,
                'count' => $hasAttendance ? $logs->count() : 0,
                'total_hours' => $hasAttendance ? $logs->sum('total_hours') : 0,
                'is_late' => $hasAttendance ? $logs->sum('late_minutes') > 0 : false,
                'is_undertime' => $hasAttendance ? $logs->sum('undertime_minutes') > 0 : false,
                'logs' => $hasAttendance ? $logs->map(function($log) {
                    return [
                        'time_in' => ($log->time_in && $log->time_in != '00:00:00') ? date('h:i A', strtotime($log->time_in)) : '--:--',
                        'time_out' => ($log->time_out && $log->time_out != '00:00:00') ? date('h:i A', strtotime($log->time_out)) : '--:--',
                    ];
                }) : []
            ];
        }

        return response()->json($formatted);
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        return view('attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'date' => 'required',
            'time_in' => 'required',
            'time_out' => 'required',
            'break1_out' => 'nullable',
            'break1_in' => 'nullable',
            'break2_out' => 'nullable',
            'break2_in' => 'nullable',
        ]);

        $stats = $this->payrollService->calculateAttendanceStats(
            $request->time_in, 
            $request->time_out, 
            $request->employee_id, 
            $request->date
        );
        
        Attendance::create(array_merge($request->all(), $stats));
        return redirect()->route('attendance.index');
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('status', 'active')->get();
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'employee_id' => 'required',
            'date' => 'required',
            'time_in' => 'required',
            'time_out' => 'required',
            'break1_out' => 'nullable',
            'break1_in' => 'nullable',
            'break2_out' => 'nullable',
            'break2_in' => 'nullable',
        ]);

        $stats = $this->payrollService->calculateAttendanceStats(
            $request->time_in, 
            $request->time_out, 
            $request->employee_id, 
            $request->date
        );
        $attendance->update(array_merge($request->all(), $stats));
        return redirect()->route('attendance.index');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index');
    }
}
