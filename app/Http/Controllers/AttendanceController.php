<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\PayrollService;
use Illuminate\Http\Request;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
            ->with(['attendances' => function($query) {
                $query->whereDate('date', today());
            }])
            ->with(['lastAttendance' => function($query) {
                $query->orderBy('date', 'desc')->orderBy('time_in', 'desc');
            }])
            ->get();

        return view('attendance.index', compact('employees'));
    }

    public function show(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $date = $request->get('date');
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->when($date, function($query, $date) {
                $query->whereDate('date', $date);
            })
            ->when(!$date, function($query) use ($month, $year) {
                $query->whereMonth('date', $month)
                      ->whereYear('date', $year);
            })
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get();

        return view('attendance.show', compact('employee', 'attendances', 'date', 'month', 'year'));
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

        // Get Direct Individual Schedules
        $individualSchedules = \App\Models\Schedule::where('employee_id', $employee->id)
            ->where('is_template', false)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('date');

        $site = $employee->site ? $employee->site->load('scheduleGroup') : null;
        $siteConfig = null;

        if ($site) {
            $siteConfig = ($site->scheduleGroup) ? $site->scheduleGroup->schedule_config : $site->schedule_config;
        }

        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $formatted = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayName = \Carbon\Carbon::parse($dateString)->format('l');
            $data = [
                'attendance' => null,
                'schedule' => null
            ];
            
            // 1. Check Attendance
            $logs = $attendances->get($dateString);
            if ($logs && $logs->count() > 0) {
                $data['attendance'] = [
                    'status' => 'present',
                    'logs' => $logs->map(function($log) {
                        return [
                            'time_in' => $log->time_in ? date('h:i A', strtotime($log->time_in)) : '--:--',
                            'time_out' => $log->time_out ? date('h:i A', strtotime($log->time_out)) : '--:--',
                            'break1_out' => $log->break1_out ? date('h:i A', strtotime($log->break1_out)) : null,
                            'break1_in' => $log->break1_in ? date('h:i A', strtotime($log->break1_in)) : null,
                        ];
                    })
                ];
            }

            // 2. Check Schedule (Individual Priority -> Group -> Site)
            $sched = null;
            $schedSource = null;

            // Direct plotting
            if ($individualSchedules->has($dateString)) {
                $s = $individualSchedules->get($dateString)->first();
                $sched = [
                    'title' => $s->title ?? 'Shift',
                    'time_in' => $s->time_in,
                    'time_out' => $s->time_out,
                ];
                $schedSource = 'individual';
            } 
            // Group/Site plotting
            elseif ($siteConfig && isset($siteConfig[$dayName])) {
                $dayConfig = $siteConfig[$dayName];
                if ($dayConfig !== 'OFF' && (!is_array($dayConfig) || !($dayConfig['is_rest_day'] ?? false))) {
                    $schedId = is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig;
                    $s = \App\Models\Schedule::find($schedId);
                    if ($s) {
                        $sched = [
                            'title' => $s->title,
                            'time_in' => $s->time_in,
                            'time_out' => $s->time_out,
                        ];
                        $schedSource = ($site->scheduleGroup) ? 'group' : 'fixed';
                    }
                }
            }

            if ($sched) {
                $data['schedule'] = array_merge($sched, ['source' => $schedSource]);
            }

            $formatted[$dateString] = $data;
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
