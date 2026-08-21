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
        $settings = AppSetting::firstOrCreate([], [
            'app_name' => 'MEBS HIYAS',
            'sss_rate' => 0.0450,
            'pagibig_rate' => 0.0200,
            'philhealth_rate' => 0.0500,
            'late_rate' => 1.0000,
            'undertime_rate' => 1.0000,
        ]);
        
        // Settings page submits "_pct" percentage fields; convert to decimals.
        // Direct "rate" fields (legacy API/tests) remain raw decimals.
        foreach (['sss_rate', 'pagibig_rate', 'philhealth_rate', 'night_diff_rate'] as $rateField) {
            $pctField = $rateField . '_pct';
            if ($request->filled($pctField)) {
                $request->merge([$rateField => (float) str_replace(',', '', $request->input($pctField)) / 100]);
            }
        }

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dtr_edit_password' => 'nullable|string',
            'sss_rate' => 'required|numeric|between:0,1',
            'pagibig_rate' => 'required|numeric|between:0,1',
            'philhealth_rate' => 'required|numeric|between:0,1',
            'late_rate' => 'required|numeric|min:0',
            'undertime_rate' => 'required|numeric|min:0',
            'night_diff_rate' => 'nullable|numeric|between:0,1',
        ]);

        $data = [
            'app_name' => $request->app_name,
            'dtr_edit_password' => $request->dtr_edit_password,
            'sss_rate' => $request->sss_rate,
            'pagibig_rate' => $request->pagibig_rate,
            'philhealth_rate' => $request->philhealth_rate,
            'late_rate' => $request->late_rate,
            'undertime_rate' => $request->undertime_rate,
            'night_diff_rate' => $request->input('night_diff_rate', $settings->night_diff_rate ?? 0.10),
        ];

        if ($request->hasFile('app_logo')) {
            // Delete old logo if exists
            if ($settings->app_logo) {
                Storage::disk('public')->delete($settings->app_logo);
            }
            $logoPath = $request->file('app_logo')->store('logos', 'public');
            $data['app_logo'] = $logoPath;

            // Ensure the logo is copied to web-accessible folders for both Local and Hostinger
            try {
                $sourcePath = storage_path('app/public/' . $logoPath);
                $filename = basename($logoPath);
                
                // 1. Copy to root logos/ (for Hostinger)
                $rootLogos = base_path('logos');
                if (!file_exists($rootLogos)) mkdir($rootLogos, 0755, true);
                copy($sourcePath, $rootLogos . '/' . $filename);
                chmod($rootLogos . '/' . $filename, 0644);

                // 2. Copy to public/logos/ (for Local)
                $publicLogos = public_path('logos');
                if (!file_exists($publicLogos)) mkdir($publicLogos, 0755, true);
                copy($sourcePath, $publicLogos . '/' . $filename);
                chmod($publicLogos . '/' . $filename, 0644);
                
            } catch (\Exception $e) {
                // Log error or ignore if copying fails
                \Log::error("Failed to copy logo to root: " . $e->getMessage());
            }
        }

        $settings->update($data);
        
        // Clear cache so changes reflect immediately
        Cache::forget('system_settings');

        return back()->with('success', 'System settings updated successfully.');
    }
}
