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
        $query = Attendance::with('employee');
        if($request->date) {
            $query->where('date', $request->date);
        }
        $attendances = $query->latest()->get();
        return view('attendance.index', compact('attendances'));
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
