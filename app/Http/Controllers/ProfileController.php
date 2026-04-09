<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class ProfileController extends Controller
{
    public function showAdmin()
    {
        $user = Auth::user();
        return view('profile.admin', compact('user'));
    }

    public function showEmployee()
    {
        $user = Auth::user();
        $employee = Employee::find($user->employee_id);
        return view('profile.employee', compact('user', 'employee'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'dtr_password' => 'nullable|string|max:255'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Update DTR authorization password if provided
        if ($request->has('dtr_password')) {
            $user->dtr_password = $request->dtr_password;
        }

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}