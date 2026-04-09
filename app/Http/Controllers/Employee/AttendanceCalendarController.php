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
        $employee = \App\Models\Employee::find(Auth::user()->employee_id);
        if (!$employee) {
            // Instead of redirecting back (which might cause a loop if the referral is the same page),
            // show a dashboard with an error or a message.
            return redirect()->route('employee.dashboard')->with('error', 'Employee record not found. Please contact HR.');
        }

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
