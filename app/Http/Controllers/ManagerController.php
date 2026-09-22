<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Fault;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class ManagerController extends Controller
{
    private const FAULT_RELATIONS = ['reporter', 'technician', 'comments.author', 'comments.replies.author'];

    private const PASSWORD_RULES = [
        'required',
        'min:8',
        'regex:/^(?=(?:.*\d){2,})(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/',
    ];

    private const PASSWORD_MESSAGES = [
        'password.regex' => 'Password must include at least one capital letter, at least two digits, and at least one special character.',
    ];

    private function managerRegion(): ?string
    {
        $managerRegion = trim((string) auth()->user()->region);
        $regions = config('tanzania_locations.regions', []);

        return collect(array_keys($regions))->first(function ($region) use ($managerRegion) {
            return strtolower($region) === strtolower($managerRegion);
        }) ?? $managerRegion;
    }

    private function validateDistrictForManager(Request $request): void
    {
        $districts = config('tanzania_locations.regions.' . $this->managerRegion(), []);

        if (! in_array($request->district, $districts, true)) {
            throw ValidationException::withMessages([
                'district' => 'Select a district that belongs to your region.',
            ]);
        }
    }

    private function usersInManagerRegion($roles)
    {
        return User::whereIn('role', (array) $roles)
            ->where('region', $this->managerRegion());
    }

    private function techniciansInManagerRegion()
    {
        return $this->usersInManagerRegion('technician')
            ->where('is_approved', true);
    }

    private function faultsInManagerRegion()
    {
        return Fault::with(self::FAULT_RELATIONS)
            ->whereHas('reporter', function ($query) {
                $query->where('region', $this->managerRegion());
            });
    }

    // DASHBOARD (latest faults)
    public function dashboard()
    {
        $faults = $this->faultsInManagerRegion()
            ->where('status', '!=', 'Resolved')
            ->latest()
            ->take(3)
            ->get();
        $technicians = $this->techniciansInManagerRegion()->get();

        $assignedCount = $this->faultsInManagerRegion()->whereNotNull('technician_id')->count();
        $pendingCount = $this->faultsInManagerRegion()->where('status', 'Pending')->count();
        $inProgressCount = $this->faultsInManagerRegion()->where('status', 'In Progress')->count();
        $resolvedCount = $this->faultsInManagerRegion()->where('status', 'Resolved')->count();
        $reportedCount = $this->faultsInManagerRegion()->count();
        $customerCount = $this->usersInManagerRegion('customer')->count();

        return view('manager.dashboard', [
            'faults' => $faults,
            'technicians' => $technicians,
            'reportedCount' => $reportedCount,
            'assignedCount' => $assignedCount,
            'pendingCount' => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'resolvedCount' => $resolvedCount,
            'customerCount' => $customerCount,
            'page' => 'dashboard'
        ]);
    }

    // ALL FAULTS
    public function allFaults()
    {
        $faults = $this->faultsInManagerRegion()->latest()->paginate(20);
        $technicians = $this->techniciansInManagerRegion()->get();

        return view('manager.dashboard', [
            'faults' => $faults,
            'technicians' => $technicians,
            'page' => 'all_faults'
        ]);
    }

    // ASSIGNED FAULTS
    public function assigned()
    {
        $faults = $this->faultsInManagerRegion()->whereNotNull('technician_id')->latest()->paginate(20);
        $technicians = $this->techniciansInManagerRegion()->get();

        return view('manager.dashboard', [
            'faults' => $faults,
            'technicians' => $technicians,
            'page' => 'assigned'
        ]);
    }

    private function faultRateClass(float $rate): string
    {
        return match (true) {
            $rate >= 80 => 'critical',
            $rate >= 50 => 'high',
            $rate >= 30 => 'medium',
            default => 'low',
        };
    }

    private function reportDistricts(Carbon $start, Carbon $end): array
    {
        $endExclusive = $end->copy()->addMonth();
        $region = $this->managerRegion();
        $faults = Fault::query()
            ->with('reporter:id,district,region')
            ->whereHas('reporter', fn ($query) => $query->where('region', $region))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $endExclusive)
            ->get(['id', 'user_id', 'created_at']);
        $users = $this->usersInManagerRegion(['technician', 'customer'])
            ->get(['id', 'district', 'role']);
        $faultsByDistrict = $faults->groupBy(fn (Fault $fault) => $fault->reporter?->district ?: 'Unspecified');
        $districts = $faultsByDistrict->keys()
            ->merge($users->pluck('district')->filter())
            ->unique()->sort()->values();
        $totalFaults = $faults->count();

        return $districts->map(function ($district) use ($faultsByDistrict, $users, $totalFaults) {
            $faultCount = $faultsByDistrict->get($district, collect())->count();
            $rate = $totalFaults ? round(($faultCount / $totalFaults) * 100, 1) : 0;
            $districtUsers = $users->where('district', $district);

            return [
                'district' => $district,
                'customers' => $districtUsers->where('role', 'customer')->count(),
                'technicians' => $districtUsers->where('role', 'technician')->count(),
                'faults' => $faultCount,
                'rate' => $rate,
                'rateClass' => $this->faultRateClass($rate),
            ];
        })->sortByDesc('faults')->values()->map(function ($district, $index) {
            $district['rank'] = $index + 1;
            return $district;
        })->all();
    }

    public function reports(Request $request)
    {
        $request->validate([
            'type' => ['nullable', 'in:monthly,annual,custom'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'custom_start' => ['nullable', 'date_format:Y-m'],
            'custom_end' => ['nullable', 'date_format:Y-m'],
        ]);

        $type = $request->input('type', 'monthly');
        $year = (int) $request->input('year', now()->year);
        $month = $request->input('month', now()->format('Y-m'));
        $customStart = $request->input('custom_start', now()->format('Y-m'));
        $customEnd = $request->input('custom_end', now()->format('Y-m'));
        $start = $type === 'annual'
            ? Carbon::create($year, 1, 1)->startOfMonth()
            : Carbon::createFromFormat('Y-m', $type === 'custom' ? $customStart : $month)->startOfMonth();
        $end = $type === 'annual'
            ? $start->copy()->endOfYear()->startOfMonth()
            : Carbon::createFromFormat('Y-m', $type === 'custom' ? $customEnd : $month)->startOfMonth();
        if ($start->greaterThan($end)) {
            throw ValidationException::withMessages(['custom_end' => 'The end month must be the same as or later than the start month.']);
        }
        $districts = $this->reportDistricts($start, $end);
        $monthlyReports = [];

        if ($type === 'annual') {
            for ($number = 1; $number <= 12; $number++) {
                $period = Carbon::create($year, $number, 1)->startOfMonth();
                $monthDistricts = $this->reportDistricts($period, $period);
                $monthlyReports[] = [
                    'label' => $period->format('F'),
                    'total' => collect($monthDistricts)->sum('faults'),
                    'districts' => $monthDistricts,
                ];
            }
        }

        $reportPeriod = $type === 'annual'
            ? $start->format('Y')
            : ($type === 'custom' ? $start->format('F Y').' – '.$end->format('F Y') : $start->format('F Y'));

        return view('manager.dashboard', compact('type', 'year', 'month', 'customStart', 'customEnd', 'districts', 'monthlyReports', 'reportPeriod') + [
            'reportStart' => $start,
            'reportedCount' => $this->faultsInManagerRegion()->count(),
            'pendingCount' => $this->faultsInManagerRegion()->where('status', 'Pending')->count(),
            'assignedCount' => $this->faultsInManagerRegion()->whereNotNull('technician_id')->count(),
            'inProgressCount' => $this->faultsInManagerRegion()->where('status', 'In Progress')->count(),
            'resolvedCount' => $this->faultsInManagerRegion()->where('status', 'Resolved')->count(),
            'page' => 'reports',
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
        $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $fault = $this->faultsInManagerRegion()->findOrFail($id);
        $technician = $this->techniciansInManagerRegion()->findOrFail($request->technician_id);

        $fault->technician_id = $technician->id;
        $fault->status = 'In Progress';
        $fault->save();

        return back()->with('success', 'Technician assigned successfully');
    }

public function technicians()
{
    $technicians = $this->usersInManagerRegion('technician')
        ->orderBy('is_approved')
        ->latest()
        ->paginate(20);

    return view('manager.dashboard', [
        'technicians' => $technicians,
        'page' => 'technicians'
    ]);
}

public function storeTechnician(Request $request)
{
    $messages = [
        'email.regex' => 'Check your email. Use firstname.lastname@ttcl.co.tz',
        'phone.regex' => 'TTCL numbers must start with 073 and be exactly 10 digits',
    ] + self::PASSWORD_MESSAGES;

    $request->validate([
        'name' => 'required',
        'email' => ['required', 'regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i', 'unique:users,email'],
        'phone' => ['required','regex:/^073\d{7}$/','unique:users,phone'],
        'password' => self::PASSWORD_RULES,
    ], $messages);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'region' => $this->managerRegion(),
        'district' => auth()->user()->district,
        'password' => Hash::make($request->password),
        'role' => 'technician',
        'is_approved' => true
    ]);

    return back()->with('success', 'Technician added');
}

public function updateTechnician(Request $request, $id)
{
    $user = $this->usersInManagerRegion('technician')->findOrFail($id);

    $request->validate([
        'email' => ['required', 'regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i', 'unique:users,email,'.$id],
        'phone' => ['required','regex:/^073\d{7}$/','unique:users,phone,'.$id],
    ], [
        'email.regex' => 'Check your email. Use firstname.lastname@ttcl.co.tz',
        'phone.regex' => 'TTCL numbers must start with 073 and be exactly 10 digits',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->save();

    return back()->with('success', 'Updated');
}

public function deleteTechnician($id)
{
    $this->usersInManagerRegion('technician')->findOrFail($id)->delete();
    return back()->with('success', 'Deleted');
}

public function users()
{
    $users = $this->usersInManagerRegion(['customer', 'technician'])
        ->latest()
        ->get();

    return view('manager.users', [
        'users' => $users,
        'page' => 'users'
    ]);
}

public function storeUser(Request $request)
{
    $messages = [
        'email.regex' => 'Check your email. Use firstname.lastname@ttcl.co.tz',
        'phone.regex' => 'TTCL numbers must start with 073 and be exactly 10 digits',
        'customer_phone.regex' => 'Phone number must be a valid Tanzanian number (e.g., 0712345678 or +255712345678)',
    ] + self::PASSWORD_MESSAGES;

    $rules = [
        'name' => 'required',
        'role' => 'required|in:customer,technician',
        'email' => ['required', 'email', 'unique:users,email'],
        'phone' => ['required', 'unique:users,phone'],
        'district' => 'required',
        'password' => self::PASSWORD_RULES,
    ];

    if ($request->role === 'customer') {
        $rules['phone'] = ['required', 'regex:/^(0\d{9}|\+255\d{9})$/', 'unique:users,phone'];
        $rules['ward'] = 'required';
        $rules['street'] = 'required';
    } else {
        $rules['email'] = ['required', 'regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i', 'unique:users,email'];
        $rules['phone'] = ['required', 'regex:/^073\d{7}$/', 'unique:users,phone'];
    }

    if ($request->role === 'technician') {
        $rules['tech_base'] = 'required';
    }

    $request->validate($rules, $messages);

    $this->validateDistrictForManager($request);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'region' => $this->managerRegion(),
        'district' => $request->district,
        'ward' => $request->role === 'customer' ? $request->ward : null,
        'street' => $request->role === 'customer' ? $request->street : null,
        'tech_base' => $request->role === 'technician' ? $request->tech_base : null,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'is_approved' => true
    ];

    User::create($data);

    return back()->with('success', ucfirst($request->role).' added successfully');
}

public function customers()
{
    $customers = $this->usersInManagerRegion('customer')
        ->orderBy('is_approved')
        ->latest()
        ->paginate(20);

    return view('manager.dashboard', [
        'customers' => $customers,
        'page' => 'customers'
    ]);
}

public function storeCustomer(Request $request)
{
    $request->validate([
        'phone' => ['nullable','regex:/^(0\d{9}|\+255\d{9})$/','unique:users,phone'],
        'password' => self::PASSWORD_RULES,
    ], [
        'phone.regex' => 'Phone number must be a valid Tanzanian number (e.g., 0712345678 or +255712345678)',
    ] + self::PASSWORD_MESSAGES);

    $this->validateDistrictForManager($request);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'region' => $this->managerRegion(),
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
    $user = $this->usersInManagerRegion('customer')->findOrFail($id);

    $request->validate([
        'phone' => ['nullable','regex:/^(0\d{9}|\+255\d{9})$/','unique:users,phone,'.$id],
    ], [
        'phone.regex' => 'Phone number must be a valid Tanzanian number (e.g., 0712345678 or +255712345678)',
    ]);

    $this->validateDistrictForManager($request);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->region = $this->managerRegion();
    $user->district = $request->district;
    $user->ward = $request->ward;
    $user->street = $request->street;
    $user->save();

    return back()->with('success', 'Customer updated');
}

public function approveUser($id)
{
    $user = $this->usersInManagerRegion(['customer', 'technician'])->findOrFail($id);

    $user->is_approved = true;
    $user->save();

    return back()->with('success', 'User approved');
}

public function deactivateUser($id)
{
    $user = $this->usersInManagerRegion(['customer', 'technician'])->findOrFail($id);

    $user->is_approved = false;
    $user->save();

    return back()->with('success', 'User deactivated');
}

public function deleteCustomer($id)
{
    $this->usersInManagerRegion('customer')->findOrFail($id)->delete();
    return back()->with('success', 'Customer deleted');
}
}
