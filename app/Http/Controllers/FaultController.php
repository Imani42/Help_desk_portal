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
            'location' => 'required'
        ]);

        Fault::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'description' => $request->description,
            'location' => $request->location,
            'status' => 'Pending'
        ]);

        return redirect('/customer/dashboard')->with('success','Fault submitted');
    }
}