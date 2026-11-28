@php $active='parcels'; $pageTitle='Land Parcel Monitoring'; $pageSubtitle='Search and filter mapped land survey records by municipality, barangay, land type and status.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Total Surveys</div><div class="sc-val">{{ $total }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validated }}</div></div>
    <div class="stat-card amber"><div class="sc-label">For Review</div><div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $pending }}</div></div>
</div>

<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-layer-group" style="color:#1a3d1a;margin-right:7px"></i>Land Survey Registry</div>
        <div class="card-hdr-sub">All LSN records with lot details — filter and search below</div>
    </div>

    {{-- Filters --}}
    <div class="filter-row">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="parcelSearch" placeholder="Search LSN, owner, municipality…" oninput="filterParcels()">
        </div>
        <select class="filter-select" id="filterMuni" onchange="filterParcels()">
            <option value="">All municipalities</option>
            @foreach($municipalities as $m)<option value="{{ strtolower($m) }}">{{ $m }}</option>@endforeach
        </select>
        <select class="filter-select" id="filterBrgy" onchange="filterParcels()">
            <option value="">All barangays</option>
            @foreach($barangays->filter(fn($b) => $b && $b !== 'N/A') as $b)
            <option value="{{ strtolower($b) }}">{{ $b }}</option>
            @endforeach
        </select>
        <select class="filter-select" id="filterType" onchange="filterParcels()">
            <option value="">All land types</option>
            @foreach($landTypes as $t)<option value="{{ strtolower($t) }}">{{ ucfirst($t) }}</option>@endforeach
        </select>
        <select class="filter-select" id="filterStatus" onchange="filterParcels()">
            <option value="">All statuses</option>
            <option value="validated">Validated</option>
            <option value="for_review">For Review</option>
            <option value="pending">Pending</option>
        </select>
    </div>

    <div style="padding:8px 18px;font-size:11.5px;color:#888;background:#fafafa;border-bottom:1px solid #f0f0f0">
        <span id="parcelCount">{{ $total }} records shown</span>
    </div>

    <div style="overflow-x:auto">
    <table class="mon-table" id="parcelTable">
        <thead>
            <tr>
                <th>LSN</th>
                <th>Original Owner</th>
                <th>Municipality</th>
                <th>Barangay</th>
                <th style="text-align:center">Lots</th>
                <th style="text-align:right">Area (ha)</th>
                <th>Land Types</th>
                <th style="text-align:center">Attachments</th>
                <th>Date Registered</th>
                <th>Status</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody id="parcelBody">
            @forelse($surveys as $s)
            <tr data-search="{{ strtolower($s->lsn.' '.$s->original_owner.' '.$s->municipality.' '.$s->barangay) }}"
                data-status="{{ $s->status }}"
                data-muni="{{ strtolower($s->municipality) }}"
                data-brgy="{{ strtolower($s->barangay) }}"
                data-type="{{ strtolower(implode(' ',$s->land_types)) }}">
                <td>
                    <div style="font-weight:700;color:#1a3d1a;font-size:12.5px">{{ $s->lsn }}</div>
                    @if($s->notes)
                    <div style="font-size:10.5px;color:#bbb;margin-top:1px" title="{{ $s->notes }}">{{ Str::limit($s->notes, 40) }}</div>
                    @endif
                </td>
                <td style="font-weight:600;color:#333">{{ $s->original_owner }}</td>
                <td>{{ $s->municipality }}</td>
                <td style="font-size:12px;color:#666">{{ $s->barangay !== 'N/A' ? $s->barangay : '—' }}</td>
                <td style="text-align:center">
                    <span style="display:inline-block;padding:2px 8px;border-radius:8px;background:#e8edf5;color:#1a3d1a;font-size:11px;font-weight:700">{{ $s->lot_count }}</span>
                </td>
                <td style="text-align:right;font-weight:700;color:#333">{{ number_format($s->area_ha,4) }}</td>
                <td>
                    @foreach(array_unique($s->land_types) as $lt)
                    @php
                        $ltColors = ['agricultural'=>'#e8f5e9','residential'=>'#e3f2fd','commercial'=>'#fff3e0','industrial'=>'#f3e5f5','government'=>'#e0f7fa','other'=>'#f5f5f5'];
                        $ltText   = ['agricultural'=>'#2e7d32','residential'=>'#1565c0','commercial'=>'#e65100','industrial'=>'#6a1b9a','government'=>'#00838f','other'=>'#666'];
                    @endphp
                    <span style="display:inline-block;padding:2px 7px;border-radius:6px;font-size:10.5px;font-weight:600;background:{{ $ltColors[$lt]??'#f5f5f5' }};color:{{ $ltText[$lt]??'#555' }};margin-right:2px">{{ ucfirst($lt) }}</span>
                    @endforeach
                    @if(empty($s->land_types))<span style="font-size:11px;color:#ccc">—</span>@endif
                </td>
                <td style="text-align:center">
                    @if($s->attachments > 0)
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:8px;background:#e8f5e9;color:#2e7d32;font-size:11px;font-weight:700">
                        <i class="fas fa-paperclip"></i> {{ $s->attachments }}
                    </span>
                    @else
                    <span style="font-size:11px;color:#ccc">None</span>
                    @endif
                </td>
                <td style="font-size:12px;color:#888">
                    {{ $s->date_registered ? \Carbon\Carbon::parse($s->date_registered)->format('M j, Y') : '—' }}
                </td>
                <td>
                    <span style="display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;
                        background:{{ $s->status==='validated'?'#e8f5e9':($s->status==='for_review'?'#fff8e1':'#ffebee') }};
                        color:{{ $s->status==='validated'?'#2e7d32':($s->status==='for_review'?'#e65100':'#c62828') }}">
                        {{ ucfirst(str_replace('_',' ',$s->status)) }}
                    </span>
                </td>
                <td style="font-size:12px;color:#888">{{ $s->updated_at->format('M j, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center;padding:36px;color:#bbb;font-size:13px">No survey records yet. Add surveys in Land Records.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@push('scripts')
<script>
function filterParcels() {
    const q  = (document.getElementById('parcelSearch').value || '').toLowerCase();
    const mu = (document.getElementById('filterMuni').value   || '').toLowerCase();
    const br = (document.getElementById('filterBrgy').value   || '').toLowerCase();
    const ty = (document.getElementById('filterType').value   || '').toLowerCase();
    const st = (document.getElementById('filterStatus').value || '').toLowerCase();
    let shown = 0;
    document.querySelectorAll('#parcelBody tr[data-search]').forEach(row => {
        const mq = !q  || row.dataset.search.includes(q);
        const mm = !mu || row.dataset.muni === mu;
        const mb = !br || row.dataset.brgy === br;
        const mt = !ty || row.dataset.type.includes(ty);
        const ms = !st || row.dataset.status === st;
        const show = mq && mm && mb && mt && ms;
        row.style.display = show ? '' : 'none';
        if (show) shown++;
    });
    document.getElementById('parcelCount').textContent = shown + ' record' + (shown !== 1 ? 's' : '') + ' shown';
}
</script>
@endpush
@endsection
