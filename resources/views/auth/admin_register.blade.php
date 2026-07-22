@extends('layouts.app')

@section('content')

<div class="form-container">
    <h2>Admin Registration</h2>

    @include('partials.form-errors')

    <form method="POST" action="/register/admin">
        @csrf

        <input type="text" name="name" placeholder="Full Name" value="{{ old('name') }}" maxlength="255" required><br>
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" maxlength="255" required><br>
        @error('email')
            <div class="field-error">{{ $message }}</div>
        @enderror
        <input type="text" name="phone" placeholder="Phone" value="{{ old('phone') }}" maxlength="20" required><br>
        @error('phone')
            <div class="field-error">{{ $message }}</div>
        @enderror

        <div class="password-field">
            <input type="password" name="password" placeholder="Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
        </div>

        <div class="password-field">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
        </div>

        <p class="password-policy">Use at least 8 characters with 1 capital letter, 2 digits, and 1 special character.</p>

        <button type="submit">Register Admin</button>
    </form>

    <p>
        I have an account <a href="/login">Login</a>
    </p>
</div>

@endsection
