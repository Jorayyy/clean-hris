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
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('id', $user->employee_id)->first();
        
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

        // Get all unique payroll periods for the filter
        $payrollPeriods = \App\Models\Payroll::whereHas('items', function($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->latest()
            ->get();

        $query = PayrollItem::with('payroll')
            ->where('employee_id', $employee->id);

        if ($request->filled('payroll_id')) {
            $query->where('payroll_id', $request->payroll_id);
        }

        $latestSalary = (clone $query)->latest()->first();
        $salaries = $query->latest()->get();

        return view('employee.dashboard', compact('salaries', 'totalHoursThisMonth', 'pendingTickets', 'latestSalary', 'payrollPeriods'));
    }

    public function showPayslip($id)
    {
        $employeeId = Auth::user()->employee_id;
        $item = PayrollItem::with(['payroll', 'employee'])
            ->where('id', $id)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        return view('payslip.show', compact('item'));
    }
}
