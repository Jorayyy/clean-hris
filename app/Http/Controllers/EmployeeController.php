<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollGroup;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('payrollGroup')->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $groups = PayrollGroup::all();
        return view('employees.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|unique:employees',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:employees',
            'position' => 'required',
            'daily_rate' => 'required|numeric',
        ]);

        Employee::create($request->all());
        return redirect()->route('employees.index');
    }

    public function edit(Employee $employee)
    {
        $groups = PayrollGroup::all();
        return view('employees.edit', compact('employee', 'groups'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'employee_id' => 'required|unique:employees,employee_id,' . $employee->id,
            'first_name' => 'required',
            'last_name' => 'required',
            'position' => 'required',
            'daily_rate' => 'required|numeric',
        ]);

        $employee->update($request->all());
        return redirect()->route('employees.index');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index');
    }
}
