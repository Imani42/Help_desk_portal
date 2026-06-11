@extends('layouts.customer')

@section('content')

{{-- DASHBOARD --}}
@if($page == 'dashboard')

<div class="grid">

    <!-- RECENT -->
    <div class="card">
        <h3>Recent Faults</h3>

        @forelse($faults as $fault)
        <div class="fault-item">

            <div>
                <strong>{{ $fault->type }}</strong>
                <p>{{ $fault->description }}</p>
                @if($fault->technician)
                    <small>Assigned technician: {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
                @else
                    <small>Technician not assigned yet</small>
                @endif
                @include('faults.comments', ['fault' => $fault, 'context' => 'customer'])
            </div>

            <div class="status 
                @if($fault->status=='Pending') pending
                @elseif($fault->status=='In Progress') progress
                @else resolved
                @endif">
                {{ $fault->status }}
            </div>

        </div>
        @empty
        <p>No faults yet</p>
        @endforelse

    </div>

</div>

@endif


{{-- REPORT PAGE --}}
@if($page == 'report')

<div class="card">
    <h3>Report Network Fault</h3>

    @if($errors->any())
    <div class="alert error">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="/customer/fault/store">
        @csrf

        <select name="type" required>
            <option value="">Choose Fault(Chagua Aina ya Tatizo)</option>
            <option>No Internet (Mtandao haupatikani)</option>
            <option>Slow Internet (Mtandao ni polepole)</option>
            <option>Cable Cut (Kukatika kwa waya)</option>
            <option>No Mobile Service (Hakuna huduma ya simu)</option>
            <option>No Power to Device (Kifaa Hakipokei umeme)</option>
            <option>Weak Signal (Signal dhaifu)</option>
            <option>Equipment Damage (Kifaa kimeharibika)</option>
            <option>Sudden Service Interruption (Huduma imekatika ghafla)</option>
            <option>VoIP or Landline Issue (Tatizo la VoIP au Simu ya mezani)</option>
            <option>Other (Tatizo lingine)</option>
        </select>

        <textarea name="description" placeholder="Describe the problem..." required></textarea>

        <input type="text" name="location" placeholder="Location" required>

        <button type="submit">Submit Fault</button>
    </form>
</div>

@endif


{{-- MY FAULTS --}}
@if($page == 'my_faults')

<div class="card">
    <h3>My Reported Faults</h3>

    @forelse($faults as $fault)

    <div class="fault-item">

        <div>
            <strong>{{ $fault->type }}</strong>
            <p>{{ $fault->description }}</p>
            <small>{{ $fault->location }}</small>
            @if($fault->technician)
                <small class="assigned-tech">Assigned technician: {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
            @else
                <small>Technician not assigned yet</small>
            @endif
            @include('faults.comments', ['fault' => $fault, 'context' => 'customer'])
        </div>

        <div class="status 
            @if($fault->status=='Pending') pending
            @elseif($fault->status=='In Progress') progress
            @else resolved
            @endif">
            {{ $fault->status }}
        </div>

    </div>

    @empty
    <p>No faults reported</p>
    @endforelse

</div>

@endif


{{-- RESOLVED --}}
@if($page == 'resolved')

<div class="card">
    <h3>Resolved Faults</h3>

    @forelse($faults as $fault)
    <div class="fault-item">
        <div>
            <strong>{{ $fault->type }}</strong>
            @if($fault->technician)
                <small>Assigned technician: {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
            @endif
            @include('faults.comments', ['fault' => $fault, 'context' => 'customer'])
        </div>
        <span class="status resolved">Resolved</span>
    </div>
    @empty
    <p>No resolved faults</p>
    @endforelse

</div>

@endif


{{-- ACCOUNT --}}
@if($page == 'account')

@include('account.manage')

@endif

@endsection
