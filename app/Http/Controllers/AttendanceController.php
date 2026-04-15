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
