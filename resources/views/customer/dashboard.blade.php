@extends('layouts.customer')

@section('content')

{{-- DASHBOARD --}}
@if($page == 'dashboard')

<div class="stats-grid">
    <div class="stat-card stat-reported">
        <div class="stat-number">{{ $reportedCount ?? 0 }}</div>
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
    <h3>Recent Faults</h3>

    <ul class="recent-list" style="list-style:none;padding:0;margin:0">
    @forelse($faults as $fault)
        <li class="fault-item">
            <div>
                <strong>{{ $fault->type }}</strong>
                <p>{{ \Illuminate\Support\Str::limit($fault->description, 120) }}</p>
                <small>{{ $fault->location }}</small>
                @if($fault->technician)
                    <small class="assigned-tech">Assigned technician: {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
                @else
                    <small>Technician not assigned yet</small>
                @endif
            </div>

            <span class="status @if($fault->status=='Pending') pending @elseif($fault->status=='In Progress') progress @else resolved @endif">{{ $fault->status }}</span>
        </li>
    @empty
        <p>No faults yet</p>
    @endforelse
    </ul>
</div>

@endif


{{-- REPORT PAGE --}}
@if($page == 'report')

<div class="card">
    <h3>Report Fault</h3>

    @if($errors->any())
    <div class="alert error">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="/customer/fault/store">
        @csrf

       SELECT A FAULT TYPE(CHAGUA AINA YA TATIZO)<select name="type" required>
            <!-- <option value="">Select fault type</option> -->
            <option {{ old('type') == 'No Internet' ? 'selected' : '' }}>No Internet(Hakuna mtandao)</option>
            <option {{ old('type') == 'Slow Internet' ? 'selected' : '' }}>Slow Internet(Mtandao uko pole pole)</option>
            <option {{ old('type') == 'Cable Cut' ? 'selected' : '' }}>Cable Cut(waya umekatika)</option>
            <option {{ old('type') == 'Phone Service Issue' ? 'selected' : '' }}>Phone Service Issue(Tatizo la simu)</option>
            <option {{ old('type') == 'Equipment Issue' ? 'selected' : '' }}>Equipment Issue(Tatizo la kiufundi)</option>
            <option {{ old('type') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>

        <textarea name="description" placeholder="Briefly Problem Descriptio(maelezo mafupi juu ya tatizo)" required>{{ old('description') }}</textarea>

        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="Phone number (optional)">

        <button type="submit">Submit Fault</button>
    </form>
</div>

@endif


{{-- MY FAULTS --}}
@if($page == 'my_faults')

@if(session('success'))
<div class="alert success">
    <p>{{ session('success') }}</p>
</div>
@endif

<div class="grid dashboard-home-grid">
    <div class="card" style="padding-bottom:10px;">
        <h3>My Reported Faults</h3>

        <ul class="customer-faults-list" style="list-style:none;padding:0;margin:0">
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
            <p>No faults reported</p>
        @endforelse
        </ul>
    </div>

    <div class="card fault-details-card" id="customer-fault-details">
        <h3>Fault Details</h3>
        <div class="details-empty">Select a fault to view details and comments.</div>
    </div>
</div>

@foreach($faults as $fault)
    <div id="customer-fault-template-{{ $fault->id }}" style="display:none">
        <p><strong>Type:</strong> {{ $fault->type }}</p>
        <p><strong>Description:</strong> {{ $fault->description }}</p>
        <p><strong>Location:</strong> {{ $fault->location }}</p>
        <p><strong>Contact Phone:</strong> {{ $fault->contact_phone ?: optional($fault->reporter)->phone ?: 'Not provided' }}</p>
        @if($fault->technician)
            <p><strong>Assigned Technician:</strong> {{ $fault->technician->name }} {{ $fault->technician->phone }}</p>
        @else
            <p><strong>Assigned Technician:</strong> Technician not assigned yet</p>
        @endif
        <p><strong>Status:</strong> {{ $fault->status }}</p>

        <form method="POST" action="/customer/fault/{{ $fault->id }}" class="delete-fault-form">
            @csrf
            @method('DELETE')
            <button type="button" class="delete-fault-toggle">Delete Fault</button>
            <div class="delete-fault-confirm">
                <span>Delete this fault?</span>
                <button type="submit">Yes</button>
                <button type="button" class="delete-fault-cancel">No</button>
            </div>
        </form>

        @include('faults.comments', ['fault' => $fault, 'context' => 'customer'])
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectedFault = null;
    var listItems = document.querySelectorAll('.customer-faults-list .fault-item');
    var details = document.getElementById('customer-fault-details');
    var emptyHtml = '<h3>Fault Details</h3><div class="details-empty">Select a fault to view details and comments.</div>';

    listItems.forEach(function(item){
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(){
            var id = item.getAttribute('data-id');
            if (selectedFault === id) {
                details.innerHTML = emptyHtml;
                item.classList.remove('selected');
                selectedFault = null;
                return;
            }

            if (selectedFault) {
                var prev = document.querySelector('.customer-faults-list .fault-item[data-id="'+selectedFault+'"]');
                if (prev) prev.classList.remove('selected');
            }

            selectedFault = id;
            item.classList.add('selected');

            var template = document.getElementById('customer-fault-template-' + id);
            details.innerHTML = '<h3>Fault Details</h3>' + (template ? template.innerHTML : '');
        });
    });

    details.addEventListener('click', function(event){
        if (event.target.classList.contains('delete-fault-toggle')) {
            event.target.closest('.delete-fault-form').classList.add('confirming');
        }

        if (event.target.classList.contains('delete-fault-cancel')) {
            event.target.closest('.delete-fault-form').classList.remove('confirming');
        }
    });
});
</script>

@endif


{{-- RESOLVED --}}
@if($page == 'resolved')

<div class="grid dashboard-home-grid">
    <div class="card" style="padding-bottom:10px;">
        <h3>Resolved Faults</h3>

        <ul class="customer-resolved-list" style="list-style:none;padding:0;margin:0">
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
            <p>No resolved faults</p>
        @endforelse
        </ul>
    </div>

    <div class="card fault-details-card" id="customer-resolved-details">
        <h3>Fault Details</h3>
        <div class="details-empty">Select a resolved fault to view details.</div>
    </div>
</div>

@foreach($faults as $fault)
    <div id="customer-resolved-template-{{ $fault->id }}" style="display:none">
        <p><strong>Type:</strong> {{ $fault->type }}</p>
        <p><strong>Description:</strong> {{ $fault->description }}</p>
        <p><strong>Location:</strong> {{ $fault->location }}</p>
        <p><strong>Contact Phone:</strong> {{ $fault->contact_phone ?: optional($fault->reporter)->phone ?: 'Not provided' }}</p>
        @if($fault->technician)
            <p><strong>Assigned Technician:</strong> {{ $fault->technician->name }} {{ $fault->technician->phone }}</p>
        @else
            <p><strong>Assigned Technician:</strong> Technician not assigned yet</p>
        @endif
        <p><strong>Status:</strong> Resolved</p>

        <form method="POST" action="/customer/fault/{{ $fault->id }}" class="delete-fault-form">
            @csrf
            @method('DELETE')
            <button type="button" class="delete-fault-toggle">Delete Fault</button>
            <div class="delete-fault-confirm">
                <span>Delete this fault?</span>
                <button type="submit">Yes</button>
                <button type="button" class="delete-fault-cancel">No</button>
            </div>
        </form>

        @include('faults.comments', ['fault' => $fault, 'context' => 'customer'])
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectedResolved = null;
    var listItems = document.querySelectorAll('.customer-resolved-list .fault-item');
    var details = document.getElementById('customer-resolved-details');
    var emptyHtml = '<h3>Fault Details</h3><div class="details-empty">Select a resolved fault to view details.</div>';

    listItems.forEach(function(item){
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(){
            var id = item.getAttribute('data-id');
            if (selectedResolved === id) {
                details.innerHTML = emptyHtml;
                item.classList.remove('selected');
                selectedResolved = null;
                return;
            }

            if (selectedResolved) {
                var prev = document.querySelector('.customer-resolved-list .fault-item[data-id="'+selectedResolved+'"]');
                if (prev) prev.classList.remove('selected');
            }

            selectedResolved = id;
            item.classList.add('selected');

            var template = document.getElementById('customer-resolved-template-' + id);
            details.innerHTML = '<h3>Fault Details</h3>' + (template ? template.innerHTML : '');
        });
    });

    details.addEventListener('click', function(event){
        if (event.target.classList.contains('delete-fault-toggle')) {
            event.target.closest('.delete-fault-form').classList.add('confirming');
        }

        if (event.target.classList.contains('delete-fault-cancel')) {
            event.target.closest('.delete-fault-form').classList.remove('confirming');
        }
    });
});
</script>

@endif


{{-- ACCOUNT --}}
@if($page == 'account')

@include('account.manage')

@endif

@if(in_array($page, ['my_faults', 'resolved']) && $faults->hasPages())
    <div class="pagination">{{ $faults->onEachSide(1)->links() }}</div>
@endif

@endsection
