<?php

namespace App\Http\Controllers;

use App\Models\Fault;
use App\Models\ManagerReportComment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    private function trendData(Carbon $startMonth, Carbon $endMonth): array
    {
        $endExclusive = $endMonth->copy()->addMonth();
        $countsByMonth = Fault::query()
            ->where('created_at', '>=', $startMonth)
            ->where('created_at', '<', $endExclusive)
            ->get(['created_at'])
            ->countBy(fn (Fault $fault) => $fault->created_at->format('Y-m'));

        $months = collect();
        $month = $startMonth->copy();

        while ($month->lessThanOrEqualTo($endMonth)) {
            $monthKey = $month->format('Y-m');

            $months->push([
                'label' => $month->format('M Y'),
                'count' => (int) ($countsByMonth[$monthKey] ?? 0),
            ]);

            $month->addMonth();
        }

        $max = max($months->max('count'), 1);

        $labelEvery = max(1, (int) ceil($months->count() / 12));

        return $months->values()->map(function ($month, $index) use ($max, $labelEvery) {
            $month['height'] = max(8, round(($month['count'] / $max) * 100));
            $month['showLabel'] = $index % $labelEvery === 0 || $index === $months->count() - 1;
            return $month;
        })->all();
    }

    public function dashboard(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'trend_start' => ['nullable', 'date_format:Y-m'],
            'trend_end' => ['nullable', 'date_format:Y-m'],
        ]);

        $startMonth = $request->filled('trend_start')
            ? Carbon::createFromFormat('Y-m', $request->trend_start)->startOfMonth()
            : Carbon::today()->startOfMonth()->subMonths(11);
        $endMonth = $request->filled('trend_end')
            ? Carbon::createFromFormat('Y-m', $request->trend_end)->startOfMonth()
            : Carbon::today()->startOfMonth();

        if ($startMonth->greaterThan($endMonth)) {
            throw ValidationException::withMessages([
                'trend_end' => 'The end month must be the same as or later than the start month.',
            ]);
        }

        $userCounts = User::selectRaw('role, COUNT(*) as total')
            ->whereIn('role', ['manager', 'technician', 'customer'])
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.dashboard', [
            'managerCount' => (int) ($userCounts['manager'] ?? 0),
            'technicianCount' => (int) ($userCounts['technician'] ?? 0),
            'customerCount' => (int) ($userCounts['customer'] ?? 0),
            'faultCount' => Fault::count(),
            'trendData' => $this->trendData($startMonth, $endMonth),
            'trendStart' => $startMonth->format('Y-m'),
            'trendEnd' => $endMonth->format('Y-m'),
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
            'managers' => User::where('role', 'manager')->orderBy('is_approved')->latest()->paginate(20),
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
            'technicians' => User::where('role', 'technician')->latest()->paginate(20),
            'page' => 'technicians',
        ]);
    }

    public function customers()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'customers' => User::where('role', 'customer')->latest()->paginate(20),
            'page' => 'customers',
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

    private function reportRegions(Carbon $start, Carbon $end): array
    {
        $endExclusive = $end->copy()->addMonth();
        $faults = Fault::query()
            ->with('reporter:id,region')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $endExclusive)
            ->get(['id', 'user_id', 'created_at']);
        $totalFaults = $faults->count();
        $faultsByRegion = $faults->groupBy(fn (Fault $fault) => $fault->reporter?->region ?: 'Unspecified');
        $users = User::query()
            ->whereIn('role', ['manager', 'technician', 'customer'])
            ->whereNotNull('region')
            ->get(['id', 'name', 'region', 'role']);
        $userRegions = $users->pluck('region')->filter()->unique();
        $regions = $faultsByRegion->keys()->merge($userRegions)->unique()->sort()->values();

        return $regions->map(function ($region) use ($faultsByRegion, $totalFaults, $users) {
            $faultCount = $faultsByRegion->get($region, collect())->count();
            $rate = $totalFaults ? round(($faultCount / $totalFaults) * 100, 1) : 0;
            $regionalUsers = $users->where('region', $region);

            return [
                'region' => $region,
                'faults' => $faultCount,
                'rate' => $rate,
                'rateClass' => $this->faultRateClass($rate),
                'managers' => $regionalUsers->where('role', 'manager')->values(),
                'technicians' => $regionalUsers->where('role', 'technician')->count(),
                'customers' => $regionalUsers->where('role', 'customer')->count(),
            ];
        })->sortByDesc('faults')->values()->map(function ($region, $index) {
            $region['rank'] = $index + 1;
            return $region;
        })->all();
    }

    public function reports(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'type' => ['nullable', 'in:monthly,annual'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $type = $request->input('type', 'monthly');
        $year = (int) $request->input('year', now()->year);
        $month = $request->input('month', now()->format('Y-m'));
        $start = $type === 'annual'
            ? Carbon::create($year, 1, 1)->startOfMonth()
            : Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $type === 'annual' ? $start->copy()->endOfYear()->startOfMonth() : $start->copy();
        $regions = $this->reportRegions($start, $end);

        $monthlyReports = [];
        $evaluations = [];
        $comments = collect();
        if ($type === 'annual') {
            for ($number = 1; $number <= 12; $number++) {
                $period = Carbon::create($year, $number, 1);
                $monthlyReports[] = [
                    'label' => $period->format('F'),
                    'total' => Fault::whereBetween('created_at', [$period->copy()->startOfMonth(), $period->copy()->endOfMonth()])->count(),
                    'regions' => $this->reportRegions($period, $period),
                ];
            }
            foreach ($regions as $region) {
                $firstHalf = collect($monthlyReports)->take(6)->sum(fn ($report) => collect($report['regions'])->firstWhere('region', $region['region'])['faults'] ?? 0);
                $secondHalf = collect($monthlyReports)->skip(6)->sum(fn ($report) => collect($report['regions'])->firstWhere('region', $region['region'])['faults'] ?? 0);
                $change = $firstHalf ? round((($secondHalf - $firstHalf) / $firstHalf) * 100, 1) : ($secondHalf ? 100 : 0);
                $status = $change > 10 ? 'Growing' : ($change < -10 ? 'Staging' : 'Maintaining');
                $evaluations[$region['region']] = compact('firstHalf', 'secondHalf', 'change', 'status');
            }
            $comments = ManagerReportComment::where('year', $year)->pluck('comment', 'manager_id');
        }

        return view('admin.dashboard', compact('type', 'year', 'month', 'regions', 'monthlyReports', 'evaluations', 'comments') + [
            'reportStart' => $start,
            'reportEnd' => $end,
            'page' => 'reports',
        ]);
    }

    public function saveReportComment(Request $request, User $manager)
    {
        $this->authorizeAdmin();
        abort_unless($manager->role === 'manager', 404);
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
        ManagerReportComment::updateOrCreate(
            ['manager_id' => $manager->id, 'year' => $data['year']],
            ['comment' => $data['comment']]
        );

        return back()->with('success', 'Manager comment saved.');
    }

    public function account()
    {
        $this->authorizeAdmin();

        return view('admin.dashboard', [
            'page' => 'account',
        ]);
    }
}
