@php $active='overview'; $pageTitle='GIS Overview'; $pageSubtitle='Geospatial land records and parcel monitoring.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Sample Parcels</div><div class="sc-val">{{ $total }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validated }}</div></div>
    <div class="stat-card amber"><div class="sc-label">For Review</div><div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $pending }}</div></div>
</div>

<div class="proto-banner">
    <i class="fas fa-info-circle" style="margin-right:5px;color:#f59e0b"></i>
    <strong>GIS prototype:</strong> The map is a schematic illustration. Parcel locations, areas, ownership references and counts are fictional examples — not verified DAR GIS records or official Pangasinan boundary data.
</div>

<!-- GIS Monitoring Alerts -->
@if(!empty($alerts))
<div class="card" style="margin-top:16px">
    <div class="card-hdr">
        <div class="card-hdr-title">GIS Monitoring Alerts</div>
        <div class="card-hdr-sub">Parcels needing location or document verification</div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;padding:16px 20px">
        @foreach(array_slice($alerts, 0, 6) as $a)
        <div style="background:{{ $a['status']==='for_review' ? '#fff8e1' : '#ffebee' }};border:1px solid {{ $a['status']==='for_review' ? '#ffe082' : '#ffcdd2' }};border-radius:8px;padding:12px 14px">
            <div style="font-size:12.5px;font-weight:700;color:#1a3d1a;margin-bottom:2px">{{ $a['ref'] }} · {{ $a['municipality'] }}</div>
            <div style="font-size:11.5px;color:#888;margin-bottom:8px">{{ $a['status']==='for_review' ? 'Boundary attachment under review' : 'Location reference requires verification' }}</div>
            <span class="badge badge-{{ $a['status'] }}">{{ $a['status']==='for_review' ? 'For Review' : 'Pending' }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- GIS Map Filters -->
<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title">GIS Map Filters</div>
        <div class="card-hdr-sub">Filter map markers and parcel records by municipality, barangay and status</div>
    </div>
    <div style="padding:14px 20px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px">
            <div style="flex:1;min-width:200px;position:relative">
                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#bbb;font-size:12px"></i>
                <input type="text" id="overviewSearch" placeholder="Search parcel, municipality or personnel"
                       oninput="filterOverview(this.value)"
                       style="width:100%;padding:8px 10px 8px 30px;border:1px solid #e0e0e0;border-radius:7px;font-size:12.5px;outline:none">
            </div>
            <select id="overviewMuni" onchange="filterOverview(document.getElementById('overviewSearch').value)"
                    style="padding:8px 10px;border:1px solid #e0e0e0;border-radius:7px;font-size:12.5px;background:white;outline:none">
                <option value="">All municipalities</option>
                @foreach($municipalities as $m)<option value="{{ strtolower($m) }}">{{ $m }}</option>@endforeach
            </select>
            <select id="overviewStatus" onchange="filterOverview(document.getElementById('overviewSearch').value)"
                    style="padding:8px 10px;border:1px solid #e0e0e0;border-radius:7px;font-size:12.5px;background:white;outline:none">
                <option value="">All statuses</option>
                <option value="validated">Validated</option>
                <option value="for_review">For Review</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        <div id="overviewCount" style="font-size:11.5px;color:#888">{{ $total }} of {{ $total }} sample parcels shown</div>
    </div>

    <!-- Schematic map + parcel details -->
    <div style="display:grid;grid-template-columns:1fr 320px;gap:0;border-top:1px solid #f0f0f0">

        <!-- Schematic map -->
        <div style="padding:16px 20px;border-right:1px solid #f0f0f0">
            <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap">
                <button class="map-filter-btn active" onclick="setMapFilter('all',this)">All Parcels</button>
                <button class="map-filter-btn" onclick="setMapFilter('validated',this)">Validated</button>
                <button class="map-filter-btn" onclick="setMapFilter('for_review',this)">For Review</button>
                <button class="map-filter-btn" onclick="setMapFilter('pending',this)">Pending</button>
            </div>
            <div style="background:#e8f5e9;border-radius:12px;position:relative;min-height:320px;overflow:hidden;border:1px solid #c8e6c9">
                <!-- Pangasinan schematic -->
                <div style="padding:16px 20px 10px;font-size:11px;font-weight:800;color:#666;letter-spacing:1px;text-transform:uppercase">Pangasinan &bull; Schematic View</div>
                <svg viewBox="0 0 500 340" style="width:100%;max-height:280px;display:block">
                    <!-- Province shape -->
                    <polygon points="80,60 160,30 280,20 400,50 460,120 440,220 380,280 260,300 140,280 60,200 50,130" fill="#c8e6c9" stroke="#a5d6a7" stroke-width="1.5" fill-opacity="0.7"/>
                    <!-- Roads -->
                    <line x1="50" y1="200" x2="460" y2="200" stroke="#bdbdbd" stroke-width="1" stroke-dasharray="6,3"/>
                    <line x1="250" y1="20" x2="250" y2="300" stroke="#bdbdbd" stroke-width="1" stroke-dasharray="6,3"/>
                    <!-- City dots -->
                    @php
                        $mapPoints = [
                            'Urdaneta City'  => [290, 165],
                            'Dagupan City'   => [220, 115],
                            'San Carlos City'=> [160, 195],
                            'Bayambang'      => [230, 230],
                            'Alaminos City'  => [100, 170],
                            'Mangaldan'      => [270, 90],
                        ];
                        $statusColors = ['validated'=>'#2e7d32','for_review'=>'#f59e0b','pending'=>'#e53935'];
                    @endphp
                    @foreach($parcels as $p)
                    @php
                        $muni = $p['municipality'];
                        $coords = $mapPoints[$muni] ?? [250+rand(-80,80), 170+rand(-60,60)];
                        $col = $statusColors[$p['status']] ?? '#888';
                    @endphp
                    <circle cx="{{ $coords[0] }}" cy="{{ $coords[1] }}" r="12" fill="white" stroke="{{ $col }}" stroke-width="2.5"/>
                    <circle cx="{{ $coords[0] }}" cy="{{ $coords[1] }}" r="6" fill="{{ $col }}"/>
                    <text x="{{ $coords[0]+16 }}" y="{{ $coords[1]+4 }}" font-size="10" fill="#333" font-family="Segoe UI,sans-serif">{{ $muni }}</text>
                    @endforeach
                </svg>
                <div style="padding:10px 16px;display:flex;gap:14px;font-size:11px;color:#555">
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#2e7d32;margin-right:4px"></span>Validated</span>
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#f59e0b;margin-right:4px"></span>For Review</span>
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#e53935;margin-right:4px"></span>Pending</span>
                </div>
                <div style="padding:0 16px 10px;font-size:10.5px;color:#aaa;font-style:italic">Illustrative positions only &bull; Not official parcel boundaries or coordinates</div>
            </div>
        </div>

        <!-- Parcel list + details panel -->
        <div>
            <div style="max-height:380px;overflow-y:auto;border-bottom:1px solid #f0f0f0" id="parcelListPanel">
                @foreach($parcels as $p)
                <div class="parcel-list-item" data-id="{{ $p['id'] }}" data-status="{{ $p['status'] }}"
                     data-search="{{ strtolower($p['ref'].' '.$p['municipality'].' '.$p['owner']) }}"
                     onclick="selectParcel(this)"
                     style="padding:10px 14px;border-bottom:1px solid #f7f7f7;cursor:pointer;transition:background 0.1s">
                    <div style="font-size:12.5px;font-weight:700;color:#1a3d1a">{{ $p['municipality'] }}</div>
                    <div style="font-size:11.5px;color:#888">{{ $p['ref'] }} &bull; {{ $p['area_ha'] }} ha</div>
                    <span class="badge badge-{{ $p['status'] }}" style="margin-top:3px;display:inline-block">{{ match($p['status']){'validated'=>'Validated','for_review'=>'For Review','pending'=>'Pending',default=>'—'} }}</span>
                </div>
                @endforeach
            </div>

            <!-- Selected parcel detail (shows first by default) -->
            @if(!empty($parcels))
            @php $first = $parcels[0]; @endphp
            <div id="parcelDetail" style="padding:14px">
                <div style="font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Selected parcel – GIS {{ now()->year }} 001</div>
                <div id="parcelDetailContent">@include('monitoring._parcel-detail', ['p'=>$first])</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- GIS Monitoring Tools -->
<div class="card">
    <div style="padding:16px 20px 10px">
        <div style="font-size:14px;font-weight:700;color:#111;margin-bottom:2px">GIS Monitoring Tools</div>
        <div style="font-size:11.5px;color:#2e7d32;margin-bottom:14px">View-only navigation for the GIS module</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:0 20px 20px">
        <a href="{{ route('monitoring.parcels') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:#1a3d1a;color:white;border-radius:8px;text-decoration:none;font-size:13.5px;font-weight:700;transition:background 0.15s" onmouseover="this.style.background='#245c24'" onmouseout="this.style.background='#1a3d1a'">
            View Parcel Monitoring &rarr;
        </a>
        <a href="{{ route('monitoring.reports') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:#1a3d1a;color:white;border-radius:8px;text-decoration:none;font-size:13.5px;font-weight:700;transition:background 0.15s" onmouseover="this.style.background='#245c24'" onmouseout="this.style.background='#1a3d1a'">
            View GIS Reports &rarr;
        </a>
    </div>
</div>

<style>
.map-filter-btn{padding:5px 14px;border-radius:20px;border:1.5px solid #ccc;background:white;color:#555;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.15s}
.map-filter-btn.active{background:#1a3d1a;color:white;border-color:#1a3d1a}
.map-filter-btn:hover:not(.active){border-color:#1a3d1a;color:#1a3d1a}
.parcel-list-item:hover{background:#f0f9f0}
.parcel-list-item.selected{background:#e8f5e9}
</style>

@push('scripts')
<script>
const parcels = @json($parcels);

function filterOverview(q) {
    q = (q||'').toLowerCase();
    const muni   = document.getElementById('overviewMuni').value;
    const status = document.getElementById('overviewStatus').value;
    let shown = 0;
    document.querySelectorAll('#parcelListPanel .parcel-list-item').forEach(row => {
        const mq = !q      || row.dataset.search.includes(q);
        const mm = !muni   || row.dataset.search.includes(muni);
        const ms = !status || row.dataset.status === status;
        const show = mq && mm && ms;
        row.style.display = show ? '' : 'none';
        if (show) shown++;
    });
    document.getElementById('overviewCount').textContent = shown + ' of {{ $total }} sample parcels shown';
}

function setMapFilter(f, btn) {
    document.querySelectorAll('.map-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#parcelListPanel .parcel-list-item').forEach(row => {
        row.style.display = (f==='all' || row.dataset.status===f) ? '' : 'none';
    });
}

function selectParcel(el) {
    document.querySelectorAll('.parcel-list-item').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
}
</script>
@endpush
@endsection
