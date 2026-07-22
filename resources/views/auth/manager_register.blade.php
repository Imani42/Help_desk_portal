@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2>Manager Registration</h2>

    @include('partials.form-errors')

    <form method="POST" action="/register/manager">
        @csrf

        <input type="text" name="name" placeholder="Full Names" value="{{ old('name') }}" maxlength="255" required><br>

        <input type="email" name="email" placeholder="@ttcl.co.tz only" value="{{ old('email') }}" maxlength="255" required><br>
        @error('email')
            <div class="field-error">{{ $message }}</div>
        @enderror

        <input type="text" name="phone" placeholder="TTCL Contact (07XXXXXXX)" value="{{ old('phone') }}" maxlength="20" required><br>
        @error('phone')
            <div class="field-error">{{ $message }}</div>
        @enderror

        @include('partials.location-fields')

        <div class="password-field">
            <input type="password" name="password" placeholder="Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
        </div>
        <p class="password-policy">Use at least 8 characters with 1 capital letter, 2 digits, and 1 special character.</p>
        @error('password')
            <div class="field-error">{{ $message }}</div>
        @enderror

        <div class="password-field">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password confirmation">&#128065;</button>
        </div>

        <button type="submit">Register</button>
    </form>

    <p style="margin-top:15px;">
        I have an account? 
        <a href="/login">Login</a>
    </p>

</div>

@endsection
