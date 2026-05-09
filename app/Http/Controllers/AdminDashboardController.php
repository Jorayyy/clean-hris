<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SupportTicket;
use App\Models\Holiday;
use App\Models\PayrollGroup;
use App\Models\Position;
use App\Models\Classification;
use App\Models\Level;
use App\Models\Dtr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Stats Cards
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalAttendanceToday = Attendance::whereDate('date', Carbon::today())->count();
        $pendingTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
        $totalPayrollDisbursed = PayrollItem::sum('net_pay');

        // Distribution Data for Charts
        $classificationCounts = Employee::select(
                DB::raw('COALESCE(classification, "Unassigned") as classification'), 
                DB::raw('count(*) as total')
            )
            ->where('status', 'active')
            ->groupBy('classification')
            ->get();
            
        $positionCounts = Employee::select('position', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->groupBy('position')
            ->get();

        // High Overtime / Late Watchlist (Current Month)
        $startOfMonth = Carbon::now()->startOfMonth();
        $watchlist = Dtr::with('employee')
            ->where('start_date', '>=', $startOfMonth)
            ->select('employee_id', 
                DB::raw('SUM(total_late_minutes) as late_mins'),
                DB::raw('SUM(total_overtime_hours) as ot_hours'))
            ->groupBy('employee_id')
            ->having('late_mins', '>', 0)
            ->orHaving('ot_hours', '>', 0)
            ->orderBy('late_mins', 'desc')
            ->limit(5)
            ->get();

        // Recent Activity / Critical Tasks
        $pendingDtrs = Dtr::where('status', 'pending')->count();
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

        $groups = PayrollGroup::withCount('employees')->get();

        // Site Distribution
        $siteDistribution = Employee::select('sites.name as site_name', DB::raw('count(employees.id) as total'))
            ->join('sites', 'employees.site_id', '=', 'sites.id')
            ->where('employees.status', 'active')
            ->groupBy('sites.name')
            ->get();

        // Yield vs Overtime (Current Month)
        $yieldMetrics = Dtr::where('start_date', '>=', $startOfMonth)
            ->select(
                DB::raw('SUM(total_regular_hours) as reg_hours'),
                DB::raw('SUM(total_overtime_hours) as ot_hours')
            )
            ->first();

        $compactData = [
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
            'unprocessedPayrolls',
            'classificationCounts',
            'positionCounts',
            'watchlist',
            'siteDistribution',
            'yieldMetrics'
        ];

        if (auth()->user()->hasRole('Accounting Admin') || auth()->user()->isAdmin()) {
            return view('admin.dashboard_accounting', compact(...$compactData));
        }

        if (auth()->user()->hasRole('HR Admin')) {
            return view('admin.dashboard_hr', compact(...$compactData));
        }

        return view('admin.dashboard', compact(...$compactData));
    }
}