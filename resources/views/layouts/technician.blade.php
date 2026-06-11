<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<div class="dashboard">

    <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle">
    <label for="sidebar-toggle" class="menu-button">Menu</label>
    <label for="sidebar-toggle" class="sidebar-backdrop"></label>

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="logo-box">
            <img src="{{ asset('images/ttcl.png') }}" alt="TTCL Logo">
            <h2>TTCL</h2>
        </div>

        <a href="/technician/dashboard" class="{{ $page=='dashboard' ? 'active' : '' }}">Dashboard</a>
        <a href="/technician/assigned" class="{{ $page=='assigned' ? 'active' : '' }}">Assigned Faults</a>
        <a href="/technician/completed" class="{{ $page=='completed' ? 'active' : '' }}">Completed</a>
        <a href="/technician/account" class="{{ $page=='account' ? 'active' : '' }}">Account</a>

        <a href="/logout" class="logout">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <h3>TTCL Fault Portal</h3>
            <div class="topbar-user">
                @if(auth()->user()->profile_photo)
                    <img class="user-avatar" src="{{ asset(auth()->user()->profile_photo) }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="user-avatar user-avatar-empty">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif
                <span class="user-label">Technician: {{ auth()->user()->name }}</span>
            </div>
        </div>

        <div class="content">
            @yield('content')
        </div>

    </div>

</div>

</body>
</html>
