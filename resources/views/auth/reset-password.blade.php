@extends('layouts.app')

@section('content')
<div class="form-container">
    <h2>Reset Password</h2>
    <p>Choose a new password. After resetting, your account must be approved before you can log in.</p>

    @include('partials.form-errors')

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" placeholder="Email" value="{{ old('email', $email) }}" maxlength="255" required autofocus>

        <div class="password-field">
            <input type="password" name="password" placeholder="New Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
        </div>

        <div class="password-field">
            <input type="password" name="password_confirmation" placeholder="Confirm New Password" maxlength="255" required>
            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    <p><a href="{{ route('login') }}">Back to Login</a></p>
</div>
@endsection
