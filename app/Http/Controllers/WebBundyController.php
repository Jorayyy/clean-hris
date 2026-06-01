<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebBundyPunchRequest;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AuthorizedNetwork;
use Carbon\Carbon;

class WebBundyController extends Controller
{
    public function showBundy(Request $request)
    {
        // Global IP Lockdown: Only allow access to the Bundy page from authorized networks
        $isAuthorized = AuthorizedNetwork::isAuthorized($request->ip());

        if (!$isAuthorized) {
            return view('auth.bundy')->with('unauthorized_ip', $request->ip());
        }

        return view('auth.bundy');
    }

    public function checkStatus($employee_id)
    {
        $employee = Employee::where('employee_id', $employee_id)->first();
        if (!$employee) {
            return response()->json(['error' => 'Invalid ID'], 404);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $targetDate = $today;

        // Check for open shift from yesterday (for night shifts)
        $yesterday = Carbon::yesterday()->toDateString();
        $yesterdayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $yesterday)
            ->where(function ($q) {
                $q->where('time_in', '!=', '00:00:00')->whereNotNull('time_in');
            })
            ->where(function ($q) {
                $q->where('time_out', '00:00:00')->orWhereNull('time_out');
            })
            ->first();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // If yesterday is open and today hasn't started, priority is yesterday
        if ($yesterdayAttendance && (!$todayAttendance || $todayAttendance->time_in === '00:00:00')) {
            $targetDate = $yesterday;
        }

        $attendance = $targetDate === $yesterday ? $yesterdayAttendance : $todayAttendance;

        return response()->json([
            'full_name' => $employee->full_name,
            'date' => $targetDate,
            'is_in' => $attendance && $attendance->time_in && $attendance->time_in !== '00:00:00',
            'is_lunch_out' => $attendance && $attendance->lunch_out && $attendance->lunch_out !== '00:00:00',
            'is_lunch_in' => $attendance && $attendance->lunch_in && $attendance->lunch_in !== '00:00:00',
            'is_break1_out' => $attendance && $attendance->break1_out && $attendance->break1_out !== '00:00:00',
            'is_break1_in' => $attendance && $attendance->break1_in && $attendance->break1_in !== '00:00:00',
            'is_break2_out' => $attendance && $attendance->break2_out && $attendance->break2_out !== '00:00:00',
            'is_break2_in' => $attendance && $attendance->break2_in && $attendance->break2_in !== '00:00:00',
            'is_out' => $attendance && $attendance->time_out && $attendance->time_out !== '00:00:00',
        ]);
    }

    public function punch(WebBundyPunchRequest $request)
    {
        if ($request->punch_type === 'none') {
            return back()->with('bundy_error', 'Invalid action. Please click one of the punch buttons.');
        }

        // Global IP Lockdown: Stop punches if not on an authorized network
        $isAuthorized = AuthorizedNetwork::isAuthorized($request->ip());

        if (!$isAuthorized) {
            return back()->with('bundy_error', 'Access Denied: Your current network (IP: ' . $request->ip() . ') is not authorized for Web Bundy punches.');
        }

        $employee = Employee::where('employee_id', $request->employee_id_string)->first();

        if (!$employee) {
            return back()->with('bundy_error', 'Invalid Employee ID.');
        }

        if (empty($employee->web_bundy_code)) {
            return back()->with('bundy_error', 'No Web Bundy Code Set: Please contact HR to assign a passcode for your account before you can punch.');
        }

        if ($employee->web_bundy_code !== $request->web_bundy_code) {
            return back()->with('bundy_error', 'Incorrect Bundy Passcode.');
        }

        // IP Restriction Check
        if ($employee->registered_ip && $request->ip() !== $employee->registered_ip) {
            return back()->with('bundy_error', 'Access Denied: Please use your registered internet connection to punch. (Your IP: ' . $request->ip() . ')');
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $schedule = $employee->active_schedule;

        // Check if employee has a schedule for today
        $dayName = $now->format('l');
        
        // Priority 1: Use direct Employee-based schedule group
        $scheduleInTime = null;
        $scheduleOutTime = null;
        $isRestDay = false;

        if ($employee->schedule_group_id && $employee->scheduleGroup) {
            $dayConfig = $employee->scheduleGroup->schedule_config[$dayName] ?? null;
            if ($dayConfig === 'OFF' || (isset($dayConfig['is_rest_day']) && $dayConfig['is_rest_day'])) {
                $isRestDay = true;
            } else {
                $schedId = is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig;
                $siteSchedule = \App\Models\Schedule::find($schedId);
                if ($siteSchedule) {
                    $scheduleInTime = $siteSchedule->time_in;
                    $scheduleOutTime = $siteSchedule->time_out;
                }
            }
        }

        // Priority 2: Use Site-based schedule config if it exists (mirroring PayrollService logic)
        if (!$scheduleInTime && !$isRestDay && $employee->site_id) {
            $site = \App\Models\Site::with('scheduleGroup')->find($employee->site_id);
            if ($site && $site->schedule_group_id && $site->scheduleGroup) {
                $dayConfig = $site->scheduleGroup->schedule_config[$dayName] ?? null;
                if ($dayConfig === 'OFF' || (isset($dayConfig['is_rest_day']) && $dayConfig['is_rest_day'])) {
                    $isRestDay = true;
                } else {
                    $schedId = is_array($dayConfig) ? ($dayConfig['id'] ?? null) : $dayConfig;
                    $siteSchedule = \App\Models\Schedule::find($schedId);
                    if ($siteSchedule) {
                        $scheduleInTime = $siteSchedule->time_in;
                        $scheduleOutTime = $siteSchedule->time_out;
                    }
                }
            } elseif ($site && $site->schedule_config && isset($site->schedule_config[$dayName])) {
                $config = $site->schedule_config[$dayName];
                if ($config === 'OFF') {
                    $isRestDay = true;
                } else {
                    $siteSchedule = \App\Models\Schedule::find($config);
                    if ($siteSchedule) {
                        $scheduleInTime = $siteSchedule->time_in;
                        $scheduleOutTime = $siteSchedule->time_out;
                    }
                }
            }
        }

        // Priority 2: Fallback to direct active_schedule
        if (!$scheduleInTime && !$isRestDay && $schedule) {
            if (is_array($schedule->days) && in_array($dayName, $schedule->days)) {
                $scheduleInTime = $schedule->time_in;
                $scheduleOutTime = $schedule->time_out;
            } else {
                $isRestDay = true;
            }
        }

        // BLOCK PUNCH IF NO SCHEDULE EXISTS (Not even as a Rest Day/Manual Plot)
        if (!$scheduleInTime && !$isRestDay && !$schedule) {
            return back()->with('bundy_error', 'NO SCHEDULE FOUND: Please contact administrator to assign you a schedule before you can punch!');
        }

        if ($isRestDay && !in_array($request->punch_type, ['am_in', 'pm_out'])) {
            // Optional: You might want to allow punches on rest days, but maybe with a warning or as OT.
            // For now, let's just proceed but log it.
        }

        // NIGHT SHIFT LOGIC: Intelligent Target Date Detection
        $targetDate = $today;
        
        // If they are punching anything EXCEPT 'am_in', check if they have an open shift from yesterday
        if ($request->punch_type !== 'am_in') {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            // If no record today, OR today's record is empty but yesterday has an open shift, use yesterday
            if (!$todayAttendance || ($todayAttendance->time_in === '00:00:00' || !$todayAttendance->time_in)) {
                $yesterday = Carbon::yesterday()->toDateString();
                $yesterdayAttendance = Attendance::where('employee_id', $employee->id)
                    ->where('date', $yesterday)
                    ->where(function($q) {
                        $q->where('time_in', '!=', '00:00:00')->whereNotNull('time_in');
                    })
                    ->where(function($q) {
                        $q->where('time_out', '00:00:00')->orWhereNull('time_out');
                    })
                    ->first();
                
                if ($yesterdayAttendance) {
                    $targetDate = $yesterday;
                }
            }
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $targetDate)
            ->first();

        if (!$attendance) {
            if ($request->punch_type === 'am_in') {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $targetDate,
                    'time_in' => '00:00:00',
                    'time_out' => '00:00:00',
                    'break1_out' => '00:00:00',
                    'break1_in' => '00:00:00',
                    'break2_out' => '00:00:00',
                    'break2_in' => '00:00:00',
                    'total_hours' => 0,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                ]);
            } else {
                return back()->with('bundy_error', 'SEQUENCE ERROR: No "Start Shift" record found for ' . ($targetDate === $today ? 'today' : 'yesterday') . '. Please punch START SHIFT first.');
            }
        }

        // Map punch types to database columns
        $typeMap = [
            'am_in' => 'time_in',
            'am_out' => 'break1_out',
            'pm_in' => 'break1_in',
            'pm_out' => 'time_out',
            'lunch_out' => 'lunch_out',
            'lunch_in' => 'lunch_in',
            'break1_out' => 'break1_out',
            'break1_in' => 'break1_in',
            'break2_out' => 'break2_out',
            'break2_in' => 'break2_in',
        ];

        $column = $typeMap[$request->punch_type] ?? $request->punch_type;

        // Check if already punched
        if ($attendance->{$column} !== null && $attendance->{$column} !== '00:00:00') {
            $formattedTime = Carbon::parse($attendance->{$column})->format('H:i');
            return back()->with('bundy_error', 'DUPLICATE PUNCH: You already punched for ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' at ' . $formattedTime . ' for shift starting ' . Carbon::parse($attendance->date)->format('M d') . '.');
        }

        // Strict Sequence Validations (Independent for each break type)
        if ($request->punch_type !== 'am_in' && ($attendance->time_in === '00:00:00' || !$attendance->time_in)) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You must punch START SHIFT first.');
        }

        if ($request->punch_type == 'lunch_in' && ($attendance->lunch_out === '00:00:00' || !$attendance->lunch_out)) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch LUNCH IN because you haven\'t punched LUNCH OUT.');
        }

        if ($request->punch_type == 'break1_in' && ($attendance->break1_out === '00:00:00' || !$attendance->break1_out)) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch 1st BREAK IN because you haven\'t punched 1st BREAK OUT.');
        }

        if ($request->punch_type == 'break2_in' && ($attendance->break2_out === '00:00:00' || !$attendance->break2_out)) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch 2nd BREAK IN because you haven\'t punched 2nd BREAK OUT.');
        }

        // Update the specific punch column
        $attendance->update([
            $column => $now->toTimeString()
        ]);

        // Recalculate stats
        $payrollService = app(\App\Services\PayrollService::class);
        $timeIn = ($attendance->time_in && $attendance->time_in !== '00:00:00') ? $attendance->time_in : null;
        $timeOut = ($attendance->time_out && $attendance->time_out !== '00:00:00') ? $attendance->time_out : null;

        if ($timeIn) {
            $stats = $payrollService->calculateAttendanceStats(
                $timeIn, 
                $timeOut ?? $now->toTimeString(), 
                $employee->id, 
                $attendance->date
            );
            $attendance->update($stats);
        }

        $formattedTime = Carbon::now()->format('H:i');
        return back()->with('bundy_success', 'SUCCESS: ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' recorded at ' . $formattedTime . ' for ' . $employee->full_name);
    }
}
