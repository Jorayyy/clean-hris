<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Site;
use App\Models\Schedule;

class ScheduleCalendarController extends Controller
{
    public function index(Request $request)
    {
        $employee = Employee::with(['scheduleGroup', 'site.scheduleGroup'])->find(Auth::user()->employee_id);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Pre-fetch schedules for the full calendar view (including buffer days)
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $scheduleData = [];
        $tempDate = $startOfCalendar->copy();
        
        while ($tempDate <= $endOfCalendar) {
            $dateStr = $tempDate->toDateString();
            $sched = $employee->getScheduleForDate($dateStr);
            
            $isRestDay = false;
            if ($sched && $sched->is_rest_day) {
                $isRestDay = true;
            } elseif (!$sched) {
                $isRestDay = true; // No schedule found is treated as a rest day for the roster view
            }

            $scheduleData[$dateStr] = [
                'schedule' => $sched,
                'is_rest_day' => $isRestDay,
            ];
            
            $tempDate->addDay();
        }

        return view('employee.schedule', compact('scheduleData', 'selectedDate', 'employee'));
    }

    // Removed getMonthlySchedule to favor decentralized/Employee model logic
}
