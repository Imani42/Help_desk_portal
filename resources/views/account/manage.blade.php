@if(session('success'))
<div class="alert success">
    <p>{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="alert error">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="grid account-grid">
    <div class="card account-box">
        <h3>Update Account Info</h3>

        <form method="POST" action="/account/update" enctype="multipart/form-data">
            @csrf

            <div class="profile-photo-preview">
                @if(auth()->user()->profile_photo)
                    <img src="{{ asset(auth()->user()->profile_photo) }}" alt="Profile photo">
                @else
                    <div class="profile-photo-placeholder">No photo</div>
                @endif
            </div>

            <label class="file-label" for="profile_photo">Profile photo</label>
            <input type="file" name="profile_photo" id="profile_photo" accept="image/*">

            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Name" required>
            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="Email" required>
            <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="Phone">

            @if(auth()->user()->role == 'customer')
                <input type="text" name="region" value="{{ old('region', auth()->user()->region) }}" placeholder="Region">
                <input type="text" name="district" value="{{ old('district', auth()->user()->district) }}" placeholder="District">
                <input type="text" name="ward" value="{{ old('ward', auth()->user()->ward) }}" placeholder="Ward">
                <input type="text" name="street" value="{{ old('street', auth()->user()->street) }}" placeholder="Street">
            @endif

            @if(auth()->user()->role == 'technician')
                <input type="text" name="tech_base" value="{{ old('tech_base', auth()->user()->tech_base) }}" placeholder="Technical base">
            @endif

            <button type="submit">Update Account</button>
        </form>
    </div>

    <div class="card account-box">
        <h3>Reset Password</h3>

        <form method="POST" action="/account/password">
            @csrf

            <input type="password" name="current_password" placeholder="Current password" required>
            <input type="password" name="password" placeholder="New password" required>
            <input type="password" name="password_confirmation" placeholder="Confirm new password" required>

            <button type="submit">Reset Password</button>
        </form>
    </div>

    <div class="card account-box danger-zone">
        <h3>Delete Account</h3>
        <p>This permanently removes your account.</p>

        <form method="POST" action="/account/delete" onsubmit="return confirm('Delete your account permanently?');">
            @csrf
            @method('DELETE')

            <input type="password" name="current_password" placeholder="Current password" required>
            <button type="submit">Delete Account</button>
        </form>
    </div>
</div>
