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
        $shifts = \App\Models\Shift::all();
        $schedules = Schedule::with(['employee', 'payrollGroup', 'scheduleGroup', 'shift'])
            ->where('is_template', false)
            ->latest()
            ->get();
        $sites = Site::with('scheduleGroup')->get();
        $employeeCount = Employee::count();
        $directAssignmentCount = Schedule::whereNotNull('employee_id')->count();
        
        return view('admin.schedules.index', compact('shifts', 'schedules', 'sites', 'employeeCount', 'directAssignmentCount'));
    }

    public function shiftsIndex()
    {
        $shifts = \App\Models\Shift::all();
        return view('admin.schedules.shifts.index', compact('shifts'));
    }

    public function shiftsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'time_in' => 'required',
            'time_out' => 'required',
            'color' => 'required|string|max:7',
        ]);

        \App\Models\Shift::create($request->all());

        return back()->with('success', 'Shift created successfully.');
    }

    public function shiftsUpdate(Request $request, \App\Models\Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'time_in' => 'required',
            'time_out' => 'required',
            'color' => 'required|string|max:7',
        ]);

        $shift->update($request->all());

        return back()->with('success', 'Shift updated successfully.');
    }

    public function shiftsDestroy(\App\Models\Shift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift deleted successfully.');
    }

    public function visualPlotting()
    {
        $employees = Employee::with(['schedules', 'site'])->get();
        $shifts = \App\Models\Shift::where('is_active', true)->get();
        $sites = Site::all();
        
        return view('admin.schedules.plotting.index', compact('employees', 'shifts', 'sites'));
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'shift_id' => 'required|exists:shifts,id',
            'days' => 'required|array',
        ]);

        $shift = \App\Models\Shift::find($request->shift_id);

        foreach ($request->employee_ids as $empId) {
            Schedule::updateOrCreate(
                ['employee_id' => $empId, 'is_template' => false],
                [
                    'shift_id' => $shift->id,
                    'time_in' => $shift->time_in,
                    'time_out' => $shift->time_out,
                    'days' => $request->days,
                    'is_template' => false,
                    'assigned_by' => auth()->id(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Schedules updated successfully.']);
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
