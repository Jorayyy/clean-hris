<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Schedule;
use Illuminate\Http\Request;

class SiteAccountController extends Controller
{
    public function index()
    {
        $sites = Site::all();
        $schedules = Schedule::all(); // Assuming these are fixed schedules available for selection
        return view('admin.settings.sites.index', compact('sites', 'schedules'));
    }

    public function show(Site $site)
    {
        $schedules = Schedule::all();
        return view('admin.settings.sites.show', compact('site', 'schedules'));
    }

    public function updateSchedule(Request $request, Site $site)
    {
        $site->update([
            'schedule_config' => $request->schedule,
            'is_special_1_hour' => $request->has('is_special_1_hour'),
            'is_present_policy' => $request->has('is_present_policy'),
        ]);

        return redirect()->back()->with('success', 'Account schedule policy updated successfully.');
    }
}
