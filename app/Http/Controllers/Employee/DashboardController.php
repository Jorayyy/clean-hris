<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollItem;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('employee_id', $user->employee_id)->first();
        
        if (!$employee) {
            return back()->with('error', 'User not linked to an employee profile.');
        }

        // Stats
        $totalHoursThisMonth = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('total_hours');
            
        $pendingTickets = SupportTicket::where('employee_id', $employee->id)
            ->where('status', '!=', 'resolved')
            ->count();

        $latestSalary = PayrollItem::with('payroll')
            ->where('employee_id', $employee->id)
            ->latest()
            ->first();

        $salaries = PayrollItem::with('payroll')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return view('employee.dashboard', compact('salaries', 'totalHoursThisMonth', 'pendingTickets', 'latestSalary'));
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
