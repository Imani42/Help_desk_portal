<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    // Show form
    public function showRegister()
    {
        return view('auth.customer_register');
    }

    // Handle form
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            // Gmail only
            'email' => ['required','regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'],

            // Phone validation
            'phone' => ['required','regex:/^(0\d{9}|\+255\d{9})$/'],

            'region' => 'required',
            'district' => 'required',
            'ward' => 'required',
            'street' => 'required',

            'password' => 'required|confirmed|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'region' => $request->region,
            'district' => $request->district,
            'ward' => $request->ward,
            'street' => $request->street,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'is_approved' => false
        ]);

        return redirect('/login')->with('success','Registered Successfully');
    }
}
