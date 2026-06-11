@extends('layouts.technician')

@section('content')

{{-- DASHBOARD --}}
@if($page == 'dashboard')
<div class="grid">

    <div class="card">
        <h3>Recent Assigned Faults</h3>

        @forelse($faults as $fault)
        <div class="fault-item">
            <div>
                <strong>{{ $fault->type }}</strong>
                <p>{{ $fault->description }}</p>
                @if($fault->reporter)
                    <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
                @endif
                @include('faults.comments', ['fault' => $fault, 'context' => 'technician'])
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
        <p>No assigned faults yet</p>
        @endforelse

    </div>

</div>
@endif


{{-- ALL ASSIGNED --}}
@if($page == 'assigned')
<div class="card">
    <h3>All Assigned Faults</h3>

    @forelse($faults as $fault)
    <div class="fault-item">

        <div>
            <strong>{{ $fault->type }}</strong>
            <p>{{ $fault->description }}</p>
            <small>{{ $fault->location }}</small>
            @if($fault->reporter)
                <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
            @endif
            @include('faults.comments', ['fault' => $fault, 'context' => 'technician'])
        </div>

        <form method="POST" action="/technician/update/{{ $fault->id }}" class="assign-form">
            @csrf

            <select name="status" required>
                <option value="Pending" {{ $fault->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="In Progress" {{ $fault->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ $fault->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
            </select>

            <button type="submit">Update</button>
        </form>

    </div>
    @empty
    <p>No assigned faults</p>
    @endforelse

</div>
@endif


{{-- COMPLETED --}}
@if($page == 'completed')
<div class="card">
    <h3>Completed Faults</h3>

    @forelse($faults as $fault)
    <div class="fault-item">
        <div>
            <strong>{{ $fault->type }}</strong>
            <p>{{ $fault->description }}</p>
            @if($fault->reporter)
                <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
            @endif
            @include('faults.comments', ['fault' => $fault, 'context' => 'technician'])
        </div>
        <span class="status resolved">Resolved</span>
    </div>
    @empty
    <p>No completed faults</p>
    @endforelse

</div>
@endif


{{-- ACCOUNT --}}
@if($page == 'account')
@include('account.manage')
@endif

@endsection
