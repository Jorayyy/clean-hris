<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class PayrollSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::first() ?: (object)[
            'sss_rate' => 0.0450,
            'pagibig_rate' => 0.0200,
            'philhealth_rate' => 0.0500,
            'late_rate' => 1.0000,
            'undertime_rate' => 1.0000,
        ];
        return view('admin.settings.payroll', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = AppSetting::firstOrCreate([]);
        
        $request->validate([
            'sss_rate' => 'required|numeric|between:0,1',
            'pagibig_rate' => 'required|numeric|between:0,1',
            'philhealth_rate' => 'required|numeric|between:0,1',
            'late_rate' => 'required|numeric|min:0',
            'undertime_rate' => 'required|numeric|min:0',
        ]);

        $settings->update([
            'sss_rate' => $request->sss_rate,
            'pagibig_rate' => $request->pagibig_rate,
            'philhealth_rate' => $request->philhealth_rate,
            'late_rate' => $request->late_rate,
            'undertime_rate' => $request->undertime_rate,
        ]);

        return redirect()->back()->with('success', 'Payroll settings updated successfully.');
    }
}
