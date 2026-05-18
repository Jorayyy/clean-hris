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
        $groups = ScheduleGroup::withCount('sites')->get();
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
            'description' => 'nullable|string',
            'schedule' => 'required|array'
        ]);

        ScheduleGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'schedule_config' => $validated['schedule']
        ]);

        return redirect()->route('admin.settings.sites.index')
            ->with('success', 'Schedule Group created successfully.');
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
            'description' => 'nullable|string',
            'schedule' => 'required|array'
        ]);

        $scheduleGroup->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'schedule_config' => $validated['schedule']
        ]);

        return redirect()->route('admin.settings.sites.index')
            ->with('success', 'Schedule Group updated successfully.');
    }

    public function destroy(ScheduleGroup $scheduleGroup)
    {
        $scheduleGroup->delete();
        return redirect()->route('admin.settings.schedule-groups.index')
            ->with('success', 'Schedule Group deleted successfully.');
    }
}
