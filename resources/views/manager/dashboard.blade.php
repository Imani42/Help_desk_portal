@extends('layouts.manager')

@section('content')

@php
    // This template is shared by all manager pages, so dashboard totals must
    // always have safe defaults when a page does not provide them.
    $reported = $reportedCount ?? 0;
    $pending = $pendingCount ?? 0;
    $assigned = $assignedCount ?? 0;
    $inProgress = $inProgressCount ?? 0;
    $resolved = $resolvedCount ?? 0;
    $statusTotal = max($pending + $assigned + $inProgress + $resolved, 1);
    $pendingDeg = ($pending / $statusTotal) * 360;
    $assignedDeg = $pendingDeg + (($assigned / $statusTotal) * 360);
    $progressDeg = $assignedDeg + (($inProgress / $statusTotal) * 360);
    $statusSegments = [
        ['name' => 'Pending', 'end' => $pendingDeg],
        ['name' => 'Assigned', 'end' => $assignedDeg],
        ['name' => 'In Progress', 'end' => $progressDeg],
        ['name' => 'Resolved', 'end' => 360],
    ];
@endphp

{{-- REPORTS --}}
@if($page == 'reports')
<div class="report-tools no-print">
    <div>
        <h1>Regional District Reports</h1>
        <p>Generate a monthly, annual, or custom-period report for {{ auth()->user()->region }} and print it when ready.</p>
    </div>
    <button type="button" class="report-print-button" onclick="window.print()">Print Report</button>
</div>

<div class="card report-filter no-print">
    <form method="GET" action="/manager/reports">
        <label>Report type
            <select name="type" id="report-type">
                <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>Monthly report</option>
                <option value="annual" {{ $type === 'annual' ? 'selected' : '' }}>Annual report</option>
                <option value="custom" {{ $type === 'custom' ? 'selected' : '' }}>Custom period</option>
            </select>
        </label>
        <label class="month-input">Month
            <input type="month" name="month" value="{{ $month }}">
        </label>
        <label class="year-input">Year
            <input type="number" name="year" min="2000" max="2100" value="{{ $year }}">
        </label>
        <label class="custom-start-input">Start month
            <input type="month" name="custom_start" value="{{ $customStart }}">
        </label>
        <label class="custom-end-input">End month
            <input type="month" name="custom_end" value="{{ $customEnd }}">
        </label>
        <button type="submit">Generate report</button>
    </form>
</div>

<section class="report-document">
    <div class="report-heading">
        <div>
            <img src="{{ asset('images/ttcl.png') }}" alt="TTCL" class="report-logo">
            <h2>TTCL {{ auth()->user()->region }} District Report</h2>
            <p>{{ ucfirst($type) }} report: {{ $reportPeriod }}</p>
        </div>
        <p class="report-generated">Prepared by {{ auth()->user()->name }}<br>Generated {{ now()->format('d M Y, H:i') }}</p>
    </div>

    @php($rateGroups = ['80–100% of faults' => 'critical', '50–79% of faults' => 'high', '30–49% of faults' => 'medium', '0–29% of faults' => 'low'])
    @if(collect($districts)->isEmpty())
        <p class="report-empty">No district records are available for this region.</p>
    @else
        @foreach($rateGroups as $groupLabel => $groupClass)
            @php($groupDistricts = collect($districts)->where('rateClass', $groupClass))
            @if($groupDistricts->isNotEmpty())
                <section class="report-rate-group {{ $groupClass }}">
                    <h3>{{ $groupLabel }}</h3>
                    <div class="report-table-wrap">
                        <table class="regional-report-table">
                            <thead><tr><th>Rank</th><th>District</th><th>Customers</th><th>Technicians</th><th>Faults reported</th><th>Fault rate</th></tr></thead>
                            <tbody>@foreach($groupDistricts as $district)
                                <tr><td>#{{ $district['rank'] }}</td><td><strong>{{ $district['district'] }}</strong></td><td>{{ $district['customers'] }}</td><td>{{ $district['technicians'] }}</td><td>{{ $district['faults'] }}</td><td><span class="rate-badge {{ $district['rateClass'] }}">{{ number_format($district['rate'], 1) }}%</span></td></tr>
                            @endforeach</tbody>
                        </table>
                    </div>
                </section>
            @endif
        @endforeach
    @endif

    @if($type === 'annual')
        <h3 class="report-section-title">Monthly district fault reports</h3>
        <div class="report-table-wrap">
            <table class="annual-month-table">
                <thead><tr><th>Month</th><th>Total faults</th><th>District breakdown</th></tr></thead>
                <tbody>@foreach($monthlyReports as $monthlyReport)
                    <tr><td>{{ $monthlyReport['label'] }}</td><td>{{ $monthlyReport['total'] }}</td><td>{{ collect($monthlyReport['districts'])->map(fn ($district) => $district['district'].' ('.$district['faults'].')')->join(', ') ?: 'No faults reported' }}</td></tr>
                @endforeach</tbody>
            </table>
        </div>
    @endif
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('report-type');
    if (!type) return;
    const setVisibility = () => {
        document.querySelector('.month-input').style.display = type.value === 'monthly' ? 'grid' : 'none';
        document.querySelector('.year-input').style.display = type.value === 'annual' ? 'grid' : 'none';
        document.querySelector('.custom-start-input').style.display = type.value === 'custom' ? 'grid' : 'none';
        document.querySelector('.custom-end-input').style.display = type.value === 'custom' ? 'grid' : 'none';
    };
    type.addEventListener('change', setVisibility); setVisibility();
});
</script>
@endif

{{-- DASHBOARD --}}
@if($page == 'dashboard')

<div class="manager-dashboard-head">
    <div>
        <h1>Dashboard Overview</h1>
        <p>Welcome back, {{ auth()->user()->name }}</p>
    </div>
    <div class="dashboard-date">{{ now()->format('d M Y') }}</div>
</div>

<div class="manager-stat-grid">
    <a href="/manager/faults" class="manager-stat-card stat-blue">
        <span>Reported</span>
        <div class="stat-icon">RP</div>
        <strong>{{ $reported }}</strong>
        <small>Total reported faults</small>
    </a>
    <a href="/manager/faults" class="manager-stat-card stat-red">
        <span>Pending</span>
        <div class="stat-icon">PN</div>
        <strong>{{ $pending }}</strong>
        <small>View pending faults</small>
    </a>
    <a href="/manager/assigned" class="manager-stat-card stat-gold">
        <span>Assigned</span>
        <div class="stat-icon">AS</div>
        <strong>{{ $assigned }}</strong>
        <small>View assigned</small>
    </a>
    <a href="/manager/faults" class="manager-stat-card stat-purple">
        <span>In Progress</span>
        <div class="stat-icon">IP</div>
        <strong>{{ $inProgress }}</strong>
        <small>View active faults</small>
    </a>
    <a href="/manager/faults" class="manager-stat-card stat-green">
        <span>Resolved</span>
        <div class="stat-icon">RS</div>
        <strong>{{ $resolved }}</strong>
        <small>View resolved</small>
    </a>
</div>

<div class="dashboard-report-grid">
    <div class="dashboard-panel">
        <h3>Faults by Status</h3>
        <div class="status-chart-wrap">
            <div class="status-donut"
                data-status-segments='@json($statusSegments)'
                style="background:conic-gradient(#2563eb 0deg {{ $pendingDeg }}deg, #f59e0b {{ $pendingDeg }}deg {{ $assignedDeg }}deg, #22c55e {{ $assignedDeg }}deg {{ $progressDeg }}deg, #ef4444 {{ $progressDeg }}deg 360deg);">
                <span>{{ $reported }}</span>
                <div class="status-tooltip" aria-hidden="true"></div>
            </div>
            <div class="status-legend">
                <div><span class="legend-dot blue"></span>Pending <strong>{{ $pending }}</strong></div>
                <div><span class="legend-dot gold"></span>Assigned <strong>{{ $assigned }}</strong></div>
                <div><span class="legend-dot green"></span>In Progress <strong>{{ $inProgress }}</strong></div>
                <div><span class="legend-dot red"></span>Resolved <strong>{{ $resolved }}</strong></div>
            </div>
        </div>
    </div>

    <div class="dashboard-panel recent-table-panel">
        <div class="panel-title-row">
            <h3>Recent Faults</h3>
            <a href="/manager/faults">View all</a>
        </div>

        <div class="fault-table-wrap">
            <table class="fault-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faults as $fault)
                    <tr>
                        <td>FLT-{{ str_pad($fault->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ optional($fault->reporter)->name ?? 'Unknown' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($fault->type, 28) }}</td>
                        <td><span class="status @if($fault->status=='Pending') pending @elseif($fault->status=='In Progress') progress @else resolved @endif">{{ $fault->status }}</span></td>
                        <td>{{ optional($fault->created_at)->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No recent faults</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.status-donut[data-status-segments]').forEach(function (donut) {
        var tooltip = donut.querySelector('.status-tooltip');
        var segments = JSON.parse(donut.getAttribute('data-status-segments') || '[]');

        function getSegmentName(angle) {
            for (var i = 0; i < segments.length; i++) {
                if (angle <= segments[i].end) {
                    return segments[i].name;
                }
            }
            return segments.length ? segments[segments.length - 1].name : '';
        }

        donut.addEventListener('mousemove', function (event) {
            var rect = donut.getBoundingClientRect();
            var x = event.clientX - rect.left - rect.width / 2;
            var y = event.clientY - rect.top - rect.height / 2;
            var angle = (Math.atan2(y, x) * 180 / Math.PI + 90 + 360) % 360;
            var name = getSegmentName(angle);

            tooltip.textContent = name;
            tooltip.style.left = (event.clientX - rect.left + 12) + 'px';
            tooltip.style.top = (event.clientY - rect.top + 12) + 'px';
            tooltip.classList.add('is-visible');
        });

        donut.addEventListener('mouseleave', function () {
            tooltip.classList.remove('is-visible');
        });
    });
});
</script>

@endif


{{-- ALL FAULTS + ASSIGN --}}
@if($page == 'all_faults')
<div class="grid dashboard-home-grid">
    <div class="card" style="padding-bottom:10px;">
        <h3>All Faults</h3>

        <ul class="all-faults-list" style="list-style:none;padding:0;margin:0">
        @forelse($faults as $fault)
            <li class="fault-item" data-id="{{ $fault->id }}" data-location="{{ e($fault->location ?? '') }}" data-reporter="{{ e(optional($fault->reporter)->name) }}" data-reporter-phone="{{ e($fault->contact_phone ?: optional($fault->reporter)->phone) }}" data-tech-name="{{ e(optional($fault->technician)->name) }}" data-tech-phone="{{ e(optional($fault->technician)->phone) }}">
                <div>
                    <strong>{{ $fault->type }}</strong>
                    <p>{{ \Illuminate\Support\Str::limit($fault->description, 120) }}</p>
                    <small>{{ $fault->location }}</small>
                </div>
                <span class="status @if($fault->status=='Pending') pending @elseif($fault->status=='In Progress') progress @else resolved @endif">{{ $fault->status }}</span>
            </li>
        @empty
            <p>No faults found</p>
        @endforelse
        </ul>
    </div>

    <div class="card fault-details-card" id="all-fault-details">
        <h3>Fault Details</h3>
        <div class="details-empty">Select a fault to view details and assign a technician.</div>
    </div>
</div>

{{-- hidden template for technician options --}}
<select id="tech-options" style="display:none">
    <option value="">Select Technician</option>
    @foreach($technicians as $tech)
        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
    @endforeach
</select>

<div style="display:none">
    @foreach($faults as $fault)
        <template id="all-fault-comments-{{ $fault->id }}">
            @include('faults.comments', ['fault' => $fault, 'context' => 'manager'])
        </template>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectedAll = null;
    var listItems = document.querySelectorAll('.all-faults-list .fault-item');
    var details = document.getElementById('all-fault-details');
    var techOptions = document.getElementById('tech-options').innerHTML;
    var csrf = '{{ csrf_token() }}';

    listItems.forEach(function(item){
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(){
            var id = item.getAttribute('data-id');
            if (selectedAll === id) {
                details.innerHTML = '<h3>Fault Details</h3><div class="details-empty">Select a fault to view details and assign a technician.</div>';
                item.classList.remove('selected');
                selectedAll = null;
                return;
            }

            if (selectedAll) {
                var prev = document.querySelector('.all-faults-list .fault-item[data-id="'+selectedAll+'"]');
                if (prev) prev.classList.remove('selected');
            }

            selectedAll = id;
            item.classList.add('selected');

            var loc = item.getAttribute('data-location') || '';
            var reporter = item.getAttribute('data-reporter') || '';
            var reporterPhone = item.getAttribute('data-reporter-phone') || '';
            var techName = item.getAttribute('data-tech-name') || 'Unassigned';
            var techPhone = item.getAttribute('data-tech-phone') || '';
            var type = item.querySelector('strong') ? item.querySelector('strong').innerText : '';
            var desc = item.querySelector('p') ? item.querySelector('p').innerText : '';
            var status = item.querySelector('.status') ? item.querySelector('.status').innerText : '';

            var html = '<p><strong>Type:</strong> ' + type + '</p>' +
                       '<p><strong>Description:</strong> ' + desc + '</p>' +
                       '<p><strong>Location:</strong> ' + loc + '</p>' +
                       '<p><strong>Reporter:</strong> ' + reporter + '</p>' +
                       '<p><strong>Contact Phone:</strong> ' + (reporterPhone || 'Not provided') + '</p>' +
                       '<p><strong>Assigned Technician:</strong> ' + techName + ' ' + techPhone + '</p>' +
                       '<p><strong>Status:</strong> ' + status + '</p>';

            // assign form
            html += '<h4>Assign Technician</h4>' +
                    '<form id="assign-form" method="POST" action="/manager/assign/' + id + '">' +
                    '<input type="hidden" name="_token" value="' + csrf + '">' +
                    '<select name="technician_id" required>' + techOptions + '</select>' +
                    '<button type="submit">Assign</button>' +
                    '</form>';

            var commentsTemplate = document.getElementById('all-fault-comments-' + id);
            if (commentsTemplate) html += commentsTemplate.innerHTML;
            details.innerHTML = '<h3>Fault Details</h3>' + html;
        });
    });
});
</script>

@endif


{{-- ASSIGNED --}}
@if($page == 'assigned')
<div class="grid dashboard-home-grid">
    <div class="card" style="padding-bottom:10px;">
        <h3>Assigned Faults</h3>

        <ul class="assigned-faults-list" style="list-style:none;padding:0;margin:0">
        @forelse($faults as $fault)
            <li class="fault-item" data-id="{{ $fault->id }}" data-location="{{ e($fault->location ?? '') }}" data-reporter="{{ e(optional($fault->reporter)->name) }}" data-reporter-phone="{{ e($fault->contact_phone ?: optional($fault->reporter)->phone) }}" data-tech-name="{{ e(optional($fault->technician)->name) }}" data-tech-phone="{{ e(optional($fault->technician)->phone) }}">
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

    <div class="card fault-details-card" id="assigned-fault-details">
        <h3>Fault Details</h3>
        <div class="details-empty">Select a fault to view details and reassign if needed.</div>
    </div>
</div>

{{-- hidden template for technician options (available when this page rendered) --}}
<select id="tech-options-assigned" style="display:none">
    <option value="">Select Technician</option>
    @foreach($technicians as $tech)
        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
    @endforeach
</select>

<div style="display:none">
    @foreach($faults as $fault)
        <template id="assigned-fault-comments-{{ $fault->id }}">
            @include('faults.comments', ['fault' => $fault, 'context' => 'manager'])
        </template>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectedAssigned = null;
    var listItems = document.querySelectorAll('.assigned-faults-list .fault-item');
    var details = document.getElementById('assigned-fault-details');
    var techOptions = document.getElementById('tech-options-assigned').innerHTML;
    var csrf = '{{ csrf_token() }}';

    listItems.forEach(function(item){
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(){
            var id = item.getAttribute('data-id');
            if (selectedAssigned === id) {
                details.innerHTML = '<h3>Fault Details</h3><div class="details-empty">Select a fault to view details and reassign if needed.</div>';
                item.classList.remove('selected');
                selectedAssigned = null;
                return;
            }

            if (selectedAssigned) {
                var prev = document.querySelector('.assigned-faults-list .fault-item[data-id="'+selectedAssigned+'"]');
                if (prev) prev.classList.remove('selected');
            }

            selectedAssigned = id;
            item.classList.add('selected');

            var loc = item.getAttribute('data-location') || '';
            var reporter = item.getAttribute('data-reporter') || '';
            var reporterPhone = item.getAttribute('data-reporter-phone') || '';
            var techName = item.getAttribute('data-tech-name') || 'Unassigned';
            var techPhone = item.getAttribute('data-tech-phone') || '';
            var type = item.querySelector('strong') ? item.querySelector('strong').innerText : '';
            var desc = item.querySelector('p') ? item.querySelector('p').innerText : '';
            var status = item.querySelector('.status') ? item.querySelector('.status').innerText : '';

            var html = '<p><strong>Type:</strong> ' + type + '</p>' +
                       '<p><strong>Description:</strong> ' + desc + '</p>' +
                       '<p><strong>Location:</strong> ' + loc + '</p>' +
                       '<p><strong>Reporter:</strong> ' + reporter + '</p>' +
                       '<p><strong>Contact Phone:</strong> ' + (reporterPhone || 'Not provided') + '</p>' +
                       '<p><strong>Assigned Technician:</strong> ' + techName + ' ' + techPhone + '</p>' +
                       '<p><strong>Status:</strong> ' + status + '</p>';

            // reassign form
            html += '<h4>Reassign Technician</h4>' +
                    '<form id="assign-form" method="POST" action="/manager/assign/' + id + '">' +
                    '<input type="hidden" name="_token" value="' + csrf + '">' +
                    '<select name="technician_id" required>' + techOptions + '</select>' +
                    '<button type="submit">Assign</button>' +
                    '</form>';

            var commentsTemplate = document.getElementById('assigned-fault-comments-' + id);
            if (commentsTemplate) html += commentsTemplate.innerHTML;
            details.innerHTML = '<h3>Fault Details</h3>' + html;
        });
    });
});
</script>

@endif


{{-- ACCOUNT --}}
@if($page == 'account')
@include('account.manage')
@endif


{{-- TECHNICIANS --}}
@if($page == 'technicians')

<div class="card">
    <h3>All Technicians</h3>

    <div class="user-list">
    @forelse($technicians as $tech)
    <div class="technician-row user-click-row" style="cursor:pointer; flex-wrap:wrap;" data-type="Technician">
        <div>
            <strong>{{ $tech->name }}</strong>
            @if($tech->is_approved)
                <span class="approval-badge approved">Approved</span>
            @else
                <span class="approval-badge pending-approval">Pending approval</span>
            @endif
        </div>

        <div class="technician-actions" onclick="event.stopPropagation()">
            @if(! $tech->is_approved)
                <form method="POST" action="/manager/user/approve/{{ $tech->id }}" class="inline-action-form">
                    @csrf
                    <button type="submit" class="approve-action">Approve</button>
                </form>
            @else
                <form method="POST" action="/manager/user/deactivate/{{ $tech->id }}" class="inline-action-form">
                    @csrf
                    <button type="submit" class="deactivate-action">Deactivate</button>
                </form>
            @endif

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

            <form method="POST" action="/manager/technician/delete/{{ $tech->id }}" class="inline-action-form" onsubmit="return confirm('Delete this technician?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger-action">Delete</button>
            </form>
        </div>

        <div class="user-inline-details" style="display:none; width:100%; margin-top:12px; padding:12px; background:#fff; border:1px solid #ddd; border-radius:8px;">
            <p><strong>Email:</strong> {{ $tech->email }}</p>
            <p><strong>Phone:</strong> {{ $tech->phone }}</p>
            <p><strong>Region:</strong> {{ $tech->region ?: 'Not provided' }}</p>
            <p><strong>District:</strong> {{ $tech->district ?: 'Not provided' }}</p>
            <p><strong>Base:</strong> {{ $tech->tech_base ?: 'Not provided' }}</p>
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

<div class="card">
    <h3>All Customers</h3>

    <div class="user-list">
    @forelse($customers as $customer)
    <div class="technician-row user-click-row" style="cursor:pointer; flex-wrap:wrap;" data-type="Customer">
        <div>
            <strong>{{ $customer->name }}</strong>
            @if($customer->is_approved)
                <span class="approval-badge approved">Approved</span>
            @else
                <span class="approval-badge pending-approval">Pending approval</span>
            @endif
        </div>

        <div class="technician-actions" onclick="event.stopPropagation()">
            @if(! $customer->is_approved)
                <form method="POST" action="/manager/user/approve/{{ $customer->id }}" class="inline-action-form">
                    @csrf
                    <button type="submit" class="approve-action">Approve</button>
                </form>
            @else
                <form method="POST" action="/manager/user/deactivate/{{ $customer->id }}" class="inline-action-form">
                    @csrf
                    <button type="submit" class="deactivate-action">Deactivate</button>
                </form>
            @endif

            <details>
                <summary>Edit</summary>
                <form method="POST" action="/manager/customer/update/{{ $customer->id }}" class="technician-panel">
                    @csrf

                    <input type="text" name="name" value="{{ $customer->name }}" required>
                    <input type="email" name="email" value="{{ $customer->email }}" required>
                    <input type="text" name="phone" value="{{ $customer->phone }}" required>
                    @include('partials.location-fields', [
                        'selectedRegion' => auth()->user()->region,
                        'selectedDistrict' => $customer->district,
                        'regionReadonly' => true,
                    ])
                    <input type="text" name="ward" value="{{ $customer->ward }}" required>
                    <input type="text" name="street" value="{{ $customer->street }}" required>

                    <button>Update</button>
                </form>
            </details>

            <form method="POST" action="/manager/customer/delete/{{ $customer->id }}" class="inline-action-form" onsubmit="return confirm('Delete this customer?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger-action">Delete</button>
            </form>
        </div>

        <div class="user-inline-details" style="display:none; width:100%; margin-top:12px; padding:12px; background:#fff; border:1px solid #ddd; border-radius:8px;">
            <p><strong>Email:</strong> {{ $customer->email }}</p>
            <p><strong>Phone:</strong> {{ $customer->phone }}</p>
            <p><strong>Region:</strong> {{ $customer->region ?: 'Not provided' }}</p>
            <p><strong>District:</strong> {{ $customer->district ?: 'Not provided' }}</p>
            <p><strong>Ward:</strong> {{ $customer->ward ?: 'Not provided' }}</p>
            <p><strong>Street:</strong> {{ $customer->street ?: 'Not provided' }}</p>
        </div>
    </div>
    @empty
    <p>No customers found</p>
    @endforelse
    </div>
</div>

@endif

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.user-click-row').forEach(function(row){
        row.addEventListener('click', function(){
            row.classList.toggle('selected');
            var details = row.querySelector('.user-inline-details');
            if (details) {
                details.style.display = details.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
});
</script>

@if(in_array($page, ['all_faults', 'assigned']) && $faults->hasPages())
    <div class="pagination">{{ $faults->onEachSide(1)->links() }}</div>
@endif

@if($page === 'technicians' && $technicians->hasPages())
    <div class="pagination">{{ $technicians->onEachSide(1)->links() }}</div>
@endif

@if($page === 'customers' && $customers->hasPages())
    <div class="pagination">{{ $customers->onEachSide(1)->links() }}</div>
@endif

@endsection
