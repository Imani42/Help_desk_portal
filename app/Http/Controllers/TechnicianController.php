<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fault;

class TechnicianController extends Controller
{
    // DASHBOARD (only assigned faults, limit 2)
    public function dashboard()
    {
        $faults = Fault::where('technician_id', auth()->id())
                        ->with(['reporter', 'comments.author', 'comments.replies.author'])
                        ->latest()
                        ->take(2)
                        ->get();

        return view('technician.dashboard', [
            'faults' => $faults,
            'page' => 'dashboard'
        ]);
    }

    // ALL ASSIGNED FAULTS
    public function assigned()
    {
        $faults = Fault::where('technician_id', auth()->id())
                        ->with(['reporter', 'comments.author', 'comments.replies.author'])
                        ->latest()
                        ->get();

        return view('technician.dashboard', [
            'faults' => $faults,
            'page' => 'assigned'
        ]);
    }

    // COMPLETED FAULTS
    public function completed()
    {
        $faults = Fault::where('technician_id', auth()->id())
                        ->with(['reporter', 'comments.author', 'comments.replies.author'])
                        ->where('status', 'Resolved')
                        ->get();

        return view('technician.dashboard', [
            'faults' => $faults,
            'page' => 'completed'
        ]);
    }

    // ACCOUNT
    public function account()
    {
        return view('technician.dashboard', [
            'page' => 'account'
        ]);
    }

    // UPDATE STATUS
    public function updateStatus(Request $request, $id)
    {
        $fault = Fault::findOrFail($id);

        $fault->status = $request->status;
        $fault->save();

        return back()->with('success', 'Status updated');
    }
}
