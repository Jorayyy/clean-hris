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
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->withCount(['attendances as attendances_count' => function ($query) {
                $query->whereDate('date', today());
            }])
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
        if ($request->filled('start') && $request->filled('end')) {
            $startDate = \Carbon\Carbon::parse($request->get('start'))->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->get('end'))->startOfDay();
            if ($endDate->lessThan($startDate)) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }
            if ($startDate->diffInDays($endDate) > 80) {
                $endDate = $startDate->copy()->addDays(80);
            }
        } else {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->startOfDay();
        }

        $formatTime = function ($value) {
            if (!$value || $value === '00:00:00') {
                return null;
            }

            return date('H:i', strtotime($value));
        };

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('time_in')
            ->get()
            ->groupBy(function ($log) {
                return \Carbon\Carbon::parse($log->date)->toDateString();
            });

        $datedSchedules = \App\Models\Schedule::where('employee_id', $employee->id)
            ->where('is_template', false)
            ->whereBetween('schedule_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy(function ($schedule) {
                return $schedule->schedule_date ? $schedule->schedule_date->toDateString() : null;
            });

        $patternSchedules = \App\Models\Schedule::where('employee_id', $employee->id)
            ->where('is_template', false)
            ->whereNull('schedule_date')
            ->get();

        $resolveShiftById = function ($id) {
            if (!$id) {
                return null;
            }

            $shift = \App\Models\Shift::find($id);
            if ($shift) {
                return ['name' => $shift->name, 'time_in' => $shift->time_in, 'time_out' => $shift->time_out];
            }

            $custom = \App\Models\CustomShift::find($id);
            if ($custom) {
                return ['name' => $custom->title, 'time_in' => $custom->start_time, 'time_out' => $custom->end_time];
            }

            return null;
        };

        $scheduleFromRow = function ($row) use ($resolveShiftById) {
            if ($row->time_in && $row->time_out) {
                return ['name' => $row->name ?? 'Shift', 'time_in' => $row->time_in, 'time_out' => $row->time_out];
            }

            $resolved = $resolveShiftById($row->shift_id);
            if ($resolved) {
                return $resolved;
            }

            if ($row->custom_shift_id) {
                $custom = \App\Models\CustomShift::find($row->custom_shift_id);
                if ($custom) {
                    return ['name' => $custom->title, 'time_in' => $custom->start_time, 'time_out' => $custom->end_time];
                }
            }

            return null;
        };

        $configDayValue = function ($config, $dayName) {
            if (!is_array($config)) {
                return null;
            }

            foreach ([$dayName, strtoupper($dayName), ucfirst(strtolower($dayName))] as $key) {
                if (array_key_exists($key, $config)) {
                    return $config[$key];
                }
            }

            return null;
        };

        $ownGroupConfig = $employee->scheduleGroup ? $employee->scheduleGroup->schedule_config : null;

        $site = $employee->site ? $employee->site->load('scheduleGroup') : null;
        $siteConfig = null;
        if ($site) {
            $siteConfig = $site->scheduleGroup ? $site->scheduleGroup->schedule_config : $site->schedule_config;
        }

        $formatted = [];
        for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
            $dateString = $date->toDateString();
            $dayName = $date->format('l');
            $data = [
                'attendance' => null,
                'schedule' => null,
            ];

            $logs = $attendances->get($dateString);
            if ($logs && $logs->count() > 0) {
                $realLogs = $logs->filter(function ($log) {
                    return collect([
                        $log->time_in, $log->time_out,
                        $log->break1_out, $log->break1_in,
                        $log->lunch_out, $log->lunch_in,
                        $log->break2_out, $log->break2_in,
                    ])->contains(function ($value) {
                        return $value && $value !== '00:00:00';
                    });
                });

                if ($realLogs->isNotEmpty()) {
                    $data['attendance'] = [
                        'status' => 'present',
                        'total_hours' => (float) $realLogs->sum('total_hours'),
                        'logs' => $realLogs->map(function ($log) use ($formatTime) {
                            return [
                                'time_in' => $formatTime($log->time_in),
                                'time_out' => $formatTime($log->time_out),
                                'break1_out' => $formatTime($log->break1_out),
                                'break1_in' => $formatTime($log->break1_in),
                                'lunch_out' => $formatTime($log->lunch_out),
                                'lunch_in' => $formatTime($log->lunch_in),
                                'break2_out' => $formatTime($log->break2_out),
                                'break2_in' => $formatTime($log->break2_in),
                            ];
                        })->values(),
                    ];
                }
            }

            $sched = null;
            $schedSource = null;

            $directPlot = $datedSchedules->get($dateString)?->first();
            if ($directPlot) {
                $sched = $scheduleFromRow($directPlot);
                $schedSource = 'individual';
            }

            if (!$sched) {
                $pattern = $patternSchedules->first(function ($p) use ($dayName) {
                    return $p->days && is_array($p->days) && in_array($dayName, $p->days);
                });
                if ($pattern) {
                    $sched = $scheduleFromRow($pattern);
                    $schedSource = 'individual';
                }
            }

            if (!$sched) {
                $dayConfig = $configDayValue($ownGroupConfig, $dayName);
                if ($dayConfig !== 'OFF' && (!is_array($dayConfig) || empty($dayConfig['is_rest_day']))) {
                    $resolved = $resolveShiftById(is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig);
                    if ($resolved) {
                        $sched = $resolved;
                        $schedSource = 'group';
                    }
                }
            }

            if (!$sched) {
                $dayConfig = $configDayValue($siteConfig, $dayName);
                if ($dayConfig !== 'OFF' && (!is_array($dayConfig) || empty($dayConfig['is_rest_day']))) {
                    $resolved = $resolveShiftById(is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig);
                    if ($resolved) {
                        $sched = $resolved;
                        $schedSource = 'fixed';
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
            'lunch_out' => 'nullable',
            'lunch_in' => 'nullable',
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
            'lunch_out' => 'nullable',
            'lunch_in' => 'nullable',
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

        $warnings = [];
        if ($stats['total_hours'] <= 0) {
            $warnings[] = "Note: Calculated Regular Hours is 0. Check if the punch times (1:10 AM) are outside the scheduled shift (e.g. 8:00 AM) or if breaks exceed the stay duration.";
        }
        
        if (empty($request->time_out) || $request->time_out === '00:00:00') {
            $warnings[] = "Note: Missing Time Out punch. DTR will remain unverified until fixed.";
        }

        if (!empty($warnings)) {
            session()->flash('warning', implode(' ', $warnings));
        }

        return redirect()->route('attendance.index')->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index');
    }

    public function toggleOt(Attendance $attendance)
    {
        $attendance->update([
            'ot_authorized' => !$attendance->ot_authorized
        ]);

        // Recalculate stats since OT might have changed
        $stats = $this->payrollService->calculateAttendanceStats(
            $attendance->time_in,
            $attendance->time_out,
            $attendance->employee_id,
            $attendance->date
        );
        $attendance->update($stats);

        // Find linked DTR and update totals
        $dtr = \App\Models\Dtr::where('employee_id', $attendance->employee_id)
            ->whereDate('start_date', '<=', $attendance->date)
            ->whereDate('end_date', '>=', $attendance->date)
            ->first();
        
        if ($dtr) {
            $attendances = Attendance::where('employee_id', $dtr->employee_id)
                ->whereDate('date', '>=', $dtr->start_date)
                ->whereDate('date', '<=', $dtr->end_date)
                ->get();
            
            $dtr->update([
                'total_overtime_hours' => $attendances->sum('overtime_hours'),
                'total_regular_hours' => $attendances->sum('total_hours'),
                'total_late_minutes' => (int)$attendances->sum('late_minutes'),
                'total_undertime_minutes' => (int)$attendances->sum('undertime_minutes'),
            ]);
        }

        return back()->with('success', 'Overtime authorization updated.');
    }
}
