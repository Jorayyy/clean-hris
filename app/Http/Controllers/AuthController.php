<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.login');
    }

        protected function redirectUserByRole($user)
    {
        // Check standard model helper OR look directly at the role string
        if ($user->isAdmin() || in_array($user->role, ['superadmin', 'admin'])) {
            return redirect('/admin/dashboard');
        }
        
        return redirect('/employee/dashboard');
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8', // Enhancement: Min 8 chars
            ]
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return $this->redirectUserByRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
