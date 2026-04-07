<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dtr;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
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

    public function verify(Dtr $dtr)
    {
        $dtr->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);
        return back()->with('success', 'DTR Verified.');
    }

    public function finalize(Dtr $dtr)
    {
        $dtr->update([
            'status' => 'finalized',
            'finalized_by' => Auth::id(),
            'finalized_at' => now(),
        ]);
        return back()->with('success', 'DTR Finalized and locked for payroll.');
    }

    public function destroy(Dtr $dtr)
    {
        if ($dtr->status == 'finalized') {
            return back()->with('error', 'Cannot delete a finalized DTR record.');
        }

        $dtr->delete();
        return back()->with('success', 'DTR record deleted.');
    }
}
