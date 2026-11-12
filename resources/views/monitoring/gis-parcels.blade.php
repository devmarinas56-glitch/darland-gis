@php $active='parcels'; $pageTitle='Land Parcel Monitoring'; $pageSubtitle='Review mapped land parcels and status by municipality.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Mapped Parcels</div><div class="sc-val">{{ $total }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validated }}</div></div>
    <div class="stat-card amber"><div class="sc-label">Requires Review</div><div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $pending }}</div></div>
</div>

<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title">Land Parcel Registry</div>
        <div class="card-hdr-sub">Illustrative geospatial monitoring records &bull; Search and filter</div>
    </div>

    <div class="filter-row">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="parcelSearch" placeholder="Search parcel, municipality or personnel" oninput="filterParcels(this.value)">
        </div>
        <select class="filter-select" id="parcelMuni" onchange="filterParcels(document.getElementById('parcelSearch').value)">
            <option value="">All municipalities</option>
            @foreach($municipalities as $m)<option value="{{ strtolower($m) }}">{{ $m }}</option>@endforeach
        </select>
        <select class="filter-select" id="parcelBrgy" onchange="filterParcels(document.getElementById('parcelSearch').value)">
            <option value="">All barangays</option>
            @foreach($barangays as $b)<option value="{{ strtolower($b) }}">{{ $b }}</option>@endforeach
        </select>
        <select class="filter-select" id="parcelStatus" onchange="filterParcels(document.getElementById('parcelSearch').value)">
            <option value="">All statuses</option>
            <option value="validated">Validated</option>
            <option value="for_review">For Review</option>
            <option value="pending">Pending</option>
        </select>
    </div>
    <div style="padding:8px 16px;font-size:11.5px;color:#888;background:#fafafa;border-bottom:1px solid #f0f0f0" id="parcelCount">
        {{ $total }} of {{ $total }} sample parcels shown
    </div>

    <table class="mon-table" id="parcelTable">
        <thead>
            <tr>
                <th>Parcel Reference</th>
                <th>Municipality</th>
                <th>Barangay</th>
                <th>Area</th>
                <th>Assigned Unit</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody id="parcelBody">
            @forelse($parcels as $p)
            <tr data-search="{{ strtolower($p['ref'].' '.$p['municipality'].' '.$p['barangay'].' '.$p['owner']) }}"
                data-status="{{ $p['status'] }}"
                data-muni="{{ strtolower($p['municipality']) }}"
                data-brgy="{{ strtolower($p['barangay']) }}">
                <td><a href="#" style="color:#1a3d1a">{{ $p['ref'] }}</a></td>
                <td>{{ $p['municipality'] }}</td>
                <td style="font-size:12px">{{ $p['barangay'] }}</td>
                <td style="font-size:12.5px">{{ $p['area_ha'] }} ha</td>
                <td style="font-size:12px;color:{{ $p['status']==='for_review' ? '#1565c0' : '#444' }}">
                    {{ $p['status']==='for_review' ? 'Field Monitoring Unit' : 'GIS Records Unit' }}
                </td>
                <td><span class="badge badge-{{ $p['status'] }}">{{ match($p['status']){'validated'=>'Validated','for_review'=>'For Review','pending'=>'Pending',default=>'—'} }}</span></td>
                <td style="font-size:12px;color:#888">{{ $p['updated']->format('M j, Y') }}</td>
                <td>
                    <button onclick="alert('Read-only monitoring — parcel detail view coming soon.')"
                            style="padding:5px 12px;background:#e8f5e9;color:#1a3d1a;border:1px solid #c8e6c9;border-radius:6px;cursor:pointer;font-size:11.5px;font-weight:600">
                        View Details
                    </button>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="8">No parcel records yet. Add surveys in the Land Records module.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
function filterParcels(q) {
    q = (q||'').toLowerCase();
    const st = document.getElementById('parcelStatus').value;
    const mu = document.getElementById('parcelMuni').value;
    const br = document.getElementById('parcelBrgy').value;
    let shown=0, total=0;
    document.querySelectorAll('#parcelBody tr[data-search]').forEach(row => {
        total++;
        const mq = !q  || row.dataset.search.includes(q);
        const ms = !st || row.dataset.status === st;
        const mm = !mu || row.dataset.muni === mu;
        const mb = !br || row.dataset.brgy === br;
        const show = mq && ms && mm && mb;
        row.style.display = show ? '' : 'none';
        if (show) shown++;
    });
    document.getElementById('parcelCount').textContent = shown + ' of ' + total + ' sample parcels shown';
}
</script>
@endpush
@endsection
