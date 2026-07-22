<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fault;

class TechnicianController extends Controller
{
    // DASHBOARD (only assigned faults, limit 5)
    public function dashboard()
    {
        $assignedCount = Fault::where('technician_id', auth()->id())->count();
        $inProgressCount = Fault::where('technician_id', auth()->id())->where('status', 'In Progress')->count();
        $resolvedCount = Fault::where('technician_id', auth()->id())->where('status', 'Resolved')->count();
        $pendingCount = Fault::where('technician_id', auth()->id())->where('status', 'Pending')->count();

        $faults = Fault::where('technician_id', auth()->id())
                        ->with(['reporter', 'comments.author', 'comments.replies.author'])
                        ->latest()
                        ->take(5)
                        ->get();

        return view('technician.dashboard', [
            'faults' => $faults,
            'assignedCount' => $assignedCount,
            'inProgressCount' => $inProgressCount,
            'resolvedCount' => $resolvedCount,
            'pendingCount' => $pendingCount,
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
                        ->latest()
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
        $request->validate([
            'status' => 'required|in:Pending,In Progress,Resolved',
        ]);

        $fault = Fault::where('technician_id', auth()->id())->findOrFail($id);

        $fault->status = $request->status;
        $fault->save();

        return back()->with('success', 'Status updated');
    }
}
