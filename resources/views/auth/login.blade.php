@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2>Login</h2>

    <form method="POST" action="/login">
        @csrf

        <input type="email" name="email" placeholder="Email" required><br>

        <input type="password" name="password" placeholder="Password" required><br>

        <button type="submit">Login</button>
    </form>

    <p>
        Don't have account? 
        <a href="/">Register</a>
    </p>

</div>

@endsection