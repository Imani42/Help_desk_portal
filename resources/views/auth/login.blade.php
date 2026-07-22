@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2>Login</h2>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @include('partials.form-errors')

    <form method="POST" action="/login">
        @csrf

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" maxlength="255" required><br>

        <div class="password-field">
            <input type="password" name="password" placeholder="Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
        </div>

        <button type="submit">Login</button>
    </form>

    <p>
        Don't have account? 
        <a href="/">Register</a>
    </p>

</div>

@endsection
