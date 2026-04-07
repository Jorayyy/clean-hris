<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::first() ?: (object)['app_name' => 'HRIS Payroll', 'app_logo' => null];
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = AppSetting::first();
        
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = [
            'app_name' => $request->app_name,
        ];

        if ($request->hasFile('app_logo')) {
            // Delete old logo if exists
            if ($settings->app_logo) {
                Storage::disk('public')->delete($settings->app_logo);
            }
            $logoPath = $request->file('app_logo')->store('logos', 'public');
            $data['app_logo'] = $logoPath;
        }

        $settings->update($data);
        
        // Clear cache so changes reflect immediately
        Cache::forget('system_settings');

        return back()->with('success', 'System settings updated successfully.');
    }
}
