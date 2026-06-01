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
        $employee = Employee::find(Auth::user()->employee_id);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        $daysInMonth = $selectedDate->daysInMonth;
        $scheduleData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateStr = $date->toDateString();

            // Use the centralized robust logic from Employee model
            $sched = $employee->getScheduleForDate($dateStr);
            
            $isRestDay = false;
            if (!$sched) {
                // Check if it's explicitly a rest day
                $manual = $employee->schedules()->whereDate('schedule_date', $dateStr)->first();
                if ($manual && $manual->is_rest_day) {
                    $isRestDay = true;
                } else {
                    $phpDayOfWeek = $date->dayOfWeek;
                    $pattern = $employee->schedules()
                        ->whereNull('schedule_date')
                        ->where('day_of_week', $phpDayOfWeek)
                        ->first();
                    if ($pattern && $pattern->is_rest_day) $isRestDay = true;
                }
                
                // If still not determined, check group configs
                if (!$isRestDay) {
                    $dayName = $date->format('l');
                    $group = $employee->scheduleGroup ?? $employee->site?->scheduleGroup;
                    if ($group) {
                        $config = $group->schedule_config[$dayName] ?? null;
                        if ($config === 'OFF' || (isset($config['is_rest_day']) && $config['is_rest_day'])) {
                            $isRestDay = true;
                        }
                    }
                }
            }

            $scheduleData[$dateStr] = [
                'schedule' => $sched,
                'is_rest_day' => $isRestDay,
            ];
        }

        return view('employee.schedule', compact('scheduleData', 'selectedDate', 'employee'));
    }

    // Removed getMonthlySchedule to favor decentralized/Employee model logic
}
