<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>
<body>

<div class="dashboard">

    <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle">
    <label for="sidebar-toggle" class="menu-button" aria-label="Toggle sidebar" title="Toggle sidebar">
        <span></span><span></span><span></span>
    </label>
    <label for="sidebar-toggle" class="sidebar-backdrop"></label>

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="logo-box">
            <img src="{{ asset('images/ttcl.png') }}" alt="TTCL Logo">
        
            <p>Corporation that connects</p>
        </div>

        <a href="/manager/dashboard" class="{{ $page=='dashboard' ? 'active' : '' }}">Dashboard</a>
        <a href="/manager/faults" class="{{ $page=='all_faults' ? 'active' : '' }}">All Faults</a>
        <a href="/manager/assigned" class="{{ $page=='assigned' ? 'active' : '' }}">Assigned</a>
        <a href="/manager/users" class="{{ $page=='users' ? 'active' : '' }}">Add Users</a>
        <a href="/manager/technicians" class="{{ $page=='technicians' ? 'active' : '' }}">Technicians</a>
        <a href="/manager/customers" class="{{ $page=='customers' ? 'active' : '' }}">Customers</a>
        <a href="/manager/reports" class="{{ $page=='reports' ? 'active' : '' }}">Reports</a>
        <a href="/manager/account" class="{{ $page=='account' ? 'active' : '' }}">Account</a>

        <a href="/logout" class="logout">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <h3>TTCL Help Desk Portal</h3>
            <a href="/manager/account" class="topbar-user">
                @if(auth()->user()->profile_photo)
                    <img class="user-avatar" src="{{ asset(auth()->user()->profile_photo) }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="user-avatar user-avatar-empty">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif
                <span class="user-label">Manager: {{ auth()->user()->name }}</span>
            </a>
        </div>

        <div class="content">
            @yield('content')
        </div>

    </div>

</div>

<script>
    window.TANZANIA_LOCATIONS = @json(config('tanzania_locations.regions'));
</script>
<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
