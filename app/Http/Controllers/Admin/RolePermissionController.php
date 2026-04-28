<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return view('admin.roles.index', compact('roles', 'permissions'));
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
