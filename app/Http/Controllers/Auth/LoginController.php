<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        $attemptKey = 'login_attempts_' . hash('sha256', Str::lower($credentials['email']));

        if (Auth::attempt($credentials)) {

            $request->session()->forget($attemptKey);

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

        $attempts = (int) $request->session()->get($attemptKey, 0) + 1;
        $request->session()->put($attemptKey, $attempts);

        $errors = ['email' => 'Invalid login details.'];

        if ($attempts >= 3) {
            $errors['email'] = 'Invalid login details. You have entered an incorrect password three times. Use Forgot Password to reset it.';
            $request->session()->flash('show_forgot_password', true);
        }

        return back()->withErrors($errors)->withInput($request->only('email'));
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
