<?php

namespace App\Http\Controllers;

use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollItem::with(['employee', 'payroll']);

        if ($request->employee_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        if ($request->payroll_id) {
            $query->where('payroll_id', $request->payroll_id);
        }

        $salaries = $query->latest()->paginate(15);
        $payrolls = Payroll::latest()->get();
        
        return view('salaries.index', compact('salaries', 'payrolls'));
    }

    public function edit(PayrollItem $salary)
    {
        return view('salaries.edit', compact('salary'));
    }

    public function update(Request $request, PayrollItem $salary)
    {
        $request->validate([
            'basic_pay' => 'required|numeric',
            'overtime_pay' => 'required|numeric',
            'bonuses' => 'required|numeric',
            'night_diff' => 'required|numeric',
            'deductions_sss' => 'required|numeric',
            'deductions_pagibig' => 'required|numeric',
            'deductions_philhealth' => 'required|numeric',
            'other_deductions' => 'required|numeric',
        ]);

        $data = $request->all();
        
        // Recalculate Net Pay
        $earnings = $data['basic_pay'] + $data['overtime_pay'] + $data['bonuses'] + $data['night_diff'];
        $deductions = $data['deductions_sss'] + $data['deductions_pagibig'] + $data['deductions_philhealth'] + $data['other_deductions'];
        $data['net_pay'] = $earnings - $deductions;

        // Sync deductions_json for consistency with payslip view
        $data['deductions_json'] = [
            ['type' => 'SSS', 'amount' => $data['deductions_sss']],
            ['type' => 'PAGIBIG', 'amount' => $data['deductions_pagibig']],
            ['type' => 'PHILHEALTH', 'amount' => $data['deductions_philhealth']],
        ];
        
        if ($data['other_deductions'] > 0) {
            $data['deductions_json'][] = ['type' => 'OTHER', 'amount' => $data['other_deductions']];
        }

        $salary->update($data);

        return redirect()->route('salaries.index')->with('success', 'Salary record updated successfully.');
    }

    public function destroy(PayrollItem $salary)
    {
        $salary->delete();
        return redirect()->route('salaries.index')->with('success', 'Salary record deleted.');
    }
}
