<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fault;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $faults = Fault::where('user_id', Auth::id())
            ->with(['technician', 'comments.author', 'comments.replies.author'])
            ->latest()
            ->take(2)
            ->get();

        return view('customer.dashboard', [
            'page' => 'dashboard',
            'faults' => $faults
        ]);
    }

    public function report()
    {
        return view('customer.dashboard', [
            'page' => 'report'
        ]);
    }

    public function myFaults()
    {
        $faults = Fault::where('user_id', Auth::id())
            ->with(['technician', 'comments.author', 'comments.replies.author'])
            ->latest()
            ->get();

        return view('customer.dashboard', [
            'page' => 'my_faults',
            'faults' => $faults
        ]);
    }

    public function resolved()
    {
        $faults = Fault::where('user_id', Auth::id())
            ->with(['technician', 'comments.author', 'comments.replies.author'])
            ->where('status', 'Resolved')
            ->get();

        return view('customer.dashboard', [
            'page' => 'resolved',
            'faults' => $faults
        ]);
    }

    public function account()
    {
        return view('customer.dashboard', [
            'page' => 'account'
        ]);
    }

    public function storeFault(Request $request)
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

        return redirect('/customer/dashboard');
    }
}
