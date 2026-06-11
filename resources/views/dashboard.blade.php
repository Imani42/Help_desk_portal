@extends('layouts.app')

@section('content')

<div class="container">

    <!-- TTCL LOGO -->
    <div class="header">
        <img src="{{ asset('images/ttcl_logo.png') }}" alt="TTCL Logo" class="logo">
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

        <h2>TTCL Help Desk Portal</h2>

        <p>
            Welcome to the TTCL Help Desk Portal. This platform enables customers,
            managers, and technicians to access support services, report faults,
            and track service requests efficiently.
        </p>

        <button onclick="showRegister()">REGISTER HERE</button>

        <div id="registerOptions" style="display:none; margin-top:15px;">

            <p><strong>Register As:</strong></p>

            <a href="/register/customer">Customer</a><br><br>

            <a href="/register/manager">Manager</a><br><br>

            <a href="/register/technician">Technician</a><br><br>

            <a href="/login">Login</a>

        </div>

    </div>

</div>

<script>
function showRegister() {
    let options = document.getElementById('registerOptions');

    if (options.style.display === 'none') {
        options.style.display = 'block';
    } else {
        options.style.display = 'none';
    }
}
</script>

@endsection