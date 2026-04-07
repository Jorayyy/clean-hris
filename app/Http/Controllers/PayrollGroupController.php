<?php

namespace App\Http\Controllers;

use App\Models\PayrollGroup;
use Illuminate\Http\Request;

class PayrollGroupController extends Controller
{
    public function index()
    {
        $groups = PayrollGroup::withCount('employees')->get();
        return view('payroll-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('payroll-groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:payroll_groups',
        ]);

        PayrollGroup::create($request->all());
        return redirect()->route('payroll-groups.index')->with('success', 'Group created.');
    }

    public function edit(PayrollGroup $payrollGroup)
    {
        return view('payroll-groups.edit', compact('payrollGroup'));
    }

    public function update(Request $request, PayrollGroup $payrollGroup)
    {
        $request->validate([
            'name' => 'required|unique:payroll_groups,name,' . $payrollGroup->id,
        ]);

        $payrollGroup->update($request->all());
        return redirect()->route('payroll-groups.index')->with('success', 'Group updated.');
    }

    public function destroy(PayrollGroup $payrollGroup)
    {
        $payrollGroup->delete();
        return redirect()->route('payroll-groups.index')->with('success', 'Group deleted.');
    }
}
