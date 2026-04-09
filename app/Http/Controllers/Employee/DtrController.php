<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtrController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Find employee by the primary key 'id' stored as employee_id in the users table
        $employee = \App\Models\Employee::find($user->employee_id);

        if (!$employee) {
            return back()->with('error', 'Employee record not found for ID: ' . ($user->employee_id ?? 'None'));
        }

        $dtrs = Dtr::where('employee_id', $employee->id)
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('employee.dtr.index', compact('dtrs'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::find($user->employee_id);

        if (!$employee) {
            abort(404, 'Employee record not found.');
        }

        $dtr = Dtr::with('employee')->findOrFail($id);

        // Security check
        if ($dtr->employee_id !== $employee->id) {
            abort(403, 'Unauthorized access to DTR record.');
        }

        $attendances = Attendance::where('employee_id', $dtr->employee_id)
            ->whereBetween('date', [$dtr->start_date->format('Y-m-d'), $dtr->end_date->format('Y-m-d')])
            ->get();

        return view('employee.dtr.show', compact('dtr', 'attendances'));
    }
}
