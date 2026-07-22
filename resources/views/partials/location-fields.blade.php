@php
    $locationRegions = config('tanzania_locations.regions', []);
    $selectedRegion = $selectedRegion ?? old('region');
    $selectedDistrict = $selectedDistrict ?? old('district');
    $regionReadonly = $regionReadonly ?? false;
@endphp

@if($regionReadonly)
    <input type="text" value="{{ $selectedRegion }}" placeholder="Region" readonly required>
    <input type="hidden" name="region" value="{{ $selectedRegion }}">
@else
    <select name="region" data-region-select required>
        <option value="">Select Region</option>
        @foreach(array_keys($locationRegions) as $region)
            <option value="{{ $region }}" {{ $selectedRegion === $region ? 'selected' : '' }}>{{ $region }}</option>
        @endforeach
    </select>
@endif
@error('region')
    <div class="field-error">{{ $message }}</div>
@enderror

<select name="district" data-district-select data-selected-district="{{ $selectedDistrict }}" data-fixed-region="{{ $regionReadonly ? $selectedRegion : '' }}" required>
    <option value="">Select District</option>
</select>
@error('district')
    <div class="field-error">{{ $message }}</div>
@enderror
