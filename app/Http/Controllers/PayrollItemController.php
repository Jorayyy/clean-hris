<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\Dtr;
use App\Models\Attendance;
use Illuminate\Http\Request;

class PayrollItemController extends Controller
{
    public function create(Request $request)
    {
        $payroll = Payroll::findOrFail($request->payroll_id);
        $employees = Employee::where('payroll_group_id', $payroll->payroll_group_id)->get();
        return view('payroll_items.create', compact('payroll', 'employees'));
    }

    public function getEmployeeBasis(Request $request)
    {
        $employeeId = $request->employee_id;
        $payrollId = $request->payroll_id;
        
        $payroll = Payroll::findOrFail($payrollId);
        $employee = Employee::findOrFail($employeeId);

        // Find finalized DTR summary
        $dtr = Dtr::where('employee_id', $employeeId)
            ->where('start_date', $payroll->start_date)
            ->where('end_date', $payroll->end_date)
            ->where('status', 'finalized')
            ->first();

        // Get detailed attendance logs for the period
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$payroll->start_date, $payroll->end_date])
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'employee' => [
                'daily_rate' => $employee->daily_rate,
                'position' => $employee->position,
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
            'payroll_id' => 'required|exists:payrolls,id',
            'employee_id' => 'required|exists:employees,id',
            'total_days' => 'required|numeric',
            'total_hours' => 'required|numeric',
            'basic_pay' => 'required|numeric',
            'overtime_pay' => 'nullable|numeric',
            'night_diff' => 'nullable|numeric',
            'bonuses' => 'nullable|numeric',
            'deductions_sss' => 'nullable|numeric',
            'deductions_pagibig' => 'nullable|numeric',
            'deductions_philhealth' => 'nullable|numeric',
            'other_deductions' => 'nullable|numeric',
        ]);

        $net_pay = $data['basic_pay'] 
                 + ($data['overtime_pay'] ?? 0) 
                 + ($data['night_diff'] ?? 0) 
                 + ($data['bonuses'] ?? 0)
                 - ($data['deductions_sss'] ?? 0)
                 - ($data['deductions_pagibig'] ?? 0)
                 - ($data['deductions_philhealth'] ?? 0)
                 - ($data['other_deductions'] ?? 0);

        $data['net_pay'] = $net_pay;
        
        // Ensure nullable fields that are missing from request but NOT NULL in DB are set to 0
        $data['overtime_pay'] = $data['overtime_pay'] ?? 0;
        $data['night_diff'] = $data['night_diff'] ?? 0;
        $data['bonuses'] = $data['bonuses'] ?? 0;
        $tableFields = [
            'deductions_sss', 'deductions_pagibig', 
            'deductions_philhealth', 'other_deductions'
        ];
        foreach ($tableFields as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        PayrollItem::create($data);

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
