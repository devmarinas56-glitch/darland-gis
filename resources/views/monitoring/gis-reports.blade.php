@php $active='reports'; $pageTitle='GIS Reports'; $pageSubtitle='Geospatial distribution and monitoring summaries.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Parcels Tracked</div><div class="sc-val">{{ $total }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validated }}</div></div>
    <div class="stat-card amber"><div class="sc-label">For Review</div><div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $pending }}</div></div>
</div>

<div class="two-col">
    <!-- Parcel Status Breakdown -->
    <div class="card">
        <div class="card-hdr">
            <div class="card-hdr-title">Parcel Status Breakdown</div>
            <div class="card-hdr-sub">Distribution breakdown of GIS records</div>
        </div>
        <div style="padding:16px 20px">
            @php
                $t = max($total,1);
                $pV = round(($validated/$t)*100);
                $pR = round(($forReview/$t)*100);
                $pP = round(($pending/$t)*100);
            @endphp
            <div class="prog-row">
                <div class="prog-label">Validated</div>
                <div class="prog-track"><div class="prog-fill green" style="width:{{ $pV }}%"></div></div>
                <div class="prog-pct">{{ $pV }}%</div>
            </div>
            <div class="prog-row">
                <div class="prog-label">For Review</div>
                <div class="prog-track"><div class="prog-fill amber" style="width:{{ $pR }}%"></div></div>
                <div class="prog-pct">{{ $pR }}%</div>
            </div>
            <div class="prog-row">
                <div class="prog-label">Pending</div>
                <div class="prog-track"><div class="prog-fill red" style="width:{{ $pP }}%"></div></div>
                <div class="prog-pct">{{ $pP }}%</div>
            </div>
        </div>
    </div>

    <!-- Monitoring by Municipality -->
    <div class="card">
        <div class="card-hdr">
            <div class="card-hdr-title">Monitoring by Municipality</div>
            <div class="card-hdr-sub">Record count by municipality</div>
        </div>
        <div style="padding:16px 20px">
            @php $maxMuni = $byMuni->max('count') ?: 1; @endphp
            @forelse($byMuni as $m)
            @php $barW = round(($m['count']/$maxMuni)*100); @endphp
            <div class="prog-row" style="margin-bottom:6px">
                <div class="prog-label" style="font-size:11px;width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $m['municipality'] }}">{{ Str::limit($m['municipality'],10) }}</div>
                <div class="prog-track" style="height:22px;border-radius:4px">
                    <div class="prog-fill green" style="width:{{ $barW }}%;height:100%;display:flex;align-items:center;padding-left:6px">
                        <span style="font-size:10px;color:white;font-weight:700">{{ $m['count'] }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#bbb;padding:16px;font-size:12px">No municipality data yet.</div>
            @endforelse
            <div style="font-size:11px;color:#aaa;margin-top:6px">Each sample municipality has one illustrative parcel record.</div>
        </div>
    </div>
</div>

<!-- GIS Report Summary -->
<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title">GIS Report Summary</div>
        <div class="card-hdr-sub">Read-only status by parcel reference</div>
    </div>
    <table class="mon-table">
        <thead>
            <tr><th>Parcel</th><th>Municipality</th><th>Area</th><th>Status</th><th>Last Updated</th></tr>
        </thead>
        <tbody>
            @forelse($parcels as $p)
            <tr>
                <td><a href="{{ route('monitoring.parcels') }}" style="color:#1a3d1a">{{ $p['ref'] }}</a></td>
                <td>{{ $p['municipality'] }}</td>
                <td>{{ $p['area_ha'] }} ha</td>
                <td><span class="badge badge-{{ $p['status'] }}">{{ match($p['status']){'validated'=>'Validated','for_review'=>'For Review','pending'=>'Pending',default=>'—'} }}</span></td>
                <td style="font-size:12px;color:#888">{{ $p['updated']->format('M j, Y') }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="5">No parcel records yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
