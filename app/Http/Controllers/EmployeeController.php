<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Models\Site;
use App\Models\User;
use App\Models\Position;
use App\Models\Classification;
use App\Models\Level;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\StoreEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        $positions = Position::orderBy('name')->get();
        $classifications = Classification::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();
        return view('employees.create', compact('groups', 'sites', 'positions', 'classifications', 'levels'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        try {
            DB::beginTransaction();

            // 1. Create Employee
            $employee = Employee::create($data);

            // 2. Create User Account for Employee
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'plain_password' => $data['password'], 
                'role' => 'employee',
                'employee_id' => $employee->id,
            ]);

            // Assign Employee Role if Spatie roles exist
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $user->assignRole('Employee');
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee and portal account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $groups = PayrollGroup::all();
        $sites = Site::all();
        $positions = Position::orderBy('name')->get();
        $classifications = Classification::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();
        return view('employees.edit', compact('employee', 'groups', 'sites', 'positions', 'classifications', 'levels'));
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        try {
            DB::beginTransaction();

            $employee->update($data);

            // Update user account if it exists
            $user = User::where('employee_id', $employee->id)->orWhere('email', $employee->email)->first();
            
            if ($user) {
                $userData = [
                    'name' => $employee->full_name,
                    'email' => $employee->email,
                ];

                if (!empty($data['password'])) {
                    $userData['password'] = Hash::make($data['password']);
                    $userData['plain_password'] = $data['password'];
                }

                $user->update($userData);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();

            // Delete associated user account first to satisfy foreign key constraints
            User::where('employee_id', $employee->id)->delete();

            // Now delete the employee
            $employee->delete();

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee and associated portal account deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.index')->with('error', 'Failed to delete employee. They may have existing attendance or payroll records.');
        }
    }
}
