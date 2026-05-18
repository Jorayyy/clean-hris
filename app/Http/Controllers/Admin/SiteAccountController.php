<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Schedule;
use App\Models\ScheduleGroup;
use Illuminate\Http\Request;

class SiteAccountController extends Controller
{
    public function index()
    {
        $sites = Site::with('scheduleGroup')->get();
        return view('admin.settings.sites.index', compact('sites'));
    }

    public function show(Site $site)
    {
        $scheduleGroups = ScheduleGroup::all();
        $templates = \App\Models\Schedule::where('is_template', true)->get();
        return view('admin.settings.sites.show', compact('site', 'scheduleGroups', 'templates'));
    }

    public function updateSchedule(Request $request, Site $site)
    {
        $site->update([
            'schedule_config' => $request->schedule,
            'schedule_group_id' => $request->schedule_group_id,
            'is_special_1_hour' => $request->has('is_special_1_hour'),
            'is_present_policy' => $request->has('is_present_policy'),
        ]);

        return redirect()->back()->with('success', 'Account schedule policy updated successfully.');
    }
}
