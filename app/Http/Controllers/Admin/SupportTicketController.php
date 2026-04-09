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
        return view('admin.tickets.show', compact('ticket'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,ongoing,resolved,closed',
            'admin_reply' => 'nullable|string'
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'status' => $request->status,
            'admin_reply' => $request->admin_reply
        ]);

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket updated successfully!');
    }
}
