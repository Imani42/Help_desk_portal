@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2>Manager Registration</h2>

    <form method="POST" action="/register/manager">
        @csrf

        <input type="text" name="name" placeholder="Full Names"><br>

        <input type="email" name="email" placeholder="@ttcl.co.tz only"><br>

        <input type="text" name="phone" placeholder="TTCL Contact"><br>

        <input type="text" name="region" placeholder="Region"><br>

        <input type="text" name="district" placeholder="District"><br>

        <input type="password" name="password" placeholder="Password"><br>

        <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>

        <button type="submit">Register</button>
    </form>

    <p style="margin-top:15px;">
        I have an account? 
        <a href="/login">Login</a>
    </p>

</div>

@endsection