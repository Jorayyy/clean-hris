<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Stats Cards
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalAttendanceToday = Attendance::whereDate('date', Carbon::today())->count();
        $pendingTickets = SupportTicket::where('status', 'open')->count();
        $totalPayrollDisbursed = PayrollItem::sum('net_pay');

        // Attendance Chart Data (Last 7 Days)
        $attendanceLabels = [];
        $attendanceCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $attendanceLabels[] = $date->format('M d');
            $attendanceCounts[] = Attendance::whereDate('date', $date)->count();
        }

        // Recent Payroll Batches
        $recentPayrolls = Payroll::with('payrollGroup')->latest()->take(5)->get();

        // Recent Tickets
        $recentTickets = SupportTicket::with('employee')->latest()->take(5)->get();

        // Employee Distribution by Group
        $groupLabels = [];
        $groupCounts = [];
        $groups = \App\Models\PayrollGroup::withCount('employees')->get();
        foreach ($groups as $group) {
            $groupLabels[] = $group->name;
            $groupCounts[] = $group->employees_count;
        }

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalAttendanceToday',
            'pendingTickets',
            'totalPayrollDisbursed',
            'attendanceLabels',
            'attendanceCounts',
            'recentPayrolls',
            'recentTickets',
            'groupLabels',
            'groupCounts'
        ));
    }
}