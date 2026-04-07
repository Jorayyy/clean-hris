<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('employee_id', Auth::user()->employee_id)
            ->latest()
            ->paginate(10);
            
        return view('employee.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('employee.tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,normal,high'
        ]);

        SupportTicket::create([
            'employee_id' => Auth::user()->employee_id,
            'type' => $request->type,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'pending'
        ]);

        return redirect()->route('employee.tickets.index')->with('success', 'Support concern submitted successfully!');
    }

    public function show($id)
    {
        $ticket = SupportTicket::where('id', $id)
            ->where('employee_id', Auth::user()->employee_id)
            ->firstOrFail();

        return view('employee.tickets.show', compact('ticket'));
    }
}
