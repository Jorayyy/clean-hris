<?php

namespace App\Http\Controllers;

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
        $isAuthorized = AuthorizedNetwork::where('ip_address', $request->ip())
            ->where('is_active', true)
            ->exists();

        if (!$isAuthorized) {
            return view('auth.bundy')->with('unauthorized_ip', $request->ip());
        }

        return view('auth.bundy');
    }

    public function punch(Request $request)
    {
        // Global IP Lockdown: Stop punches if not on an authorized network
        $isAuthorized = AuthorizedNetwork::where('ip_address', $request->ip())
            ->where('is_active', true)
            ->exists();

        if (!$isAuthorized) {
            return back()->with('bundy_error', 'Access Denied: Your current network (IP: ' . $request->ip() . ') is not authorized for Web Bundy punches.');
        }

        $request->validate([
            'employee_id_string' => 'required',
            'web_bundy_code' => 'required',
            'punch_type' => 'required|in:am_in,am_out,pm_in,pm_out,break1_out,break1_in,break2_out,break2_in'
        ]);

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

        // NIGHT SHIFT LOGIC: Determine if we should look for yesterday's record
        // If current time is early morning (e.g., 00:00 to 10:00 AM) and they are punching OUT or BREAK IN
        // we check if they have an active session from yesterday.
        $targetDate = $today;
        if ($now->hour < 10 && in_array($request->punch_type, ['pm_out', 'pm_in', 'break2_in'])) {
            $yesterday = Carbon::yesterday()->toDateString();
            $yesterdayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $yesterday)
                ->where('time_in', '!=', '00:00:00')
                ->where(function($q) {
                    $q->where('time_out', '00:00:00')->orWhereNull('time_out');
                })
                ->first();
            
            if ($yesterdayAttendance) {
                $targetDate = $yesterday;
            }
        }

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $targetDate],
            [
                'time_in' => '00:00:00',
                'time_out' => '00:00:00',
                'break1_out' => '00:00:00',
                'break1_in' => '00:00:00',
                'break2_out' => '00:00:00',
                'break2_in' => '00:00:00',
                'total_hours' => 0,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
            ]
        );

        // Map punch types to database columns
        $typeMap = [
            'am_in' => 'time_in',
            'am_out' => 'break1_out',
            'pm_in' => 'break1_in',
            'pm_out' => 'time_out',
        ];

        $column = $typeMap[$request->punch_type] ?? $request->punch_type;

        // Check if already punched
        if ($attendance->{$column} !== null && $attendance->{$column} !== '00:00:00') {
            $formattedTime = Carbon::parse($attendance->{$column})->format('h:i A');
            return back()->with('bundy_error', 'DUPLICATE PUNCH: You already punched for ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' at ' . $formattedTime . ' today.');
        }

        // Sequence Validations
        if ($request->punch_type == 'pm_out' && ($attendance->time_in === null || $attendance->time_in === '00:00:00')) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch OUT (End Shift) because you haven\'t punched IN (Start Shift) today.');
        }

        if ($request->punch_type == 'pm_out' && ($attendance->break1_in === null || $attendance->break1_in === '00:00:00')) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch OUT (End Shift) because you are still on LUNCH. Please punch LUNCH IN first to resume your shift.');
        }
        
        if ($request->punch_type == 'am_out' && ($attendance->time_in === null || $attendance->time_in === '00:00:00')) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch LUNCH OUT because you haven\'t punched IN (Start Shift) today.');
        }

        if ($request->punch_type == 'pm_in' && ($attendance->break1_out === null || $attendance->break1_out === '00:00:00')) {
            return back()->with('bundy_error', 'SEQUENCE ERROR: You cannot punch LUNCH IN (Return) because you haven\'t recorded a LUNCH OUT yet.');
        }

        // Update the specific punch column
        $attendance->update([
            $column => $now->toTimeString()
        ]);

        // Recalculate stats based on current punches
        $payrollService = app(\App\Services\PayrollService::class);
        $timeOut = ($attendance->time_out && $attendance->time_out !== '00:00:00') 
            ? $attendance->time_out 
            : $now->toTimeString();

        $stats = $payrollService->calculateAttendanceStats(
            $attendance->time_in, 
            $timeOut, 
            $employee->id, 
            $attendance->date
        );
        
        $attendance->update($stats);

        return back()->with('bundy_success', 'SUCCESS: ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' recorded at ' . date('h:i A') . ' for ' . $employee->full_name);
    }
}
