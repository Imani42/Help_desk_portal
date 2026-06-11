<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Fault;


class ManagerController extends Controller
{
    // DASHBOARD (latest faults)
    public function dashboard()
    {
        $faults = Fault::with(['reporter', 'technician', 'comments.author', 'comments.replies.author'])->latest()->take(5)->get();
        $technicians = User::where('role', 'technician')->where('is_approved', true)->get();

        return view('manager.dashboard', [
            'faults' => $faults,
            'technicians' => $technicians,
            'page' => 'dashboard'
        ]);
    }

    // ALL FAULTS
    public function allFaults()
    {
        $faults = Fault::with(['reporter', 'technician', 'comments.author', 'comments.replies.author'])->latest()->get();
        $technicians = User::where('role', 'technician')->where('is_approved', true)->get();

        return view('manager.dashboard', [
            'faults' => $faults,
            'technicians' => $technicians,
            'page' => 'all_faults'
        ]);
    }

    // ASSIGNED FAULTS
    public function assigned()
    {
        $faults = Fault::with(['reporter', 'technician', 'comments.author', 'comments.replies.author'])->whereNotNull('technician_id')->latest()->get();

        return view('manager.dashboard', [
            'faults' => $faults,
            'page' => 'assigned'
        ]);
    }

    // ACCOUNT
    public function account()
    {
        return view('manager.dashboard', [
            'page' => 'account'
        ]);
    }

    // ASSIGN TECHNICIAN
    public function assign(Request $request, $id)
    {
        $fault = Fault::findOrFail($id);

        $fault->technician_id = $request->technician_id;
        $fault->status = 'In Progress';
        $fault->save();

        return back()->with('success', 'Technician assigned successfully');
    }

    public function technicians()
{
    $technicians = User::where('role', 'technician')
        ->orderBy('is_approved')
        ->latest()
        ->get();

    return view('manager.dashboard', [
        'technicians' => $technicians,
        'page' => 'technicians'
    ]);
}

public function storeTechnician(Request $request)
{
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'role' => 'technician',
        'is_approved' => true
    ]);

    return back()->with('success', 'Technician added');
}

public function updateTechnician(Request $request, $id)
{
    $user = User::findOrFail($id);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->save();

    return back()->with('success', 'Updated');
}

public function deleteTechnician($id)
{
    User::destroy($id);
    return back()->with('success', 'Deleted');
}

public function customers()
{
    $customers = User::where('role', 'customer')
        ->orderBy('is_approved')
        ->latest()
        ->get();

    return view('manager.dashboard', [
        'customers' => $customers,
        'page' => 'customers'
    ]);
}

public function storeCustomer(Request $request)
{
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
        'is_approved' => true
    ]);

    return back()->with('success', 'Customer added');
}

public function updateCustomer(Request $request, $id)
{
    $user = User::findOrFail($id);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->region = $request->region;
    $user->district = $request->district;
    $user->ward = $request->ward;
    $user->street = $request->street;
    $user->save();

    return back()->with('success', 'Customer updated');
}

public function approveUser($id)
{
    $user = User::whereIn('role', ['customer', 'technician'])->findOrFail($id);

    $user->is_approved = true;
    $user->save();

    return back()->with('success', 'User approved');
}

public function deactivateUser($id)
{
    $user = User::whereIn('role', ['customer', 'technician'])->findOrFail($id);

    $user->is_approved = false;
    $user->save();

    return back()->with('success', 'User deactivated');
}

public function deleteCustomer($id)
{
    User::destroy($id);
    return back()->with('success', 'Customer deleted');
}
}
