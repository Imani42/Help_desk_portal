@extends('layouts.admin')

@section('content')

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert error">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@if($page == 'dashboard')
<div class="manager-dashboard-head">
    <div>
        <h1>Admin Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}</p>
    </div>
    <div class="dashboard-date">{{ now()->format('d M Y') }}</div>
</div>

<div class="stats-grid admin-stats-grid">
    <div class="stat-card stat-assigned">
        <div class="stat-number">{{ $managerCount ?? 0 }}</div>
        <div class="stat-label">Managers</div>
    </div>
    <div class="stat-card stat-inprogress">
        <div class="stat-number">{{ $technicianCount ?? 0 }}</div>
        <div class="stat-label">Technicians</div>
    </div>
    <div class="stat-card stat-resolved">
        <div class="stat-number">{{ $customerCount ?? 0 }}</div>
        <div class="stat-label">Customers</div>
    </div>
    <div class="stat-card stat-reported">
        <div class="stat-number">{{ $faultCount ?? 0 }}</div>
        <div class="stat-label">Faults</div>
    </div>
</div>

<div class="dashboard-panel">
    <div class="trend-panel-heading">
        <div>
            <h3>Fault Trends</h3>
            <p>Faults reported each month</p>
        </div>
        <form method="GET" action="/admin/dashboard" class="trend-filter">
            <label>
                From
                <input type="month" name="trend_start" value="{{ $trendStart }}">
            </label>
            <label>
                To
                <input type="month" name="trend_end" value="{{ $trendEnd }}">
            </label>
            <button type="submit">Apply</button>
        </form>
    </div>
    @error('trend_end')
        <p class="trend-filter-error">{{ $message }}</p>
    @enderror
    <div class="fault-histogram" style="grid-template-columns:repeat({{ count($trendData) }}, minmax(0, 1fr));">
        @foreach($trendData as $month)
            <div class="histogram-column">
                <span>{{ $month['count'] }}</span>
                <div class="histogram-bar" style="height:{{ $month['height'] }}%" title="{{ $month['count'] }} faults in {{ $month['label'] }}"></div>
                <small class="{{ $month['showLabel'] ? '' : 'histogram-label-spacer' }}">{{ $month['showLabel'] ? $month['label'] : '' }}</small>
            </div>
        @endforeach
    </div>
</div>
@endif

@if($page == 'add_manager')
<div class="portal-form-wrap">
    <div class="card portal-form-card">
        <h3>Add Manager</h3>

        <form method="POST" action="/admin/managers/store">
            @csrf

            <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
            <input type="text" name="phone" placeholder="Phone" value="{{ old('phone') }}" required>
            @include('partials.location-fields')

            <div class="password-field">
                <input type="password" name="password" placeholder="Password" required>
                <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
            </div>
            <p class="password-policy">Use at least 8 characters with 1 capital letter, 2 digits, and 1 special character.</p>

            <button class="add-action-button">Add Manager</button>
        </form>
    </div>
</div>
@endif

@if($page == 'managers')
<div class="card">
    <h3>Managers</h3>

    <div class="user-list">
    @forelse($managers as $manager)
        <div class="technician-row user-click-row" style="cursor:pointer; flex-wrap:wrap;">
            <div>
                <strong>{{ $manager->name }}</strong>
                @if($manager->is_approved)
                    <span class="approval-badge approved">Approved</span>
                @else
                    <span class="approval-badge pending-approval">Pending approval</span>
                @endif
            </div>

            <div class="technician-actions" onclick="event.stopPropagation()">
                @if(! $manager->is_approved)
                    <form method="POST" action="/admin/manager/approve/{{ $manager->id }}" class="inline-action-form">
                        @csrf
                        <button type="submit" class="approve-action">Approve</button>
                    </form>
                @else
                    <form method="POST" action="/admin/manager/deactivate/{{ $manager->id }}" class="inline-action-form">
                        @csrf
                        <button type="submit" class="deactivate-action">Deactivate</button>
                    </form>
                @endif
                <form method="POST" action="/admin/manager/delete/{{ $manager->id }}" class="inline-action-form" onsubmit="return confirm('Delete this manager?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger-action">Delete</button>
                </form>
            </div>

            <div class="user-inline-details" style="display:none; width:100%; margin-top:12px; padding:12px; background:#fff; border:1px solid #ddd; border-radius:8px;">
                <p><strong>Email:</strong> {{ $manager->email }}</p>
                <p><strong>Phone:</strong> {{ $manager->phone }}</p>
                <p><strong>Region:</strong> {{ $manager->region ?: 'Not provided' }}</p>
                <p><strong>District:</strong> {{ $manager->district ?: 'Not provided' }}</p>
            </div>
        </div>
    @empty
        <p>No managers found</p>
    @endforelse
    </div>
</div>
@endif

@if($page == 'technicians')
<div class="card">
    <h3>Technicians</h3>

    <div class="user-list">
    @forelse($technicians as $tech)
        <div class="technician-row user-click-row" style="cursor:pointer; flex-wrap:wrap;">
            <div>
                <strong>{{ $tech->name }}</strong>
                @if($tech->is_approved)
                    <span class="approval-badge approved">Approved</span>
                @else
                    <span class="approval-badge pending-approval">Pending approval</span>
                @endif
            </div>

            <div class="user-inline-details" style="display:none; width:100%; margin-top:12px; padding:12px; background:#fff; border:1px solid #ddd; border-radius:8px;">
                <p><strong>Email:</strong> {{ $tech->email }}</p>
                <p><strong>Phone:</strong> {{ $tech->phone }}</p>
                <p><strong>Region:</strong> {{ $tech->region ?: 'Not provided' }}</p>
                <p><strong>District:</strong> {{ $tech->district ?: 'Not provided' }}</p>
                <p><strong>Base:</strong> {{ $tech->tech_base ?: 'Not provided' }}</p>
            </div>
        </div>
    @empty
        <p>No technicians found</p>
    @endforelse
    </div>
</div>
@endif

@if($page == 'customers')
<div class="card">
    <h3>Customers</h3>

    <div class="user-list">
    @forelse($customers as $customer)
        <div class="technician-row user-click-row" style="cursor:pointer; flex-wrap:wrap;">
            <div>
                <strong>{{ $customer->name }}</strong>
                @if($customer->is_approved)
                    <span class="approval-badge approved">Approved</span>
                @else
                    <span class="approval-badge pending-approval">Pending approval</span>
                @endif
            </div>

            <div class="user-inline-details" style="display:none; width:100%; margin-top:12px; padding:12px; background:#fff; border:1px solid #ddd; border-radius:8px;">
                <p><strong>Email:</strong> {{ $customer->email }}</p>
                <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                <p><strong>Region:</strong> {{ $customer->region ?: 'Not provided' }}</p>
                <p><strong>District:</strong> {{ $customer->district ?: 'Not provided' }}</p>
                <p><strong>Ward:</strong> {{ $customer->ward ?: 'Not provided' }}</p>
                <p><strong>Street:</strong> {{ $customer->street ?: 'Not provided' }}</p>
            </div>
        </div>
    @empty
        <p>No customers found</p>
    @endforelse
    </div>
</div>
@endif

@if($page == 'account')
@include('account.manage')
@endif

@if($page == 'reports')
<div class="report-tools no-print">
    <div>
        <h1>Regional Reports</h1>
        <p>Review regional fault performance and save the finished report as a PDF.</p>
    </div>
    <button type="button" class="report-print-button" onclick="window.print()">Print Report</button>
</div>

<div class="card report-filter no-print">
    <form method="GET" action="/admin/reports">
        <label>Report type
            <select name="type" id="report-type">
                <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>Monthly report</option>
                <option value="annual" {{ $type === 'annual' ? 'selected' : '' }}>Annual report</option>
                <option value="custom" {{ $type === 'custom' ? 'selected' : '' }}>Custom period</option>
            </select>
        </label>
        <label class="month-input">Month
            <input type="month" name="month" value="{{ $month }}">
        </label>
        <label class="year-input">Year
            <input type="number" name="year" min="2000" max="2100" value="{{ $year }}">
        </label>
        <label class="custom-start-input">Start month
            <input type="month" name="custom_start" value="{{ $customStart }}">
        </label>
        <label class="custom-end-input">End month
            <input type="month" name="custom_end" value="{{ $customEnd }}">
        </label>
        <button type="submit">Generate report</button>
    </form>
</div>

<section class="report-document">
    <div class="report-heading">
        <div>
            <img src="{{ asset('images/ttcl.png') }}" alt="TTCL" class="report-logo">
            <h2>TTCL Fault Performance Report</h2>
            <p>{{ ucfirst($type) }} report: {{ $reportPeriod }}</p>
        </div>
        <p class="report-generated">Generated {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <p class="report-note">Regions are grouped from highest to lowest fault rate. Fault rate is each region's share of faults reported in this period.</p>

    @php($rateGroups = [
        '80–100% of faults' => 'critical',
        '50–79% of faults' => 'high',
        '30–49% of faults' => 'medium',
        '0–29% of faults' => 'low',
    ])
    @if(collect($regions)->isEmpty())
        <p class="report-empty">No regional records are available for this period.</p>
    @else
        @foreach($rateGroups as $groupLabel => $groupClass)
            @php($groupRegions = collect($regions)->where('rateClass', $groupClass))
            @if($groupRegions->isNotEmpty())
                <section class="report-rate-group {{ $groupClass }}">
                    <h3>{{ $groupLabel }}</h3>
                    <div class="report-table-wrap">
                        <table class="regional-report-table">
                            <thead><tr><th>Rank</th><th>Region</th><th>Faults</th><th>Fault rate</th><th>Managers</th><th>Technicians</th><th>Customers</th></tr></thead>
                            <tbody>@foreach($groupRegions as $region)
                                <tr>
                                    <td>#{{ $region['rank'] }}</td>
                                    <td><strong>{{ $region['region'] }}</strong></td>
                                    <td>{{ $region['faults'] }}</td>
                                    <td><span class="rate-badge {{ $region['rateClass'] }}">{{ number_format($region['rate'], 1) }}%</span></td>
                                    <td>{{ $region['managers']->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td>{{ $region['technicians'] }}</td><td>{{ $region['customers'] }}</td>
                                </tr>
                            @endforeach</tbody>
                        </table>
                    </div>
                </section>
            @endif
        @endforeach
    @endif

    @if($type === 'annual')
        <h3 class="report-section-title">Monthly fault reports</h3>
        <div class="report-table-wrap">
            <table class="annual-month-table">
                <thead><tr><th>Month</th><th>Total faults</th><th>Regional ranking</th></tr></thead>
                <tbody>@foreach($monthlyReports as $monthlyReport)
                    <tr><td>{{ $monthlyReport['label'] }}</td><td>{{ $monthlyReport['total'] }}</td><td>{{ collect($monthlyReport['regions'])->map(fn ($region) => $region['region'].' ('.$region['faults'].')')->join(', ') ?: 'No faults reported' }}</td></tr>
                @endforeach</tbody>
            </table>
        </div>

        <h3 class="report-section-title">Annual regional evaluation</h3>
        <div class="report-table-wrap">
            <table class="regional-report-table">
                <thead><tr><th>Region</th><th>Jan–Jun</th><th>Jul–Dec</th><th>Change</th><th>Evaluation</th></tr></thead>
                <tbody>@foreach($regions as $region)
                    @php($evaluation = $evaluations[$region['region']])
                    <tr><td><strong>{{ $region['region'] }}</strong></td><td>{{ $evaluation['firstHalf'] }}</td><td>{{ $evaluation['secondHalf'] }}</td><td>{{ $evaluation['change'] > 0 ? '+' : '' }}{{ $evaluation['change'] }}%</td><td><span class="evaluation {{ strtolower($evaluation['status']) }}">{{ $evaluation['status'] }}</span></td></tr>
                @endforeach</tbody>
            </table>
        </div>

        <div class="manager-comments">
            <h3 class="report-section-title">Manager comments</h3>
            <p>Record an annual comment for each manager. Comments are saved with this report year.</p>
            @forelse(collect($regions)->flatMap(fn ($region) => $region['managers'])->unique('id') as $manager)
                <form method="POST" action="/admin/reports/comments/{{ $manager->id }}" class="manager-comment-form">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <label><strong>{{ $manager->name }}</strong><small>{{ $manager->region }}</small></label>
                    <textarea name="comment" rows="3" placeholder="Comment for this manager">{{ $comments->get($manager->id) }}</textarea>
                    <button type="submit">Save comment</button>
                </form>
            @empty
                <p>No managers have been assigned to a region.</p>
            @endforelse
        </div>
    @endif
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('report-type');
    if (!type) return;
    const setVisibility = () => {
        document.querySelector('.month-input').style.display = type.value === 'monthly' ? 'grid' : 'none';
        document.querySelector('.year-input').style.display = type.value === 'annual' ? 'grid' : 'none';
        document.querySelector('.custom-start-input').style.display = type.value === 'custom' ? 'grid' : 'none';
        document.querySelector('.custom-end-input').style.display = type.value === 'custom' ? 'grid' : 'none';
    };
    type.addEventListener('change', setVisibility); setVisibility();
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.user-click-row').forEach(function(row){
        row.addEventListener('click', function(){
            row.classList.toggle('selected');
            var details = row.querySelector('.user-inline-details');
            if (details) {
                details.style.display = details.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
});
</script>

@if($page === 'managers' && $managers->hasPages())
    <div class="pagination">{{ $managers->onEachSide(1)->links() }}</div>
@endif

@if($page === 'technicians' && $technicians->hasPages())
    <div class="pagination">{{ $technicians->onEachSide(1)->links() }}</div>
@endif

@if($page === 'customers' && $customers->hasPages())
    <div class="pagination">{{ $customers->onEachSide(1)->links() }}</div>
@endif

@endsection
