@extends('layouts.app')

@section('content')

<div class="container {{ !empty($showRegisterOptions) ? 'register-landing' : '' }}">

    <!-- LEFT SIDE -->
    <div class="left landing-media">
        <div class="header">
            <img src="{{ asset('images/ttcl_logo.png') }}" alt="TTCL Logo" class="logo">
        </div>

        @include('partials.ttcl-media')
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

        <h2>TTCL Help Desk Portal</h2>

        <p>
            Welcome to the TTCL Help Desk Portal. This platform enables customers,
            managers, and technicians to access support services, report faults,
            and track service requests efficiently.
        </p>

        @if(empty($showRegisterOptions))
            <button onclick="showRegister()">REGISTER HERE</button>
        @endif

        <div id="registerOptions" class="register-options" style="{{ !empty($showRegisterOptions) ? 'display:block;' : 'display:none;' }}">

            <p><strong>Register As:</strong></p>

            <a href="/register/customer">Customer</a>

            <a href="/register/manager">Manager</a>

            <a href="/register/technician">Technician</a>

            @if(empty($adminExists))
                <a href="/register/admin">Admin</a>
            @endif

            <a href="/login">Login</a>

        </div>

    </div>

</div>

<script>
function showRegister() {
    let options = document.getElementById('registerOptions');

    options.style.display = options.style.display === 'block' ? 'none' : 'block';
}
</script>

@endsection
