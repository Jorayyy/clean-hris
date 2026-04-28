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
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DtrController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view attendance', only: ['index', 'show']),
            new Middleware('can:create attendance', only: ['create', 'store', 'batchAuthorize']),
            new Middleware('can:edit attendance', only: ['update', 'verify', 'finalize', 'batchVerify', 'batchFinalize']),
            new Middleware('can:delete attendance', only: ['destroy', 'batchDestroy']),
        ];
    }

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
                ->whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->end_date)
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
                    'total_overtime_hours' => $attendances->sum('overtime_hours'),
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
            ->whereDate('date', '>=', $dtr->start_date)
            ->whereDate('date', '<=', $dtr->end_date)
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

    public function batchVerify(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:dtrs,id',
            'admin_password' => 'required'
        ]);

        $user = Auth::user();
        $targetPassword = $user->dtr_password;
        $inputPassword = $request->admin_password;

        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid security password. Batch verification failed.');
        }

        $count = Dtr::whereIn('id', $request->ids)
            ->where('status', 'draft')
            ->update([
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

        return back()->with('success', $count . ' DTR record(s) verified successfully.');
    }

    public function batchFinalize(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:dtrs,id',
            'admin_password' => 'required'
        ]);

        $user = Auth::user();
        $targetPassword = $user->dtr_password;
        $inputPassword = $request->admin_password;

        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid security password. Batch finalization failed.');
        }

        $count = Dtr::whereIn('id', $request->ids)
            ->where('status', 'verified')
            ->update([
                'status' => 'finalized',
                'finalized_by' => Auth::id(),
                'finalized_at' => now(),
            ]);

        return back()->with('success', $count . ' DTR record(s) finalized and locked.');
    }

    public function batchDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:dtrs,id',
            'admin_password' => 'required'
        ]);

        $user = Auth::user();
        $targetPassword = $user->dtr_password;
        $inputPassword = $request->admin_password;

        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid security password. Batch deletion failed.');
        }

        // Logic check: Non-admins cannot delete finalized records
        $query = Dtr::whereIn('id', $request->ids);
        if ($user->role !== 'admin') {
            $query->where('status', '!=', 'finalized');
        }

        $count = $query->delete();

        return back()->with('success', $count . ' DTR record(s) deleted successfully.');
    }

    public function batchAuthorize(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:dtrs,id',
            'action' => 'required|string'
        ]);

        $ids = $request->ids;
        $action = $request->action;

        $update = [];
        switch ($action) {
            case 'authorize_all':
                $update = [
                    'is_ot_authorized' => true,
                    'is_nd_authorized' => true,
                    'is_holiday_authorized' => true
                ];
                break;
            case 'authorize_ot':
                $update = ['is_ot_authorized' => true];
                break;
            case 'authorize_nd':
                $update = ['is_nd_authorized' => true];
                break;
            case 'authorize_holiday':
                $update = ['is_holiday_authorized' => true];
                break;
            case 'unauthorize_all':
                $update = [
                    'is_ot_authorized' => false,
                    'is_nd_authorized' => false,
                    'is_holiday_authorized' => false
                ];
                break;
        }

        if (empty($update)) {
            return back()->with('error', 'Invalid batch action.');
        }

        // Only allow updating non-finalized records OR allow if admin
        $query = Dtr::whereIn('id', $ids);
        if (Auth::user()->role !== 'admin') {
            $query->where('status', '!=', 'finalized');
        }

        $count = $query->update($update);

        return back()->with('success', "$count DTR record(s) updated successfully.");
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
            'total_night_diff_hours' => 'nullable|numeric|min:0',
            'total_holiday_hours' => 'nullable|numeric|min:0',
            'incentives' => 'nullable|numeric|min:0',
            'is_ot_authorized' => 'nullable|boolean',
            'is_nd_authorized' => 'nullable|boolean',
            'is_holiday_authorized' => 'nullable|boolean',
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

        $wasOtAuthorized = $dtr->is_ot_authorized;
        $isOtAuthorized = $request->has('is_ot_authorized');

        $updateData = $request->only([
            'total_regular_hours', 'total_late_minutes', 'total_undertime_minutes', 
            'total_overtime_hours', 'total_night_diff_hours', 'total_holiday_hours', 
            'incentives', 'admin_notes'
        ]);
        $updateData['is_ot_authorized'] = $isOtAuthorized;
        $updateData['is_nd_authorized'] = $request->has('is_nd_authorized');
        $updateData['is_holiday_authorized'] = $request->has('is_holiday_authorized');

        if (!$wasOtAuthorized && $isOtAuthorized) {
            $updateData['ot_authorized_by'] = Auth::id();
        } elseif (!$isOtAuthorized) {
            $updateData['ot_authorized_by'] = null;
        }

        $oldData = $dtr->toArray();
        $dtr->update($updateData);

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
