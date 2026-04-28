<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view users', only: ['index', 'show']),
            new Middleware('can:create users', only: ['create', 'store']),
            new Middleware('can:edit users', only: ['edit', 'update']),
            new Middleware('can:delete users', only: ['destroy']),
        ];
    }

    public function index()
    {
        $users = User::with('roles')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $employees = Employee::whereNotIn('id', User::pluck('employee_id')->filter())->get();
        return view('admin.users.create', compact('roles', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'employee_id' => $request->employee_id,
            'role' => 'employee', // Keep the legacy column updated for now
        ]);

        $user->assignRole($request->roles);

        // Sync legacy role column for sidebar compatibility
        if ($user->hasRole('Super Admin')) {
            $user->role = 'super-admin';
        } elseif ($user->hasAnyRole(['Accounting Admin', 'HR Admin'])) {
            $user->role = 'admin';
        } else {
            $user->role = 'employee';
        }
        $user->save();

        return redirect()->route('users.index')->with('success', 'User created and roles assigned successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['plain_password'] = $request->password;
        }

        $user->update($data);
        $user->syncRoles($request->roles);

        // Sync legacy role column for sidebar compatibility
        if ($user->hasRole('Super Admin')) {
            $user->role = 'super-admin';
        } elseif ($user->hasAnyRole(['Accounting Admin', 'HR Admin'])) {
            $user->role = 'admin';
        } else {
            $user->role = 'employee';
        }
        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
