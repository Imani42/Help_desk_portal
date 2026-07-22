<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Show login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255'
        ], [
            'email.max' => 'Email is too long. Please enter a valid email address.',
            'password.max' => 'Password is too long. Please enter the correct password.',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $user = Auth::user();

            if (in_array($user->role, ['customer', 'technician', 'manager']) && ! $user->is_approved) {
                Auth::logout();

                return redirect('/login')->with('success', 'Wait for admin approval before login. It will take not more than 24 hours.');
            }

            if ($user->role == 'customer') {
                return redirect('/customer/dashboard');
            }

            if ($user->role == 'technician') {
                return redirect('/technician/dashboard');
            }

            if ($user->role == 'manager') {
                return redirect('/manager/dashboard');
            }

            if ($user->role == 'admin') {
                return redirect('/admin/dashboard');
            }

            Auth::logout();
            return redirect('/login')->withErrors([
                'email' => 'Role not assigned'
            ]);
        }

        return back()->withErrors([
            'email' => 'Invalid login details'
        ]);
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
