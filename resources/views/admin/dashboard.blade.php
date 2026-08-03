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

            <button>Add Manager</button>
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
