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
        $groups = PayrollGroup::all();
        return view('payroll.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payroll_code' => 'required|unique:payrolls',
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
