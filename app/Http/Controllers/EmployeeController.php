<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Models\Site;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\StoreEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
        $sites = Site::all();
        return view('employees.create', compact('groups', 'sites'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $groups = PayrollGroup::all();
        $sites = Site::all();
        return view('employees.edit', compact('employee', 'groups', 'sites'));
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($data);
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index');
    }
}
