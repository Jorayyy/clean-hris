<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleGroup;
use App\Models\Site;
use Illuminate\Http\Request;

class ScheduleGroupController extends Controller
{
    public function index()
    {
        $groups = ScheduleGroup::with(['creator'])->withCount('sites')->get();
        return view('admin.settings.schedule-groups.index', compact('groups'));
    }

    public function create()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedules = \App\Models\Schedule::where('is_template', true)->get();
        $sites = Site::all();
        return view('admin.settings.schedule-groups.create', compact('days', 'schedules', 'sites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_ids' => 'nullable|array',
            'site_ids.*' => 'exists:sites,id',
            'primary_site_id' => 'nullable|exists:sites,id'
        ]);

        $group = ScheduleGroup::create([
            'name' => $validated['name'],
            'created_by' => auth()->id(),
            'site_id' => $validated['primary_site_id'] ?? ($validated['site_ids'][0] ?? null),
            'schedule_config' => [] // Initialize empty
        ]);

        if (!empty($validated['site_ids'])) {
            Site::whereIn('id', $validated['site_ids'])->update(['schedule_group_id' => $group->id]);
        }

        return redirect()->route('admin.settings.schedule-groups.plot', $group->id)
            ->with('success', 'Master Blueprint created successfully. Now, please plot the schedule.');
    }

    public function edit(ScheduleGroup $scheduleGroup)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedules = \App\Models\Schedule::where('is_template', true)->get();
        return view('admin.settings.schedule-groups.edit', compact('scheduleGroup', 'days', 'schedules'));
    }

    public function update(Request $request, ScheduleGroup $scheduleGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $scheduleGroup->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.settings.schedule-groups.index')
            ->with('success', 'Master Blueprint name updated successfully.');
    }

    public function plot(ScheduleGroup $scheduleGroup)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedules = \App\Models\Shift::where('is_active', true)->get();
        return view('admin.settings.schedule-groups.plot', compact('scheduleGroup', 'days', 'schedules'));
    }

    public function updatePlot(Request $request, ScheduleGroup $scheduleGroup)
    {
        $validated = $request->validate([
            'schedule' => 'required|array'
        ]);

        $scheduleGroup->update([
            'schedule_config' => $validated['schedule']
        ]);

        return redirect()->route('admin.settings.schedule-groups.index')
            ->with('success', 'Schedule pattern updated successfully.');
    }

    public function members(ScheduleGroup $scheduleGroup)
    {
        // Get employees directly assigned to this group
        $employees = \App\Models\Employee::where('schedule_group_id', $scheduleGroup->id)
            ->with('site')
            ->get();
        
        // For the "Add Employee" modal
        $allSites = Site::all();
        $unassignedEmployees = \App\Models\Employee::where('schedule_group_id', '!=', $scheduleGroup->id)
            ->orWhereNull('schedule_group_id')
            ->get();
        
        return view('admin.settings.schedule-groups.members', compact('scheduleGroup', 'employees', 'allSites', 'unassignedEmployees'));
    }

    public function addMember(Request $request, ScheduleGroup $scheduleGroup)
    {
        $validated = $request->validate([
            'site_id' => 'nullable|exists:sites,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);
        
        // Update site if provided
        if ($validated['site_id']) {
            $employee->site_id = $validated['site_id'];
        }
        
        // Assign the schedule group directly to the employee
        $employee->schedule_group_id = $scheduleGroup->id;
        $employee->save();

        return back()->with('success', "{$employee->full_name} successfully added to this schedule group.");
    }

    public function removeMember(ScheduleGroup $scheduleGroup, \App\Models\Employee $employee)
    {
        $employee->schedule_group_id = null;
        $employee->save();

        return back()->with('success', "{$employee->full_name} removed from this schedule group.");
    }

    public function toggleStatus(ScheduleGroup $scheduleGroup)
    {
        $scheduleGroup->update([
            'status' => $scheduleGroup->status === 'Active' ? 'Inactive' : 'Active'
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(ScheduleGroup $scheduleGroup)
    {
        $scheduleGroup->delete();
        return redirect()->route('admin.settings.schedule-groups.index')
            ->with('success', 'Schedule Group deleted successfully.');
    }
}
