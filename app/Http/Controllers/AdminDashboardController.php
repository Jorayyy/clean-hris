<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SupportTicket;
use App\Models\Holiday;
use App\Models\PayrollGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Stats Cards
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalAttendanceToday = Attendance::whereDate('date', Carbon::today())->count();
        $pendingTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
        $totalPayrollDisbursed = PayrollItem::sum('net_pay');

        // Recent Activity / Critical Tasks
        $pendingDtrs = \App\Models\Dtr::where('status', 'pending')->count();
        $unprocessedPayrolls = Payroll::where('status', 'draft')->count();

        // Chart Data: Attendance & Payroll Trends
        $attendanceLabels = [];
        $attendanceCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $attendanceLabels[] = $date->format('M d');
            $attendanceCounts[] = Attendance::whereDate('date', $date)->count();
        }

        // Upcoming Events
        $upcomingHolidays = Holiday::where('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->limit(3)
            ->get();

        $currentYear = (int)date('Y');
        $upcomingBirthdays = Employee::where('status', 'active')
            ->whereNotNull('birthday')
            ->get()
            ->filter(function($emp) use ($currentYear) {
                try {
                    $rawDate = Carbon::parse($emp->birthday);
                    // Avoid setter methods entirely to bypass strict type-checks on __call or setUnit
                    $bday = Carbon::createFromDate($currentYear, (int)$rawDate->format('m'), (int)$rawDate->format('d'));
                    
                    if ($bday->isPast() && !$bday->isToday()) {
                        $bday->addYear();
                    }
                    return $bday->diffInDays(Carbon::today()) <= 30;
                } catch (\Exception $e) { return false; }
            })
            ->take(5);

        // Recent Batches and Tickets
        $recentPayrolls = Payroll::with('payrollGroup')->latest()->paginate(5, ['*'], 'payroll_page');
        $recentTickets = SupportTicket::with('employee')->latest()->take(5)->get();

        // Group Distribution
        $groups = PayrollGroup::withCount('employees')->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalAttendanceToday',
            'pendingTickets',
            'totalPayrollDisbursed',
            'attendanceLabels',
            'attendanceCounts',
            'recentPayrolls',
            'recentTickets',
            'groups',
            'upcomingHolidays',
            'upcomingBirthdays',
            'pendingDtrs',
            'unprocessedPayrolls'
        ));
    }
}