@extends('layouts.manager')

@section('content')

{{-- DASHBOARD --}}
@if($page == 'dashboard')
<div class="grid">
    <div class="card">
        <h3>Recent Faults</h3>

        @forelse($faults as $fault)
        <div class="fault-item">
            <div>
                <strong>{{ $fault->type }}</strong>
                <p>{{ $fault->description }}</p>
                @if($fault->reporter)
                    <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
                @endif
                @if($fault->technician)
                    <small>Assigned to {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
                @endif
                @include('faults.comments', ['fault' => $fault, 'context' => 'manager'])
            </div>

            <span class="status
                @if($fault->status=='Pending') pending
                @elseif($fault->status=='In Progress') progress
                @else resolved
                @endif">
                {{ $fault->status }}
            </span>
        </div>
        @empty
        <p>No recent faults</p>
        @endforelse

    </div>
</div>
@endif


{{-- ALL FAULTS + ASSIGN --}}
@if($page == 'all_faults')
<div class="card">
    <h3>All Faults</h3>

    @forelse($faults as $fault)
    <div class="fault-item">

        <div>
            <strong>{{ $fault->type }}</strong>
            <p>{{ $fault->description }}</p>
            <small>{{ $fault->location }}</small>
            @if($fault->reporter)
                <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
            @endif
            @if($fault->technician)
                <small class="assigned-tech">Assigned to {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
            @endif
            @include('faults.comments', ['fault' => $fault, 'context' => 'manager'])
        </div>

        <form method="POST" action="/manager/assign/{{ $fault->id }}" class="assign-form">
            @csrf

            <select name="technician_id" required>
                <option value="">Select Technician</option>

                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ $fault->technician_id == $tech->id ? 'selected' : '' }}>
                        {{ $tech->name }}
                    </option>
                @endforeach

            </select>

            <button type="submit">Assign</button>
        </form>

    </div>
    @empty
    <p>No faults found</p>
    @endforelse

</div>
@endif


{{-- ASSIGNED --}}
@if($page == 'assigned')
<div class="card">
    <h3>Assigned Faults</h3>

    @forelse($faults as $fault)
    <div class="fault-item">
        <div>
            <strong>{{ $fault->type }}</strong>
            <p>{{ $fault->description }}</p>
            @if($fault->reporter)
                <small>Reported by {{ $fault->reporter->name }} - {{ $fault->reporter->phone }}</small>
            @endif
            @if($fault->technician)
                <small>Assigned to {{ $fault->technician->name }} - {{ $fault->technician->phone }}</small>
            @endif
            @include('faults.comments', ['fault' => $fault, 'context' => 'manager'])
        </div>

        <span class="status progress">{{ $fault->status }}</span>
    </div>
    @empty
    <p>No assigned faults</p>
    @endforelse

</div>
@endif


{{-- ACCOUNT --}}
@if($page == 'account')
@include('account.manage')
@endif


{{-- TECHNICIANS --}}
@if($page == 'technicians')

<div class="grid">
    <div class="card">
        <h3>Add Technician</h3>

        <form method="POST" action="/manager/technician/store">
            @csrf
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="password" name="password" placeholder="Password" required>
            <button>Add</button>
        </form>
    </div>

    <div class="card">
        <h3>All Technicians</h3>

        @forelse($technicians as $tech)
        <div class="technician-row">
            <div>
                <strong>{{ $tech->name }}</strong>
                @if($tech->is_approved)
                    <span class="approval-badge approved">Approved</span>
                @else
                    <span class="approval-badge pending-approval">Pending approval</span>
                @endif
            </div>

            <div class="technician-actions">
                @if(! $tech->is_approved)
                    <a href="/manager/user/approve/{{ $tech->id }}" class="approve-action">Approve</a>
                @else
                    <a href="/manager/user/deactivate/{{ $tech->id }}" class="deactivate-action">Deactivate</a>
                @endif

                <details>
                    <summary>View</summary>
                    <div class="technician-panel">
                        <p><strong>Email:</strong> {{ $tech->email }}</p>
                        <p><strong>Phone:</strong> {{ $tech->phone }}</p>
                    </div>
                </details>

                <details>
                    <summary>Edit</summary>
                    <form method="POST" action="/manager/technician/update/{{ $tech->id }}" class="technician-panel">
                        @csrf

                        <input type="text" name="name" value="{{ $tech->name }}" required>
                        <input type="email" name="email" value="{{ $tech->email }}" required>
                        <input type="text" name="phone" value="{{ $tech->phone }}" required>

                        <button>Update</button>
                    </form>
                </details>

                <a href="/manager/technician/delete/{{ $tech->id }}" class="danger-action">Delete</a>
            </div>
        </div>
        @empty
        <p>No technicians found.</p>
        @endforelse
    </div>
</div>

@endif


{{-- CUSTOMERS --}}
@if($page == 'customers')

<div class="grid">
    <div class="card">
        <h3>Add Customer</h3>

        <form method="POST" action="/manager/customer/store">
            @csrf
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="text" name="region" placeholder="Region" required>
            <input type="text" name="district" placeholder="District" required>
            <input type="text" name="ward" placeholder="Ward" required>
            <input type="text" name="street" placeholder="Street" required>
            <input type="password" name="password" placeholder="Password" required>
            <button>Add</button>
        </form>
    </div>

    <div class="card">
        <h3>All Customers</h3>

        @forelse($customers as $customer)
        <div class="technician-row">
            <div>
                <strong>{{ $customer->name }}</strong>
                @if($customer->is_approved)
                    <span class="approval-badge approved">Approved</span>
                @else
                    <span class="approval-badge pending-approval">Pending approval</span>
                @endif
            </div>

            <div class="technician-actions">
                @if(! $customer->is_approved)
                    <a href="/manager/user/approve/{{ $customer->id }}" class="approve-action">Approve</a>
                @else
                    <a href="/manager/user/deactivate/{{ $customer->id }}" class="deactivate-action">Deactivate</a>
                @endif

                <details>
                    <summary>View</summary>
                    <div class="technician-panel">
                        <p><strong>Email:</strong> {{ $customer->email }}</p>
                        <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                        <p><strong>Region:</strong> {{ $customer->region }}</p>
                        <p><strong>District:</strong> {{ $customer->district }}</p>
                        <p><strong>Ward:</strong> {{ $customer->ward }}</p>
                        <p><strong>Street:</strong> {{ $customer->street }}</p>
                    </div>
                </details>

                <details>

                //CUSTOMER ACCOUNT UPDATE BY MANAGE(here is where manager ca edit the acount of cuatomer in case the customer has forgoten the customer reported physically that has forgot his or her account)
                    <summary>Edit</summary>
                    <form method="POST" action="/manager/customer/update/{{ $customer->id }}" class="technician-panel">
                        @csrf

                        <input type="text" name="name" value="{{ $customer->name }}" required>
                        <input type="email" name="email" value="{{ $customer->email }}" required>
                        <input type="text" name="phone" value="{{ $customer->phone }}" required>
                        <input type="text" name="region" value="{{ $customer->region }}" required>
                        <input type="text" name="district" value="{{ $customer->district }}" required>
                        <input type="text" name="ward" value="{{ $customer->ward }}" required>
                        <input type="text" name="street" value="{{ $customer->street }}" required>

                        <button>Update</button>
                    </form>
                </details>

                <a href="/manager/customer/delete/{{ $customer->id }}" class="danger-action">Delete</a>
            </div>
        </div>
        @empty
        <p>No customers found</p>
        @endforelse
    </div>
</div>

@endif

@endsection
