@extends('layouts.app')

@section('content')
<div class="form-container">
    <h2>Forgot Password</h2>
    <p>Enter the email address registered to your account. We will send a password-reset link if an account exists.</p>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @include('partials.form-errors')

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" maxlength="255" required autofocus>
        <button type="submit">Send Reset Link</button>
    </form>

    <p><a href="{{ route('login') }}">Back to Login</a></p>
</div>
@endsection
