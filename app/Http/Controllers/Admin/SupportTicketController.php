<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\AuditLog;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        if ($status === 'audit') {
            $logs = AuditLog::with('user')->latest()->paginate(15);
            return view('admin.tickets.index', compact('logs', 'status'));
        }

        $tickets = SupportTicket::with('employee')
            ->where('status', $status)
            ->latest()
            ->paginate(15);

        return view('admin.tickets.index', compact('tickets', 'status'));
    }

    public function show($id)
    {
        $ticket = SupportTicket::with('employee')->findOrFail($id);
        
        $currentAttendance = null;
        if ($ticket->type === 'DTR Correction' && $ticket->correction_date) {
            $currentAttendance = \App\Models\Attendance::where('employee_id', $ticket->employee_id)
                ->where('date', $ticket->correction_date)
                ->first();
        }

        return view('admin.tickets.show', compact('ticket', 'currentAttendance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,ongoing,resolved,closed',
            'admin_reply' => 'nullable|string'
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $oldStatus = $ticket->status;

        $ticket->update([
            'status' => $request->status,
            'admin_reply' => $request->admin_reply
        ]);

        // Process DTR Correction if newly resolved
        if ($ticket->status === 'resolved' && $oldStatus !== 'resolved' && $ticket->type === 'DTR Correction') {
            $this->processDTRCorrection($ticket);
        }

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket updated successfully!');
    }

    protected function processDTRCorrection($ticket)
    {
        if (!$ticket->correction_date || !$ticket->correction_time_in || !$ticket->correction_time_out) {
            return;
        }

        // Convert full datetime to just H:i:s as expected by calculateAttendanceStats
        $timeInArr = \Carbon\Carbon::parse($ticket->correction_time_in);
        $timeOutArr = \Carbon\Carbon::parse($ticket->correction_time_out);
        
        $timeInStr = $timeInArr->format('H:i:s');
        $timeOutStr = $timeOutArr->format('H:i:s');

        // We assume an approved TK Complaint means any resulting OT is authorized
        $attendance = \App\Models\Attendance::updateOrCreate(
            [
                'employee_id' => $ticket->employee_id,
                'date' => $ticket->correction_date
            ],
            [
                'ot_authorized' => true 
            ]
        );

        $payrollService = new \App\Services\PayrollService();
        $stats = $payrollService->calculateAttendanceStats(
            $timeInStr, 
            $timeOutStr, 
            $ticket->employee_id, 
            $ticket->correction_date
        );

        $attendance->update([
            'time_in' => $timeInStr,
            'time_out' => $timeOutStr,
            'total_hours' => $stats['total_hours'],
            'late_minutes' => $stats['late_minutes'],
            'undertime_minutes' => $stats['undertime_minutes'],
            'overtime_hours' => $stats['overtime_hours'],
        ]);
    }
}
