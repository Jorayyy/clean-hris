<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Services\PayrollService;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePayrollRequest;
use App\Jobs\ProcessPayrollBatch;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $service)
    {
        $this->payrollService = $service;
    }

    public function index(Request $request)
    {
        $query = Payroll::with('payrollGroup')->latest();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where('start_date', $request->start_date)
                  ->where('end_date', $request->end_date);
        }

        $payrolls = $query->get();

        $periods = Payroll::select('start_date', 'end_date')
            ->distinct()
            ->orderBy('start_date', 'desc')
            ->get();

        return view('payroll.index', compact('payrolls', 'periods'));
    }

    public function create()
    {
        $groups = PayrollGroup::withCount('employees')->get();
        return view('payroll.create', compact('groups'));
    }

    public function store(StorePayrollRequest $request)
    {
        Payroll::create($request->validated());
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

    public function processPayroll(Request $request, Payroll $payroll)
    {
        $user = Auth::user();
        $targetPassword = $user->dtr_password;

        $request->validate([
            'admin_password' => 'required'
        ]);

        $inputPassword = $request->admin_password;
        $isAuthPassword = Hash::check($inputPassword, $user->password);
        $isDtrPassword = $targetPassword && ($inputPassword === $targetPassword);

        if (!$isAuthPassword && !$isDtrPassword) {
            return back()->with('error', 'Invalid security password. Payroll processing aborted.');
        }

        // Dispatch background job for heavy processing
        ProcessPayrollBatch::dispatch($payroll);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'PAYROLL_PROCESSED',
            'model_type' => Payroll::class,
            'model_id' => $payroll->id,
            'details' => [
                'batch' => $payroll->payroll_code,
                'period' => $payroll->start_date . ' to ' . $payroll->end_date,
                'ip' => $request->ip()
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('payroll.show', $payroll->id)->with('success', 'Payroll calculation has been queued. Please refresh in a few moments to see the results.');
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
