<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Employee;
use App\Models\PayrollGroup;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['employee', 'payrollGroup'])->get();
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $employees = Employee::all();
        $groups = PayrollGroup::all();
        return view('admin.schedules.create', compact('employees', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'time_in' => 'required',
            'time_out' => 'required',
            'days' => 'required|array',
            'target_type' => 'required|in:individual,group',
        ]);

        Schedule::create([
            'name' => $request->name,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'days' => $request->days,
            'employee_id' => $request->target_type === 'individual' ? $request->employee_id : null,
            'payroll_group_id' => $request->target_type === 'group' ? $request->payroll_group_id : null,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $employees = Employee::all();
        $groups = PayrollGroup::all();
        return view('admin.schedules.edit', compact('schedule', 'employees', 'groups'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'time_in' => 'required',
            'time_out' => 'required',
            'days' => 'required|array',
            'target_type' => 'required|in:individual,group',
        ]);

        $schedule->update([
            'name' => $request->name,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'days' => $request->days,
            'employee_id' => $request->target_type === 'individual' ? $request->employee_id : null,
            'payroll_group_id' => $request->target_type === 'group' ? $request->payroll_group_id : null,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Schedule deleted.');
    }
}
