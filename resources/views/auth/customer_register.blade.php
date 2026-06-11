@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2>Customer Registration</h2>

    <form method="POST" action="/register/customer">
        @csrf

        <input type="text" name="name" placeholder="Full Names" required><br>

        <input type="email" name="email" placeholder="example@gmail.com" required><br>

        <input type="text" name="phone" placeholder="e.g 0712345678 or +255712345678" required><br>

        <input type="text" name="region" placeholder="Region" required><br>

        <input type="text" name="district" placeholder="District" required><br>

        <input type="text" name="ward" placeholder="Ward" required><br>

        <input type="text" name="street" placeholder="Street" required><br>

        <input type="password" name="password" placeholder="Password" required><br>

        <input type="password" name="password_confirmation" placeholder="Confirm Password" required><br>

        <button type="submit">Register</button>

    </form>

    <p>
        I have an account <a href="/login">Login</a>
    </p>

</div>

@endsection