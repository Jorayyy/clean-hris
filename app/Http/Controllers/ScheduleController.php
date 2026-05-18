<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Models\Site;
use App\Models\ScheduleGroup;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['employee', 'payrollGroup', 'scheduleGroup'])
            ->where('is_template', false)
            ->get();
        $templates = Schedule::where('is_template', true)->get();
        $sites = Site::with('scheduleGroup')->get();
        $scheduleGroups = ScheduleGroup::all();
        
        return view('admin.schedules.index', compact('schedules', 'templates', 'sites', 'scheduleGroups'));
    }

    public function create(Request $request)
    {
        $isTemplate = $request->has('template');
        $employees = Employee::all();
        $payrollGroups = PayrollGroup::all();
        $scheduleGroups = ScheduleGroup::all();
        return view('admin.schedules.create', compact('employees', 'payrollGroups', 'scheduleGroups', 'isTemplate'));
    }

    public function store(Request $request)
    {
        if ($request->has('is_template')) {
            $request->validate([
                'name' => 'required',
                'time_in' => 'required',
                'time_out' => 'required',
            ]);

            Schedule::create([
                'name' => $request->name,
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'is_template' => true,
                'days' => [], // Templates don't need assigned days
            ]);
        } else {
            $request->validate([
                'time_in' => 'required',
                'time_out' => 'required',
                'days' => 'required|array',
                'target_type' => 'required|in:individual,group,payroll',
            ]);

            Schedule::create([
                'name' => $request->name,
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'days' => $request->days,
                'employee_id' => $request->target_type === 'individual' ? $request->employee_id : null,
                'schedule_group_id' => $request->target_type === 'group' ? $request->schedule_group_id : null,
                'payroll_group_id' => $request->target_type === 'payroll' ? $request->payroll_group_id : null,
                'is_template' => false,
            ]);
        }

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $employees = Employee::all();
        $payrollGroups = PayrollGroup::all();
        $scheduleGroups = ScheduleGroup::all();
        return view('admin.schedules.edit', compact('schedule', 'employees', 'payrollGroups', 'scheduleGroups'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'time_in' => 'required',
            'time_out' => 'required',
            'days' => 'required|array',
            'target_type' => 'required|in:individual,group,payroll',
        ]);

        $schedule->update([
            'name' => $request->name,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'days' => $request->days,
            'employee_id' => $request->target_type === 'individual' ? $request->employee_id : null,
            'schedule_group_id' => $request->target_type === 'group' ? $request->schedule_group_id : null,
            'payroll_group_id' => $request->target_type === 'payroll' ? $request->payroll_group_id : null,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Schedule deleted.');
    }
}
