<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fault;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $reportedCount = Fault::where('user_id', Auth::id())->count();
        $inProgressCount = Fault::where('user_id', Auth::id())->where('status', 'In Progress')->count();
        $resolvedCount = Fault::where('user_id', Auth::id())->where('status', 'Resolved')->count();
        $pendingCount = Fault::where('user_id', Auth::id())->where('status', 'Pending')->count();

        $faults = Fault::where('user_id', Auth::id())
            ->with(['technician', 'comments.author', 'comments.replies.author'])
            ->where('status', '!=', 'Resolved')
            ->latest()
            ->take(3)
            ->get();

        return view('customer.dashboard', [
            'page' => 'dashboard',
            'faults' => $faults,
            'reportedCount' => $reportedCount,
            'inProgressCount' => $inProgressCount,
            'resolvedCount' => $resolvedCount,
            'pendingCount' => $pendingCount,
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
            ->paginate(20);

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
            ->latest()
            ->paginate(20);

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

        return redirect('/customer/dashboard');
    }

    public function deleteFault($id)
    {
        $fault = Fault::where('user_id', Auth::id())->findOrFail($id);
        $fault->delete();

        return redirect('/customer/my-faults')->with('success', 'Fault deleted successfully');
    }
}
