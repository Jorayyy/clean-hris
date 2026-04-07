<?php

namespace App\Http\Controllers;

use App\Models\PayrollItem;
use App\Models\Employee;
use Illuminate\Http\Request;

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

        $salaries = $query->latest()->paginate(15);
        
        return view('salaries.index', compact('salaries'));
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

        $salary->update($data);

        return redirect()->route('salaries.index')->with('success', 'Salary record updated successfully.');
    }

    public function destroy(PayrollItem $salary)
    {
        $salary->delete();
        return redirect()->route('salaries.index')->with('success', 'Salary record deleted.');
    }
}
