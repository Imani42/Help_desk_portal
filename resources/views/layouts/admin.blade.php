<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>
<body>

<div class="dashboard">
    <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle">
    <label for="sidebar-toggle" class="menu-button" aria-label="Toggle sidebar" title="Toggle sidebar">
        <span></span><span></span><span></span>
    </label>
    <label for="sidebar-toggle" class="sidebar-backdrop"></label>

    <div class="sidebar">
        <div class="logo-box">
            <img src="{{ asset('images/ttcl.png') }}" alt="TTCL Logo">
           
            <p>Corporation that connects</p>
        </div>

        <a href="/admin/dashboard" class="{{ $page=='dashboard' ? 'active' : '' }}">Dashboard</a>
        <a href="/admin/managers/add" class="{{ $page=='add_manager' ? 'active' : '' }}">Add Manager</a>
        <a href="/admin/managers" class="{{ $page=='managers' ? 'active' : '' }}">Managers</a>
        <a href="/admin/technicians" class="{{ $page=='technicians' ? 'active' : '' }}">Technicians</a>
        <a href="/admin/customers" class="{{ $page=='customers' ? 'active' : '' }}">Customers</a>
        <a href="/admin/reports" class="{{ $page=='reports' ? 'active' : '' }}">Reports</a>
        <a href="/admin/account" class="{{ $page=='account' ? 'active' : '' }}">Account</a>
        <a href="/logout" class="logout">Logout</a>
    </div>

    <div class="main">
        <div class="topbar">
            <h3>TTCL Help Desk Portal</h3>
            <a href="/admin/account" class="topbar-user">
                @if(auth()->user()->profile_photo)
                    <img class="user-avatar" src="{{ asset(auth()->user()->profile_photo) }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="user-avatar user-avatar-empty">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif
                <span class="user-label">Admin: {{ auth()->user()->name }}</span>
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
