@extends('layouts.manager')

@section('content')

@php
    $managerRegion = trim((string) auth()->user()->region);
    $locationRegions = config('tanzania_locations.regions', []);
    $matchedRegion = collect(array_keys($locationRegions))->first(function ($region) use ($managerRegion) {
        return strtolower($region) === strtolower($managerRegion);
    }) ?? $managerRegion;
    $managerDistricts = $locationRegions[$matchedRegion] ?? [];
@endphp

<div class="portal-form-wrap">
    <div class="card portal-form-card">
        <h3>Add User</h3>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/manager/users/store" id="add-user-form">
            @csrf

            <select name="role" id="user-role" required>
                <option value="">Select user type</option>
                <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>Technician</option>
            </select>

            <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
            <input type="text" name="phone" placeholder="Phone" value="{{ old('phone') }}" required>

            <input type="text" value="{{ $matchedRegion }}" placeholder="Region" readonly required>
            <input type="hidden" name="region" value="{{ $matchedRegion }}">
            <select name="district" required>
                <option value="">{{ count($managerDistricts) ? 'Select District' : 'No districts found for this region' }}</option>
                @foreach($managerDistricts as $district)
                    <option value="{{ $district }}" {{ old('district') === $district ? 'selected' : '' }}>{{ $district }}</option>
                @endforeach
            </select>
            @error('district')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <div class="customer-fields" data-role-fields="customer">
                <input type="text" name="ward" placeholder="Ward" value="{{ old('ward') }}">
                <input type="text" name="street" placeholder="Street" value="{{ old('street') }}">
            </div>

            <div class="technician-fields" data-role-fields="technician">
                <input type="text" name="tech_base" placeholder="Technician Base" value="{{ old('tech_base') }}">
            </div>

            <div class="password-field">
                <input type="password" name="password" placeholder="Password" required>
                <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">&#128065;</button>
            </div>
            <p class="password-policy">Use at least 8 characters with 1 capital letter, 2 digits, and 1 special character.</p>

            <button class="add-action-button">Add User</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var role = document.getElementById('user-role');
    var customerFields = document.querySelector('[data-role-fields="customer"]');
    var technicianFields = document.querySelector('[data-role-fields="technician"]');
    var alerts = document.querySelectorAll('.portal-form-card .alert');

    function setFieldState(container, isVisible) {
        container.style.display = isVisible ? 'block' : 'none';
        container.querySelectorAll('input').forEach(function (input) {
            input.required = isVisible;
        });
    }

    function toggleFields() {
        setFieldState(customerFields, role.value === 'customer');
        setFieldState(technicianFields, role.value === 'technician');
    }

    role.addEventListener('change', toggleFields);
    toggleFields();

    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.display = 'none';
        }, 5000);
    });
});
</script>

@endsection
