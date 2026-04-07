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

        $employee = Employee::where('employee_id', $request->employee_id_string)
            ->where('web_bundy_code', $request->web_bundy_code)
            ->first();

        if (!$employee) {
            return back()->with('bundy_error', 'Invalid Employee ID or Bundy Code.');
        }
        // IP Restriction Check
        if ($employee->registered_ip && $request->ip() !== $employee->registered_ip) {
            return back()->with('bundy_error', 'Access Denied: Please use your registered internet connection to punch. (Your IP: ' . $request->ip() . ')');
        }
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today]
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
        if ($attendance->{$column}) {
            $formattedTime = Carbon::parse($attendance->{$column})->format('h:i A');
            return back()->with('bundy_error', 'DUPLICATE PUNCH: You already punched for ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' at ' . $formattedTime . ' today.');
        }

        // Validate sequence (Optional but helpful)
        if ($request->punch_type == 'pm_out' && !$attendance->time_in) {
            return back()->with('bundy_error', 'ERROR: Cannot punch OUT without punching IN first.');
        }

        $attendance->update([
            $column => $now
        ]);

        return back()->with('bundy_success', 'SUCCESS: ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' recorded at ' . date('h:i A') . ' for ' . $employee->full_name);
    }
}
