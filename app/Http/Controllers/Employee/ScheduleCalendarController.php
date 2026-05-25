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
            return redirect()->route('employee.dashboard')->with('error', 'Employee record not found.');
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Logic to determine daily schedule (matching PayrollService & WebBundy logic)
        $scheduleData = $this->getMonthlySchedule($employee, $month, $year);

        return view('employee.schedule', compact('scheduleData', 'selectedDate', 'employee'));
    }

    private function getMonthlySchedule($employee, $month, $year)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $scheduleData = [];

        // Pre-fetch Site & Groups
        $employee->load(['scheduleGroup', 'site.scheduleGroup']);
        $site = $employee->site;
        $activeSchedule = $employee->active_schedule;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dayName = $date->format('l');
            $dateStr = $date->toDateString();

            $dailySched = null;
            $isRestDay = false;

            // 1. Direct Employee Group Schedule
            if ($employee->schedule_group_id && $employee->scheduleGroup) {
                $dayConfig = $employee->scheduleGroup->schedule_config[$dayName] ?? null;
                if ($dayConfig === 'OFF' || (isset($dayConfig['is_rest_day']) && $dayConfig['is_rest_day'])) {
                    $isRestDay = true;
                } else {
                    $schedId = is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig;
                    $dailySched = Schedule::find($schedId);
                }
            }

            // 2. Site Group Schedule
            if (!$dailySched && !$isRestDay && $site && $site->schedule_group_id && $site->scheduleGroup) {
                $dayConfig = $site->scheduleGroup->schedule_config[$dayName] ?? null;
                if ($dayConfig === 'OFF' || (isset($dayConfig['is_rest_day']) && $dayConfig['is_rest_day'])) {
                    $isRestDay = true;
                } else {
                    $schedId = is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig;
                    $dailySched = Schedule::find($schedId);
                }
            } 
            // 3. Site Manual Config
            elseif (!$dailySched && !$isRestDay && $site && $site->schedule_config && isset($site->schedule_config[$dayName])) {
                $config = $site->schedule_config[$dayName];
                if ($config === 'OFF') {
                    $isRestDay = true;
                } else {
                    $dailySched = Schedule::find($config);
                }
            }

            // 3. Fallback to Active Schedule (Individual or Payroll Group)
            if (!$dailySched && !$isRestDay && $activeSchedule) {
                if (is_array($activeSchedule->days) && in_array($dayName, $activeSchedule->days)) {
                    $dailySched = $activeSchedule;
                } else {
                    $isRestDay = true;
                }
            }

            $scheduleData[$dateStr] = [
                'schedule' => $dailySched,
                'is_rest_day' => $isRestDay,
            ];
        }

        return $scheduleData;
    }
}
