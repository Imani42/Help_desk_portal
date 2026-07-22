<?php

namespace App\Http\Controllers;

use App\Models\Fault;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    private const PASSWORD_RULES = [
        'required',
        'min:8',
        'regex:/^(?=(?:.*\d){2,})(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/',
    ];

    private const PASSWORD_MESSAGES = [
        'password.regex' => 'Password must include at least one capital letter, at least two digits, and at least one special character.',
    ];

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user() && auth()->user()->role === 'admin', 403);
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

    private function managerRules(?int $ignoreId = null): array
    {
        $emailRule = 'unique:users,email';
        $phoneRule = 'unique:users,phone';

        if ($ignoreId) {
            $emailRule .= ',' . $ignoreId;
            $phoneRule .= ',' . $ignoreId;
        }

        return [
            'name' => 'required',
            'email' => ['required', 'regex:/^[a-z]+[a-z0-9]*\.[a-z]+[a-z0-9]*@ttcl\.co\.tz$/i', $emailRule],
            'phone' => ['required', 'regex:/^073\d{7}$/', $phoneRule],
            'region' => 'required',
            'district' => 'required',
        ];
    }

    private function managerMessages(): array
    {
        return [
            'email.regex' => 'Check your email. Use firstname.lastname@ttcl.co.tz',
            'phone.regex' => 'TTCL numbers must start with 073 and be exactly 10 digits',
        ] + self::PASSWORD_MESSAGES;
    }

    private function trendData(): array
    {
        $startDate = Carbon::today()->subDays(6);
        $countsByDate = Fault::selectRaw('DATE(created_at) as fault_date, COUNT(*) as total')
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'fault_date');

        $days = collect(range(6, 0))->map(function ($daysAgo) use ($countsByDate) {
            $date = Carbon::today()->subDays($daysAgo);
            $dateKey = $date->toDateString();

            return [
                'label' => $date->format('d M'),
                'count' => (int) ($countsByDate[$dateKey] ?? 0),
            ];
        });

        $max = max($days->max('count'), 1);

        return $days->map(function ($day) use ($max) {
            $day['height'] = max(8, round(($day['count'] / $max) * 100));
            return $day;
        })->all();
    }

    public function dashboard()
    {
        $this->authorizeAdmin();
        $userCounts = User::selectRaw('role, COUNT(*) as total')
            ->whereIn('role', ['manager', 'technician', 'customer'])
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.dashboard', [
            'managerCount' => (int) ($userCounts['manager'] ?? 0),
            'technicianCount' => (int) ($userCounts['technician'] ?? 0),
            'customerCount' => (int) ($userCounts['customer'] ?? 0),
            'faultCount' => Fault::count(),
            'trendData' => $this->trendData(),
            'page' => 'dashboard',
        ]);
    }

    public function addManager()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'page' => 'add_manager',
        ]);
    }

    public function storeManager(Request $request)
    {
        $this->authorizeAdmin();

        $rules = $this->managerRules();
        $rules['password'] = self::PASSWORD_RULES;

        $request->validate($rules, $this->managerMessages());

        $this->validateRegionDistrict($request);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'region' => $request->region,
            'district' => $request->district,
            'password' => Hash::make($request->password),
            'role' => 'manager',
            'is_approved' => true,
        ]);

        return redirect('/admin/managers')->with('success', 'Manager added');
    }

    public function managers()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'managers' => User::where('role', 'manager')->orderBy('is_approved')->latest()->get(),
            'page' => 'managers',
        ]);
    }

    public function approveManager($id)
    {
        $this->authorizeAdmin();

        $manager = User::where('role', 'manager')->findOrFail($id);
        $manager->is_approved = true;
        $manager->save();

        return back()->with('success', 'Manager approved');
    }

    public function deactivateManager($id)
    {
        $this->authorizeAdmin();

        $manager = User::where('role', 'manager')->findOrFail($id);
        $manager->is_approved = false;
        $manager->save();

        return back()->with('success', 'Manager deactivated');
    }

    public function deleteManager($id)
    {
        $this->authorizeAdmin();

        User::where('role', 'manager')->findOrFail($id)->delete();

        return back()->with('success', 'Manager deleted');
    }

    public function technicians()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'technicians' => User::where('role', 'technician')->latest()->get(),
            'page' => 'technicians',
        ]);
    }

    public function customers()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'customers' => User::where('role', 'customer')->latest()->get(),
            'page' => 'customers',
        ]);
    }

    public function account()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'page' => 'account',
        ]);
    }
}
