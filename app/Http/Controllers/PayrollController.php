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
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
        $query = Payroll::with(['payrollGroup', 'employee'])->latest();

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

    public function create(Request $request)
    {
        $groups = PayrollGroup::withCount('employees')->get();
        $employees = Employee::where('status', 'active')->get();

        $prefill = [
            'mode' => $request->input('mode', $request->filled('employee_id') ? 'single' : 'group'),
            'employee_id' => $request->input('employee_id'),
            'payroll_group_id' => $request->input('payroll_group_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        return view('payroll.create', compact('groups', 'employees', 'prefill'));
    }

    public function getFinalizedDtrs(Request $request)
    {
        $groupId = $request->get('payroll_group_id');
        $employeeId = $request->get('employee_id');
        
        if (!$groupId && !$employeeId) return response()->json([]);

        $query = \App\Models\Dtr::where('status', 'finalized');
        
        if ($groupId) {
            $query->whereHas('employee', function($q) use ($groupId) {
                $q->where('payroll_group_id', $groupId);
            });
        } elseif ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        
        $periods = $query->select('start_date', 'end_date')
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

        // Perform attendance verification
        $verification = $this->payrollService->verifyAttendance($payroll);

        // Count how many employees have finalized DTRs for this period that AREN'T in the batch yet
        $query = \App\Models\Dtr::where('status', 'finalized')
            ->whereDate('start_date', $payroll->start_date)
            ->whereDate('end_date', $payroll->end_date);

        if ($payroll->payroll_group_id) {
            $query->whereIn('employee_id', function($q) use ($payroll) {
                $q->select('id')->from('employees')
                    ->where('payroll_group_id', $payroll->payroll_group_id)
                    ->where('status', 'active');
            });
        } elseif ($payroll->employee_id) {
            $query->where('employee_id', $payroll->employee_id);
        }

        // Subtract those already processed in this batch
        $finalized_dtr_count = $query->whereNotIn('employee_id', $items->pluck('employee_id'))->count();

        return view('payroll.show', compact('payroll', 'items', 'item_count', 'finalized_dtr_count', 'verification'));
    }

    public function processBatch(Payroll $payroll, Request $request)
    {
        if ($payroll->status == 'approved') {
            return back()->with('error', 'Cannot re-process an approved payroll batch.');
        }

        try {
            $forceBypass = $request->has('force_bypass');

            // VERIFICATION: Ensure no missing logs or absent employees unless bypassed
            if (!$forceBypass) {
                $verification = $this->payrollService->verifyAttendance($payroll);
                if (!$verification['can_process']) {
                    $errorMessage = 'Cannot process payroll. Issues found: ';
                    if (!empty($verification['missing_dtr'])) $errorMessage .= count($verification['missing_dtr']) . ' missing DTRs. ';
                    if (!empty($verification['pending_dtr'])) $errorMessage .= count($verification['pending_dtr']) . ' DTRs not finalized. ';
                    if (!empty($verification['open_corrections'])) $errorMessage .= count($verification['open_corrections']) . ' employees with open attendance corrections. ';
                    if (!empty($verification['with_absences'])) $errorMessage .= count($verification['with_absences']) . ' employees have absences. ';

                    return back()->with('error', $errorMessage . ' Use the "Bypass" option if you want to proceed with zero-pay for these employees.');
                }
            }

            if ($request->boolean('queue')) {
                ProcessPayrollBatch::dispatch($payroll->fresh());

                return redirect()->route('payroll.show', $payroll->id)
                    ->with('success', 'Payroll batch queued for asynchronous processing.');
            }

            // We call the service directly for immediate feedback (Synchronous)
            // If the user wants to use the background worker, they'd use ProcessPayrollBatch::dispatch($payroll);
            $this->payrollService->computePayroll($payroll);
            
            return redirect()->route('payroll.show', $payroll->id)
                ->with('success', 'Payroll batch processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing batch: ' . $e->getMessage());
        }
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
        // Safety check: ensure at least one employee is processed
        $current_items = $payroll->items()->count();

        if ($current_items === 0) {
            return back()->with('error', 'Cannot finalize an empty payroll batch. Please process at least one employee.');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return redirect()->route('payroll.index')->with('success', 'Payroll period APPROVED. ' . $current_items . ' payslips are now finalized.');
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
