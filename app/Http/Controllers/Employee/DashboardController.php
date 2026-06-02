<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollItem;
use App\Models\Announcement;
use App\Models\LeaveBalance;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->employee_id) {
            // Check if there's an employee record with the same email
            $employee = \App\Models\Employee::where('email', $user->email)->first();
            if ($employee) {
                $user->update(['employee_id' => $employee->id]);
            }
        }

        $employee = \App\Models\Employee::where('id', $user->employee_id)->first();
        
        if (!$employee) {
            if ($user->isAdmin() && !$request->routeIs('admin.dashboard')) {
                return redirect()->route('admin.dashboard')->with('info', 'You are on the employee dashboard but do not have an employee profile linked. Redirected to Admin Dashboard.');
            }
            
            if (!$user->isAdmin()) {
                Auth::logout();
                return redirect('/login')->with('error', 'User not linked to an employee profile. Please contact human resources.');
            }

            abort(403, 'User not linked to an employee profile.');
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

        // New real data: Improved Night Shift detection for Dashboard display
        $now = Carbon::now();
        
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();
            
        $yesterdayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::yesterday())
            ->first();

        // Logic to determine which one is "Current"
        $currentAttendance = null;
        $displayDate = Carbon::today();

        // If it's early morning, be very smart about identifying the active session
        if ($now->hour < 9) {
            // Case 1: Yesterday's shift is explicitly open
            if ($yesterdayAttendance && (!$yesterdayAttendance->time_out || $yesterdayAttendance->time_out == '00:00:00')) {
                $currentAttendance = $yesterdayAttendance;
                $displayDate = Carbon::yesterday();
            } 
            // Case 2: Today has a punch, but is it a morning punch for yesterday's shift?
            elseif ($todayAttendance && $todayAttendance->time_in && Carbon::parse($todayAttendance->time_in)->hour < 6) {
                // If today's rostered shift starts at night (e.g. >= 6 PM), then a 1 AM punch is definitely for yesterday
                $todaySched = $employee->getScheduleForDate(Carbon::today());
                if ($todaySched && Carbon::parse($todaySched->time_in)->hour >= 18) {
                    $currentAttendance = $todayAttendance;
                    $displayDate = Carbon::yesterday(); // Display the roster for yesterday
                } else {
                    $currentAttendance = $todayAttendance;
                    $displayDate = Carbon::today();
                }
            }
            // Case 3: No today punch, yesterday exists (maybe just finished)
            elseif (!$todayAttendance && $yesterdayAttendance) {
                $currentAttendance = $yesterdayAttendance;
                $displayDate = Carbon::yesterday();
            } else {
                $currentAttendance = $todayAttendance;
                $displayDate = Carbon::today();
            }
        } else {
            // Later in the day, today is more likely
            $currentAttendance = $todayAttendance;
            $displayDate = Carbon::today();
        }

        // Final decision: if we have a currentAttendance and we haven't set displayDate yet
        // we use its date for the roster
        if ($currentAttendance && !isset($displayDate)) {
            $displayDate = Carbon::parse($currentAttendance->date);
        }

        $displaySchedule = $employee->getScheduleForDate($displayDate);
        $todayAttendance = $currentAttendance; // For compact compatibility

        $recentAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', '<=', Carbon::today())
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Real Announcements
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->limit(5)
            ->get();

        // Real Leave Balances
        $leaveBalance = LeaveBalance::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'sick_leave_total' => 10, 'sick_leave_used' => 0,
                'vacation_leave_total' => 12, 'vacation_leave_used' => 0,
                'sil_total' => 5, 'sil_used' => 0
            ]
        );

        return view('employee.dashboard', compact(
            'salaries', 
            'totalHoursThisMonth', 
            'pendingTickets',
            'latestSalary',
            'todayAttendance',
            'recentAttendance',
            'announcements',
            'leaveBalance',
            'payrollPeriods',
            'employee',
            'displaySchedule',
            'displayDate'
        ));
    }

    public function showPayslip($id)
    {
        $item = PayrollItem::with(['payroll', 'employee'])->findOrFail($id);
        
        // Authorization Check via Policy
        if (Auth::user()->cannot('view', $item)) {
            abort(403, 'Unauthorized access to this payslip.');
        }

        return view('payslip.show', compact('item'));
    }
}
