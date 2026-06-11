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
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $user = Auth::user();

            if (in_array($user->role, ['customer', 'technician']) && ! $user->is_approved) {
                Auth::logout();

                return redirect('/login')->withErrors([
                    'email' => 'Your account is waiting for manager approval'
                ]);
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
