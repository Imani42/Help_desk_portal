@extends('layouts.technician')

@section('content')

{{-- DASHBOARD --}}
@if($page == 'dashboard')

<div class="stats-grid">
    <div class="stat-card stat-reported">
        <div class="stat-number">{{ $assignedCount ?? 0 }}</div>
        <div class="stat-label">Reported</div>
    </div>
    <div class="stat-card stat-pending">
        <div class="stat-number">{{ $pendingCount ?? 0 }}</div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card stat-inprogress">
        <div class="stat-number">{{ $inProgressCount ?? 0 }}</div>
        <div class="stat-label">In Progress</div>
    </div>
    <div class="stat-card stat-resolved">
        <div class="stat-number">{{ $resolvedCount ?? 0 }}</div>
        <div class="stat-label">Resolved</div>
    </div>
</div>

<div class="card recent-faults-card">
    <h3>Recent Assigned Faults</h3>

    <ul class="recent-list" style="list-style:none;padding:0;margin:0">
    @forelse($faults as $fault)
        <li class="fault-item">
            <div>
                <strong>{{ $fault->type }}</strong>
                <p>{{ \Illuminate\Support\Str::limit($fault->description, 120) }}</p>
                <small>{{ $fault->location }}</small>
                @if($fault->reporter)
                    <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
                @endif
            </div>

            <span class="status @if($fault->status=='Pending') pending @elseif($fault->status=='In Progress') progress @else resolved @endif">{{ $fault->status }}</span>
        </li>
    @empty
        <p>No assigned faults yet</p>
    @endforelse
    </ul>
</div>

@endif


{{-- ALL ASSIGNED --}}
@if($page == 'assigned')
<div class="grid dashboard-home-grid">
    <div class="card" style="padding-bottom:10px;">
        <h3>All Assigned Faults</h3>

        <ul class="technician-assigned-list" style="list-style:none;padding:0;margin:0">
        @forelse($faults as $fault)
            <li class="fault-item" data-id="{{ $fault->id }}">
                <div>
                    <strong>{{ $fault->type }}</strong>
                    <p>{{ \Illuminate\Support\Str::limit($fault->description, 120) }}</p>
                    <small>{{ $fault->location }}</small>
                </div>
                <span class="status @if($fault->status=='Pending') pending @elseif($fault->status=='In Progress') progress @else resolved @endif">{{ $fault->status }}</span>
            </li>
        @empty
            <p>No assigned faults</p>
        @endforelse
        </ul>
    </div>

    <div class="card fault-details-card" id="technician-assigned-details">
        <h3>Fault Details</h3>
        <div class="details-empty">Select a fault to view details and update progress.</div>
    </div>
</div>

@foreach($faults as $fault)
    <div id="technician-assigned-template-{{ $fault->id }}" style="display:none">
        <p><strong>Type:</strong> {{ $fault->type }}</p>
        <p><strong>Description:</strong> {{ $fault->description }}</p>
        <p><strong>Location:</strong> {{ $fault->location }}</p>
        <p><strong>Contact Phone:</strong> {{ $fault->contact_phone ?: optional($fault->reporter)->phone ?: 'Not provided' }}</p>
        @if($fault->reporter)
            <p><strong>Reporter:</strong> {{ $fault->reporter->name }} {{ $fault->reporter->phone }}</p>
        @endif
        <p><strong>Status:</strong> {{ $fault->status }}</p>

        <h4>Update Status</h4>
        <form method="POST" action="/technician/update/{{ $fault->id }}" class="assign-form">
            @csrf
            <select name="status" required>
                <option value="Pending" {{ $fault->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="In Progress" {{ $fault->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ $fault->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <button type="submit">Update</button>
        </form>

        @include('faults.comments', ['fault' => $fault, 'context' => 'technician'])
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectedAssigned = null;
    var listItems = document.querySelectorAll('.technician-assigned-list .fault-item');
    var details = document.getElementById('technician-assigned-details');
    var emptyHtml = '<h3>Fault Details</h3><div class="details-empty">Select a fault to view details and update progress.</div>';

    listItems.forEach(function(item){
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(){
            var id = item.getAttribute('data-id');
            if (selectedAssigned === id) {
                details.innerHTML = emptyHtml;
                item.classList.remove('selected');
                selectedAssigned = null;
                return;
            }

            if (selectedAssigned) {
                var prev = document.querySelector('.technician-assigned-list .fault-item[data-id="'+selectedAssigned+'"]');
                if (prev) prev.classList.remove('selected');
            }

            selectedAssigned = id;
            item.classList.add('selected');

            var template = document.getElementById('technician-assigned-template-' + id);
            details.innerHTML = '<h3>Fault Details</h3>' + (template ? template.innerHTML : '');
        });
    });
});
</script>
@endif


{{-- COMPLETED --}}
@if($page == 'completed')
<div class="grid dashboard-home-grid">
    <div class="card" style="padding-bottom:10px;">
        <h3>Completed Faults</h3>

        <ul class="technician-completed-list" style="list-style:none;padding:0;margin:0">
        @forelse($faults as $fault)
            <li class="fault-item" data-id="{{ $fault->id }}">
                <div>
                    <strong>{{ $fault->type }}</strong>
                    <p>{{ \Illuminate\Support\Str::limit($fault->description, 120) }}</p>
                    <small>{{ $fault->location }}</small>
                </div>
                <span class="status resolved">Resolved</span>
            </li>
        @empty
            <p>No completed faults</p>
        @endforelse
        </ul>
    </div>

    <div class="card fault-details-card" id="technician-completed-details">
        <h3>Fault Details</h3>
        <div class="details-empty">Select a completed fault to view details.</div>
    </div>
</div>

@foreach($faults as $fault)
    <div id="technician-completed-template-{{ $fault->id }}" style="display:none">
        <p><strong>Type:</strong> {{ $fault->type }}</p>
        <p><strong>Description:</strong> {{ $fault->description }}</p>
        <p><strong>Location:</strong> {{ $fault->location }}</p>
        <p><strong>Contact Phone:</strong> {{ $fault->contact_phone ?: optional($fault->reporter)->phone ?: 'Not provided' }}</p>
        @if($fault->reporter)
            <p><strong>Reporter:</strong> {{ $fault->reporter->name }} {{ $fault->reporter->phone }}</p>
        @endif
        <p><strong>Status:</strong> Resolved</p>

        @include('faults.comments', ['fault' => $fault, 'context' => 'technician'])
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectedCompleted = null;
    var listItems = document.querySelectorAll('.technician-completed-list .fault-item');
    var details = document.getElementById('technician-completed-details');
    var emptyHtml = '<h3>Fault Details</h3><div class="details-empty">Select a completed fault to view details.</div>';

    listItems.forEach(function(item){
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(){
            var id = item.getAttribute('data-id');
            if (selectedCompleted === id) {
                details.innerHTML = emptyHtml;
                item.classList.remove('selected');
                selectedCompleted = null;
                return;
            }

            if (selectedCompleted) {
                var prev = document.querySelector('.technician-completed-list .fault-item[data-id="'+selectedCompleted+'"]');
                if (prev) prev.classList.remove('selected');
            }

            selectedCompleted = id;
            item.classList.add('selected');

            var template = document.getElementById('technician-completed-template-' + id);
            details.innerHTML = '<h3>Fault Details</h3>' + (template ? template.innerHTML : '');
        });
    });
});
</script>
@endif


{{-- ACCOUNT --}}
@if($page == 'account')
@include('account.manage')
@endif

@if(in_array($page, ['assigned', 'completed']) && $faults->hasPages())
    <div class="pagination">{{ $faults->onEachSide(1)->links() }}</div>
@endif

@endsection
