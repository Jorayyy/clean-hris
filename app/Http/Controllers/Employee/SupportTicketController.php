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

    public function tkCreate()
    {
        return view('employee.tickets.tk_create');
    }

    public function store(Request $request)
    {
        // Custom logic for TK Complaint or standard ticket
        if ($request->has('covered_date')) {
            $time_in = $request->time_in_hh . ':' . $request->time_in_mm;
            $time_out = $request->time_out_hh . ':' . $request->time_out_mm;
            
            $extended_desc = "Covered Date: " . $request->covered_date . "\n" .
                             "Time IN: " . $time_in . " (" . $request->time_in_date . ")\n" .
                             "Time OUT: " . $time_out . " (" . $request->time_out_date . ")\n" .
                             "Reason: " . $request->description;
                             
            SupportTicket::create([
                'employee_id' => Auth::user()->employee_id,
                'type' => 'DTR Correction',
                'subject' => 'TK Complaint - ' . $request->covered_date,
                'description' => $extended_desc,
                'priority' => 'normal',
                'status' => 'pending'
            ]);
        } else {
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
        }

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
