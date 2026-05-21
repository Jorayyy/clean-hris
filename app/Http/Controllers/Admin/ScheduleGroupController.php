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
        return view('admin.settings.schedule-groups.create', compact('days', 'schedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = ScheduleGroup::create([
            'name' => $validated['name'],
            'created_by' => auth()->id(),
            'schedule_config' => [] // Initialize empty
        ]);

        return redirect()->route('admin.settings.schedule-groups.index')
            ->with('success', 'Master Blueprint created successfully.');
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
        // Get all employees from sites that use this group
        $siteIds = $scheduleGroup->sites->pluck('id');
        $employees = \App\Models\Employee::whereIn('site_id', $siteIds)->with('site')->get();
        
        // For the "Add Employee" modal
        $allSites = Site::all();
        $unassignedEmployees = \App\Models\Employee::whereNotIn('site_id', $siteIds)
            ->orWhereNull('site_id')
            ->get();
        
        return view('admin.settings.schedule-groups.members', compact('scheduleGroup', 'employees', 'allSites', 'unassignedEmployees'));
    }

    public function addMember(Request $request, ScheduleGroup $scheduleGroup)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);
        $employee->site_id = $validated['site_id'];
        $employee->save();

        // Ensure the site is linked to this schedule group
        $site = Site::find($validated['site_id']);
        if ($site->schedule_group_id !== $scheduleGroup->id) {
            $site->schedule_group_id = $scheduleGroup->id;
            $site->save();
        }

        return back()->with('success', "{$employee->full_name} successfully assigned to {$site->name} under this group.");
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
