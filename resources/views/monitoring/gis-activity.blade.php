@php $active='activity'; $pageTitle='GIS Activity History'; $pageSubtitle='View geographic record activity and location-linked changes.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">GIS Events</div><div class="sc-val">{{ $gisEvents }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validation Events</div><div class="sc-val" style="color:#2e7d32">{{ $validEvents }}</div></div>
    <div class="stat-card amber"><div class="sc-label">Review Updates</div><div class="sc-val" style="color:#f59e0b">{{ $reviewEvents }}</div></div>
    <div class="stat-card red"><div class="sc-label">Location Checks</div><div class="sc-val" style="color:#e53935">{{ $locChecks }}</div></div>
</div>

<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title">GIS Monitoring Activity Log</div>
        <div class="card-hdr-sub">Illustrative record events for land parcel monitoring</div>
    </div>
    <div id="activityList">
        @forelse($activities as $ev)
        <div class="act-item">
            <div class="act-dot {{ $ev['status'] }}"></div>
            <div>
                <div class="act-title">{{ $ev['action'] }}</div>
                <div class="act-sub">
                    <a href="{{ route('monitoring.parcels') }}" style="color:#1a3d1a;font-weight:600;text-decoration:none">{{ $ev['ref'] }}</a>
                    &bull; {{ $ev['municipality'] }} &bull; {{ $ev['unit'] }} &bull; {{ $ev['date']->format('M j, Y') }}
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center;color:#bbb;padding:28px;font-size:13px">No activity recorded yet. Add surveys in the Land Records module.</div>
        @endforelse
    </div>
</div>
@endsection
