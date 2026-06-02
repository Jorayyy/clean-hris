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
        $employee = \App\Models\Employee::with(['scheduleGroup', 'site.scheduleGroup'])->find(Auth::user()->employee_id);
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

        // Pre-fetch schedules for the visible calendar days to avoid N+1 in blade
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
        
        $daySchedules = [];
        $tempDate = $startOfCalendar->copy();
        while ($tempDate <= $endOfCalendar) {
            $daySchedules[$tempDate->toDateString()] = $employee->getScheduleForDate($tempDate);
            $tempDate->addDay();
        }

        $schedule = $employee->active_schedule;

        return view('employee.attendance', compact('attendances', 'selectedDate', 'schedule', 'employee', 'daySchedules'));
    }
}
