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
        $employee = Employee::find($user->employee_id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'web_bundy_code' => 'nullable|string|min:4',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|string',
            'civil_status' => 'nullable|string',
            'religion' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($employee) {
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->middle_name = $request->middle_name;
            $employee->birthday = $request->birthday;
            $employee->gender = $request->gender;
            $employee->civil_status = $request->civil_status;
            $employee->religion = $request->religion;
            $employee->place_of_birth = $request->place_of_birth;
            $employee->web_bundy_code = $request->web_bundy_code;

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $employee->photo = $path;
            }
            $employee->save();
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