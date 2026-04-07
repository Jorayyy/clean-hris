<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceCalendarController extends Controller
{
    public function index(Request $request)
    {
        $employee = \App\Models\Employee::where('id', Auth::user()->employee_id)->first();
        if (!$employee) return back()->with('error', 'No employee profile.');

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy('date');

        $schedule = $employee->active_schedule;

        return view('employee.attendance', compact('attendances', 'selectedDate', 'schedule', 'employee'));
    }
}
