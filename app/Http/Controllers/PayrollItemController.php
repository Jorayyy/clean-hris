<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\Dtr;
use App\Models\Attendance;
use App\Models\DeductionType;
use Illuminate\Http\Request;

class PayrollItemController extends Controller
{
    public function create(Request $request)
    {
        $payroll = Payroll::findOrFail($request->payroll_id);
        
        $query = Employee::where('status', 'active');
        
        if ($payroll->employee_id) {
            // If it's an individual payroll, only show that employee
            $query->where('id', $payroll->employee_id);
        } else {
            // If it's a group payroll, show employees in that group
            $query->where('payroll_group_id', $payroll->payroll_group_id);
        }

        $employees = $query->get();

        $settings = \App\Models\AppSetting::first();

        // Filter out employees who already have a payslip in this payroll
        $existingEmployeeIds = PayrollItem::where('payroll_id', $payroll->id)
            ->pluck('employee_id')
            ->toArray();
            
        $employees = $employees->reject(function($employee) use ($existingEmployeeIds) {
            return in_array($employee->id, $existingEmployeeIds);
        });

        $deductionTypes = DeductionType::where('is_active', true)->get();
        return view('payroll_items.create', compact('payroll', 'employees', 'deductionTypes', 'settings'));
    }

    public function getEmployeeBasis(Request $request)
    {
        $employeeId = $request->employee_id;
        $payrollId = $request->payroll_id;
        
        $payroll = Payroll::findOrFail($payrollId);
        $employee = Employee::findOrFail($employeeId);

        // Standardize dates to Y-m-d strings to avoid object/string mismatch in queries
        $startDate = \Carbon\Carbon::parse($payroll->start_date)->toDateString();
        $endDate = \Carbon\Carbon::parse($payroll->end_date)->toDateString();

        // Find finalized DTR summary that covers this period
        $dtr = Dtr::where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->where('status', 'finalized')
            ->first();

        // Get detailed attendance logs for the period
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        $settings = \App\Models\AppSetting::first();

        return response()->json([
            'payroll_id' => $payrollId,
            'period' => $startDate . ' to ' . $endDate,
            'employee' => [
                'id' => $employee->id,
                'daily_rate' => $employee->daily_rate,
                'position' => $employee->position,
            ],
            'settings' => [
                'late_rate' => $settings->late_rate ?? 1.0,
                'undertime_rate' => $settings->undertime_rate ?? 1.0,
            ],
            'dtr' => $dtr ? [
                'total_regular_hours' => $dtr->total_regular_hours,
                'total_overtime_hours' => $dtr->total_overtime_hours,
                'total_late_minutes' => $dtr->total_late_minutes,
                'total_undertime_minutes' => $dtr->total_undertime_minutes,
                'total_absent_days' => $dtr->total_absent_days,
            ] : null,
            'attendances' => $attendances
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payroll_id' => 'required',
            'employee_id' => 'required',
            'total_days' => 'required|numeric',
            'total_hours' => 'required|numeric',
            'basic_pay' => 'required|numeric',
            'overtime_pay' => 'nullable|numeric',
            'night_diff' => 'nullable|numeric',
            'bonuses' => 'nullable|numeric',
            'deductions' => 'nullable|array',
            'deductions.*.type' => 'nullable|string',
            'deductions.*.amount' => 'nullable|numeric|min:0',
        ]);

        $overtime_pay = $request->input('overtime_pay', 0) ?: 0;
        $night_diff = $request->input('night_diff', 0) ?: 0;
        $bonuses = $request->input('bonuses', 0) ?: 0;

        $total_deductions = 0;
        $deductions_log = [];
        if ($request->has('deductions') && is_array($request->deductions)) {
            foreach ($request->deductions as $d) {
                if (!empty($d['type']) && isset($d['amount']) && is_numeric($d['amount'])) {
                    $total_deductions += $d['amount'];
                    $deductions_log[] = [
                        'type' => $d['type'],
                        'amount' => $d['amount']
                    ];
                }
            }
        }

        $net_pay = $data['basic_pay'] 
                 + $overtime_pay 
                 + $night_diff 
                 + $bonuses
                 - $total_deductions;

        $payrollItem = PayrollItem::create([
            'payroll_id' => $data['payroll_id'],
            'employee_id' => $data['employee_id'],
            'total_days' => $data['total_days'],
            'total_hours' => $data['total_hours'],
            'basic_pay' => $data['basic_pay'],
            'overtime_pay' => $overtime_pay,
            'night_diff' => $night_diff,
            'bonuses' => $bonuses,
            'net_pay' => $net_pay,
            'deductions_json' => $deductions_log,
        ]);

        // Auto-update payroll status to 'processing' if it was 'draft'
        $payroll = Payroll::find($data['payroll_id']);
        if ($payroll && $payroll->status === 'draft') {
            $payroll->update(['status' => 'processing']);
        }

        return redirect()->route('payroll.show', $data['payroll_id'])->with('success', 'Payslip created successfully.');
    }

    public function edit(PayrollItem $payrollItem)
    {
        return view('payroll_items.edit', compact('payrollItem'));
    }

    public function update(Request $request, PayrollItem $payrollItem)
    {
        $data = $request->validate([
            'total_days' => 'required|numeric',
            'total_hours' => 'required|numeric',
            'basic_pay' => 'required|numeric',
            'overtime_pay' => 'nullable|numeric',
            'bonuses' => 'nullable|numeric',
            'deductions_sss' => 'nullable|numeric',
            'deductions_pagibig' => 'nullable|numeric',
            'deductions_philhealth' => 'nullable|numeric',
            'other_deductions' => 'nullable|numeric',
        ]);

        $net_pay = $data['basic_pay'] 
                 + ($data['overtime_pay'] ?? 0) 
                 + ($data['bonuses'] ?? 0)
                 - ($data['deductions_sss'] ?? 0)
                 - ($data['deductions_pagibig'] ?? 0)
                 - ($data['deductions_philhealth'] ?? 0)
                 - ($data['other_deductions'] ?? 0);

        $data['net_pay'] = $net_pay;

        $payrollItem->update($data);

        return redirect()->route('payroll.show', $payrollItem->payroll_id)->with('success', 'Payslip updated successfully.');
    }

    public function destroy(PayrollItem $payrollItem)
    {
        $payrollId = $payrollItem->payroll_id;
        $payrollItem->delete();
        return redirect()->route('payroll.show', $payrollId)->with('success', 'Payslip deleted.');
    }
}
