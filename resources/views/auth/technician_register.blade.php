@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2>Technician Registration</h2>

    <form method="POST" action="/register/technician">
        @csrf

        <input type="text" name="name" placeholder="Full Names" required><br>

        <input type="email" name="email" placeholder="@ttcl.co.tz only" required><br>

        <input type="text" name="phone" placeholder="TTCL Contact" required><br>

        <input type="text" name="tech_base" placeholder="Tech Base" required><br>

        <input type="text" name="region" placeholder="Region" required><br>

        <input type="text" name="district" placeholder="District" required><br>

        <input type="password" name="password" placeholder="Password" required><br>

        <input type="password" name="password_confirmation" placeholder="Confirm Password" required><br>

        <button type="submit">Register</button>
    </form>

    <p style="margin-top:15px;">
        I have an account? 
        <a href="/login">Login</a>
    </p>

</div>

@endsection