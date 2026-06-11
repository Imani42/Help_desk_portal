<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffAuthController extends Controller
{
    // Show Manager form
    public function managerForm()
    {
        return view('auth.manager_register');
    }

    // Show Technician form
    public function technicianForm()
    {
        return view('auth.technician_register');
    }

    // Register Manager
    public function registerManager(Request $request)
    {
        $request->validate([
            'name' => 'required',

            // TTCL email only
            'email' => ['required','regex:/^[a-zA-Z0-9._%+-]+@ttcl\.co\.tz$/'],

            'phone' => 'required',
            'region' => 'required',
            'district' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'region'=>$request->region,
            'district'=>$request->district,
            'password'=>Hash::make($request->password),
            'role'=>'manager',
            'is_approved'=>true
        ]);

        return redirect('/login');
    }

    // Register Technician
    public function registerTechnician(Request $request)
    {
        $request->validate([
            'name' => 'required',

            'email' => ['required','regex:/^[a-zA-Z0-9._%+-]+@ttcl\.co\.tz$/'],

            'phone' => 'required',
            'tech_base' => 'required',
            'region' => 'required',
            'district' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'tech_base'=>$request->tech_base,
            'region'=>$request->region,
            'district'=>$request->district,
            'password'=>Hash::make($request->password),
            'role'=>'technician',
            'is_approved'=>false
        ]);

        return redirect('/login');
    }
}
