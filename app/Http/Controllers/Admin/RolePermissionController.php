<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::all();
        $users = User::all();
        return view('admin.roles.index', compact('roles', 'permissions', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string'
        ]);

        Role::create([
            'name' => $request->name, 
            'description' => $request->description,
            'remarks' => $request->remarks,
            'guard_name' => 'web'
        ]);

        return back()->with('success', 'Role created successfully.');
    }

    public function assignUsers(Request $request, Role $role)
    {
        $request->validate([
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id'
        ]);

        // Get users currently in this role
        $currentUsers = $role->users;
        
        // Remove role from all users currently in this role
        foreach ($currentUsers as $user) {
            $user->removeRole($role->name);
        }

        // Assign role to selected users
        if ($request->has('users')) {
            $users = User::whereIn('id', $request->users)->get();
            foreach ($users as $user) {
                $user->assignRole($role->name);
            }
        }

        // After bulk assignment, we should sync the legacy 'role' column for all affected users
        // This is optional but keeps the legacy logic consistent if the system still uses it
        $allAffectedUsers = $currentUsers->merge(User::whereIn('id', $request->users ?? [])->get())->unique('id');
        foreach ($allAffectedUsers as $user) {
            if ($user->hasRole('Super Admin')) {
                $user->role = 'super-admin';
            } elseif ($user->hasAnyRole(['Accounting Admin', 'HR Admin'])) {
                $user->role = 'admin';
            } else {
                $user->role = 'employee';
            }
            $user->save();
        }

        return back()->with('success', 'Users assigned to role ' . $role->name . ' successfully.');
    }

    public function update(Request $request, Role $role)
    {
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
            return back()->with('success', 'Permissions updated for ' . $role->name);
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'remarks' => 'nullable|string'
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'remarks' => $request->remarks
        ]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin' || $role->name === 'admin') {
            return back()->with('error', 'Cannot delete system roles.');
        }
        $role->delete();
        return back()->with('success', 'Role deleted successfully.');
    }
}
