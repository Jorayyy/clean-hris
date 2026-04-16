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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PayrollController extends Controller
{
    use AuthorizesRequests;

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

    public function getFinalizedDtrs(Request $request)
    {
        $groupId = $request->get('payroll_group_id');
        if (!$groupId) return response()->json([]);

        // Get all finalized DTRs for employees in this group
        // Group by start_date and end_date so we can offer them as choices
        $periods = \App\Models\Dtr::where('status', 'finalized')
            ->whereHas('employee', function($q) use ($groupId) {
                $q->where('payroll_group_id', $groupId);
            })
            ->select('start_date', 'end_date')
            ->distinct()
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($dtr) {
                return [
                    'start_date' => $dtr->start_date->format('Y-m-d'),
                    'end_date' => $dtr->end_date->format('Y-m-d'),
                    'label' => $dtr->start_date->format('M d, Y') . ' to ' . $dtr->end_date->format('M d, Y')
                ];
            });

        return response()->json($periods);
    }

    public function store(StorePayrollRequest $request)
    {
        Payroll::create($request->validated());
        return redirect()->route('payroll.index')->with('success', 'Payroll draft created.');
    }

    public function show(Payroll $payroll)
    {
        $items = $payroll->items()->with('employee')->get();
        $item_count = $items->count();
        return view('payroll.show', compact('payroll', 'items', 'item_count'));
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

        return redirect()->route('payroll.index')->with('success', 'Payroll period updated successfully.');
    }

    public function approve(Request $request, Payroll $payroll)
    {
        // Safety check: ensure all employees are accounted for
        $total_employees = \App\Models\Employee::where('payroll_group_id', $payroll->payroll_group_id)->where('status', 'active')->count();
        $current_items = $payroll->items()->count();

        if ($current_items < $total_employees) {
            return back()->with('error', 'Cannot finalize. There are still ' . ($total_employees - $current_items) . ' employees missing payslips.');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return redirect()->route('payroll.index')->with('success', 'Payroll period APPROVED. All payslips are now finalized.');
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
