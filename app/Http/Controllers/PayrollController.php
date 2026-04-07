<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $service)
    {
        $this->payrollService = $service;
    }

    public function index()
    {
        $payrolls = Payroll::with('payrollGroup')->latest()->get();
        return view('payroll.index', compact('payrolls'));
    }

    public function create()
    {
        $groups = PayrollGroup::withCount('employees')->get();
        return view('payroll.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payroll_code' => 'required|unique:payrolls',
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date',
        ]);

        Payroll::create($request->all());
        return redirect()->route('payroll.index')->with('success', 'Payroll draft created.');
    }

    public function show(Payroll $payroll)
    {
        $items = $payroll->items()->with('employee')->get();
        return view('payroll.show', compact('payroll', 'items'));
    }
    public function edit(Payroll $payroll)
    {
        // Allow editing regardless of status for flexibility, or you can keep this restricted.
        // If you want to allow changing coverage period even after process:
        // if ($payroll->status == 'processed') { ... } 
        
        $groups = PayrollGroup::withCount('employees')->get();
        return view('payroll.edit', compact('payroll', 'groups'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'payroll_code' => 'required|unique:payrolls,payroll_code,' . $payroll->id,
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date',
        ]);

        $payroll->update($request->all());

        // If it was already processed, the user might want to re-process it to catch the new dates
        if ($payroll->status == 'processed') {
            return redirect()->route('payroll.index')->with('success', 'Payroll period updated. Note: This period was already processed; you may need to re-run it to reflect date changes.');
        }

        return redirect()->route('payroll.index')->with('success', 'Payroll period updated successfully.');
    }

    public function processPayroll(Payroll $payroll)
    {
        $this->payrollService->computePayroll($payroll);
        return redirect()->route('payroll.show', $payroll->id)->with('success', 'Payroll processed successfully.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return redirect()->route('payroll.index');
    }

    public function generatePayslip($payrollItemId)
    {
        $item = PayrollItem::with('employee', 'payroll')->findOrFail($payrollItemId);
        return view('payslip.show', compact('item'));
    }
}
