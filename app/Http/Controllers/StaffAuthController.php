<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffAuthController extends Controller
{
    private const PASSWORD_RULES = [
        'required',
        'confirmed',
        'min:8',
        'regex:/^(?=(?:.*\d){2,})(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/',
    ];

    private const PASSWORD_MESSAGES = [
        'password.regex' => 'Password must include at least one capital letter, at least two digits, and at least one special character.',
        'password.max' => 'Password is too long. Use 255 characters or fewer.',
    ];

    private const STAFF_MESSAGES = [
        'email.regex' => 'Check your email. Use firstname.lastname@ttcl.co.tz',
        'email.unique' => 'This email is already registered. Use a different email or login.',
        'phone.regex' => 'TTCL numbers must start with 073 and be exactly 10 digits',
        'phone.unique' => 'This phone number is already registered. A phone number cannot be used by different users.',
    ];

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

    // Show Admin form
    public function adminForm()
    {
        if (User::where('role', 'admin')->exists()) {
            return redirect('/login')->withErrors([
                'email' => 'Admin registration is closed. An admin account already exists.',
            ]);
        }

        return view('auth.admin_register');
    }

    // Register Manager
    public function registerManager(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            // Staff email format: firstname.lastname@ttcl.co.tz
            'email' => ['required','string','max:255','regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i','unique:users,email'],

            'phone' => ['required','string','max:20','regex:/^073\d{7}$/','unique:users,phone'],
            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'password' => array_merge(self::PASSWORD_RULES, ['max:255']),
        ], self::STAFF_MESSAGES + self::PASSWORD_MESSAGES);

        $this->validateRegionDistrict($request);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'region'=>$request->region,
            'district'=>$request->district,
            'password'=>Hash::make($request->password),
            'role'=>'manager',
            'is_approved'=>false
        ]);

        return redirect('/login')->with('success', 'Manager registered successfully. Wait for admin approval before login. It will take not more than 24 hours.');
    }

    // Register Technician
    public function registerTechnician(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            'email' => ['required','string','max:255','regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i','unique:users,email'],

            'phone' => ['required','string','max:20','regex:/^073\d{7}$/','unique:users,phone'],
            'tech_base' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'password' => array_merge(self::PASSWORD_RULES, ['max:255']),
        ], self::STAFF_MESSAGES + self::PASSWORD_MESSAGES);

        $this->validateRegionDistrict($request);

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

        return redirect('/login')->with('success', 'Registered successfully. Wait for admin approval before login. It will take not more than 24 hours.');
    }

    // Register Admin
    public function registerAdmin(Request $request)
    {
        if (User::where('role', 'admin')->exists()) {
            return redirect('/login')->withErrors([
                'email' => 'Admin registration is closed. An admin account already exists.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','string','max:255','regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i','unique:users,email'],
            'phone' => ['required','string','max:20','regex:/^073\d{7}$/','unique:users,phone'],
            'password' => array_merge(self::PASSWORD_RULES, ['max:255']),
        ], self::STAFF_MESSAGES + self::PASSWORD_MESSAGES);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
            'role'=>'admin',
            'is_approved'=>true
        ]);

        return redirect('/login')->with('success', 'Admin registered successfully. You can now log in.');
    }

    private function validateRegionDistrict(Request $request): void
    {
        $districts = config('tanzania_locations.regions.' . $request->region, []);

        if (! in_array($request->district, $districts, true)) {
            throw ValidationException::withMessages([
                'district' => 'Select a district that belongs to the selected region.',
            ]);
        }
    }
}
