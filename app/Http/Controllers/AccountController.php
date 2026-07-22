<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    private const PASSWORD_RULES = [
        'required',
        'string',
        'min:6',
        'confirmed',
        'regex:/^(?=(?:.*\d){2,})(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/',
    ];

    private const PASSWORD_MESSAGES = [
        'password.regex' => 'Password must include at least one capital letter, at least two digits, and at least one special character.',
    ];

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'tech_base' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $directory = public_path('uploads/profile_photos');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            if ($user->profile_photo && file_exists(public_path($user->profile_photo))) {
                @unlink(public_path($user->profile_photo));
            }

            $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move($directory, $filename);
            $validated['profile_photo'] = 'uploads/profile_photos/' . $filename;
        }

        $user->fill($validated);
        $user->save();

        return back()->with('success', 'Account information updated');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => self::PASSWORD_RULES,
        ], self::PASSWORD_MESSAGES);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password reset successfully');
    }

    // for password reset
    public function delete(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect('/login')->with('success', 'Your account has been deleted');
    }
}
