<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollItem;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeId = Auth::user()->employee_id;
        if (!$employeeId) {
            return back()->with('error', 'User not linked to an employee profile.');
        }

        $salaries = PayrollItem::with('payroll')
            ->where('employee_id', $employeeId)
            ->latest()
            ->get();

        return view('employee.dashboard', compact('salaries'));
    }

    public function showPayslip($id)
    {
        $employeeId = Auth::user()->employee_id;
        $salary = PayrollItem::with(['payroll', 'employee'])
            ->where('id', $id)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        return view('payroll.payslip', compact('salary'));
    }
}
