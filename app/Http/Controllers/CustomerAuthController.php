<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
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

    private const REGISTER_MESSAGES = [
        'email.regex' => 'Customer email must be a Gmail address, for example name@gmail.com.',
        'email.unique' => 'This email is already registered. Use a different email or login.',
        'phone.regex' => 'Phone number must be a valid Tanzanian number, for example 0712345678 or +255712345678.',
        'phone.unique' => 'This phone number is already registered. A phone number cannot be used by different users.',
    ];

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
            'email' => ['required','string','max:255','regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/','unique:users,email'],

            // Phone validation
            'phone' => ['required','string','max:20','regex:/^(0\d{9}|\+255\d{9})$/','unique:users,phone'],

            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'street' => 'required|string|max:255',

            'password' => array_merge(self::PASSWORD_RULES, ['max:255']),
        ], self::REGISTER_MESSAGES + self::PASSWORD_MESSAGES);

        $this->validateRegionDistrict($request);

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

        return redirect('/login')->with('success', 'Registered successfully. Wait for regional manager approval before login. Action will take not more than 24 hours unless.');
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
