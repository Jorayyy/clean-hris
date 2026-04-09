<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dtr;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\AuditLog;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class DtrController extends Controller
{
    public function index(Request $request)
    {
        $query = Dtr::with('employee');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('start_date', $request->start_date)
                  ->whereDate('end_date', $request->end_date);
        }

        $dtrs = $query->latest()->paginate(20);
        
        $periods = Dtr::select('start_date', 'end_date')
            ->distinct()
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admin.dtrs.index', compact('dtrs', 'periods'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        $groups = \App\Models\PayrollGroup::all();
        return view('admin.dtrs.create', compact('employees', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:single,group',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'employee_id' => 'required_if:mode,single',
            'payroll_group_id' => 'required_if:mode,group',
        ]);

        $employeeIds = [];
        if ($request->mode == 'single') {
            $employeeIds[] = $request->employee_id;
        } else {
            $employeeIds = Employee::where('payroll_group_id', $request->payroll_group_id)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        }

        if (empty($employeeIds)) {
            return back()->with('error', 'No active employees found for the selection.');
        }

        $skippedCount = 0;
        $generatedCount = 0;

        foreach ($employeeIds as $empId) {
            // Check if a finalized DTR already exists for this period
            $exists = Dtr::where('employee_id', $empId)
                ->where('start_date', $request->start_date)
                ->where('end_date', $request->end_date)
                ->where('status', 'finalized')
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            $attendances = Attendance::where('employee_id', $empId)
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->get();

            Dtr::updateOrCreate(
                [
                    'employee_id' => $empId,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
                [
                    'total_late_minutes' => $attendances->sum('late_minutes'),
                    'total_undertime_minutes' => $attendances->sum('undertime_minutes'),
                    'total_overtime_hours' => 0,
                    'total_regular_hours' => $attendances->count() * 8,
                    'status' => 'draft',
                ]
            );
            $generatedCount++;
        }

        $message = $generatedCount . ' DTR(s) generated successfully.';
        if ($skippedCount > 0) {
            $message .= " (" . $skippedCount . " skipped because they are already finalized)";
        }

        return redirect()->route('admin.dtrs.index')->with('success', $message);
    }

    public function show(Dtr $dtr)
    {
        $attendances = Attendance::where('employee_id', $dtr->employee_id)
            ->whereBetween('date', [$dtr->start_date, $dtr->end_date])
            ->get();
            
        return view('admin.dtrs.show', compact('dtr', 'attendances'));
    }

    public function verify(Request $request, Dtr $dtr)
    {
        $user = Auth::user();
        $targetPassword = $user->dtr_password;

        $request->validate([
            'admin_password' => 'required'
        ]);

        $inputPassword = $request->admin_password;
        
        // Check either the primary login password OR the specialized DTR password
        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid security password. Verification failed.');
        }

        $dtr->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);
        return back()->with('success', 'DTR Verified successfully.');
    }

    public function finalize(Request $request, Dtr $dtr)
    {
        $user = Auth::user();
        $targetPassword = $user->dtr_password;

        $request->validate([
            'admin_password' => 'required'
        ]);

        $inputPassword = $request->admin_password;
        
        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid security password. Finalization failed.');
        }

        $dtr->update([
            'status' => 'finalized',
            'finalized_by' => Auth::id(),
            'finalized_at' => now(),
        ]);
        return back()->with('success', 'DTR Finalized and locked for payroll.');
    }

    public function update(Request $request, Dtr $dtr)
    {
        $user = Auth::user();
        $targetPassword = $user->dtr_password;

        $request->validate([
            'admin_password' => 'required',
            'total_regular_hours' => 'required|numeric|min:0',
            'total_late_minutes' => 'required|numeric|min:0',
            'total_undertime_minutes' => 'required|numeric|min:0',
            'total_overtime_hours' => 'required|numeric|min:0',
            'admin_notes' => 'nullable|string'
        ]);

        $inputPassword = $request->admin_password;
        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid password. Update unauthorized.');
        }

        if ($dtr->status === 'finalized' && $user->role !== 'admin') {
            return back()->with('error', 'Cannot edit a finalized DTR record.');
        }

        $oldData = $dtr->toArray();
        $dtr->update($request->only([
            'total_regular_hours', 'total_late_minutes', 'total_undertime_minutes', 'total_overtime_hours', 'admin_notes'
        ]));

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'DTR_MANUAL_UPDATE',
            'model_type' => Dtr::class,
            'model_id' => $dtr->id,
            'details' => [
                'employee' => $dtr->employee->full_name,
                'period' => $dtr->start_date->format('Y-m-d') . ' to ' . $dtr->end_date->format('Y-m-d'),
                'old_values' => $oldData,
                'new_values' => $dtr->toArray(),
                'ip' => $request->ip()
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return back()->with('success', 'DTR record updated successfully and activity logged.');
    }

    public function destroy(Request $request, Dtr $dtr)
    {
        $user = Auth::user();
        $targetPassword = $user->dtr_password;

        $request->validate([
            'admin_password' => 'required'
        ]);

        $inputPassword = $request->admin_password;
        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid password. Deletion unauthorized.');
        }

        if ($dtr->status == 'finalized' && $user->role !== 'admin') {
            return back()->with('error', 'Only top-level admins can delete finalized DTR records.');
        }

        // Log the activity before deleting
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DTR_DELETION',
            'model_type' => Dtr::class,
            'model_id' => $dtr->id,
            'details' => [
                'employee' => $dtr->employee->full_name,
                'period' => $dtr->start_date->format('Y-m-d') . ' to ' . $dtr->end_date->format('Y-m-d'),
                'status_at_deletion' => $dtr->status,
                'reason' => 'Administrative Deletion',
                'ip' => $request->ip()
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $dtr->delete();
        return back()->with('success', 'DTR record deleted and activity logged.');
    }
}
