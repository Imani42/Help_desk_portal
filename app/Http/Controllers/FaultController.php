<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fault;
use Illuminate\Support\Facades\Auth;

class FaultController extends Controller
{
    // Show form
    public function create()
    {
        return view('customer.create_fault');
    }

    // Store fault
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'description' => 'required',
            'contact_phone' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $location = collect([
            'Region' => $user->region,
            'District' => $user->district,
            'Ward' => $user->ward,
            'Street' => $user->street,
        ])->filter()->map(function ($value, $label) {
            return $label . ': ' . $value;
        })->implode(', ');

        Fault::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'description' => $request->description,
            'location' => $location ?: 'Not provided',
            'contact_phone' => $request->contact_phone,
            'status' => 'Pending'
        ]);

        return redirect('/customer/dashboard')->with('success','Fault submitted');
    }
}
