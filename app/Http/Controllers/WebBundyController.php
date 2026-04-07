<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class WebBundyController extends Controller
{
    public function punch(Request $request)
    {
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

        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today]
        );

        // Map punch types to database columns if they don't match exactly
        // Assuming we might need to add these columns to the attendance table if not present
        $column = $request->punch_type;

        // Check if already punched
        if ($attendance->{$column}) {
             return back()->with('bundy_error', 'Already punched for ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' today.');
        }

        $attendance->update([
            $column => $now
        ]);

        return back()->with('bundy_success', 'Successful ' . str_replace('_', ' ', strtoupper($request->punch_type)) . ' at ' . date('h:i A') . ' for ' . $employee->full_name);
    }
}
