@php $active='activity'; $pageTitle='GIS Activity History'; $pageSubtitle='Survey record events, status changes, and lot-level activity from the GIS system.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Total Records</div><div class="sc-val">{{ $gisEvents }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validEvents }}</div></div>
    <div class="stat-card amber"><div class="sc-label">For Review</div><div class="sc-val" style="color:#f59e0b">{{ $reviewEvents }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $locChecks }}</div></div>
</div>

{{-- Search --}}
<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:200px;position:relative">
        <i class="fas fa-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#bbb;font-size:12px"></i>
        <input type="text" id="actSearch" placeholder="Search LSN, owner, municipality…" oninput="filterActivity()"
               style="width:100%;padding:9px 12px 9px 32px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:12.5px;outline:none;background:white">
    </div>
    <select id="actStatus" onchange="filterActivity()"
            style="padding:9px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:12.5px;background:white;outline:none">
        <option value="">All statuses</option>
        <option value="validated">Validated</option>
        <option value="for_review">For Review</option>
        <option value="pending">Pending</option>
    </select>
</div>

<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-history" style="color:#1a3d1a;margin-right:7px"></i>Survey Activity Log</div>
        <div class="card-hdr-sub" id="actCount">{{ $activities->count() }} records</div>
    </div>
    <div id="activityList">
        @forelse($activities as $ev)
        <div class="act-item" style="padding:14px 18px;border-bottom:1px solid #f7f7f7;display:flex;align-items:flex-start;gap:14px"
             data-search="{{ strtolower($ev['lsn'].' '.$ev['owner'].' '.$ev['municipality']) }}"
             data-status="{{ $ev['status'] }}">
            {{-- Status dot --}}
            <div style="width:38px;height:38px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;margin-top:2px;
                background:{{ $ev['status']==='validated'?'#e8f5e9':($ev['status']==='for_review'?'#fff8e1':'#ffebee') }};
                color:{{ $ev['status']==='validated'?'#2e7d32':($ev['status']==='for_review'?'#f59e0b':'#e53935') }}">
                <i class="fas {{ $ev['status']==='validated'?'fa-check-circle':($ev['status']==='for_review'?'fa-clock':'fa-exclamation-circle') }}"></i>
            </div>
            {{-- Main info --}}
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:700;color:#1a3d1a">{{ $ev['lsn'] }}</div>
                <div style="font-size:12px;color:#555;margin-top:1px">{{ $ev['action'] }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px">
                    <span style="font-size:11px;background:#f5f5f5;padding:2px 8px;border-radius:6px;color:#666"><i class="fas fa-map-marker-alt" style="margin-right:3px;color:#e53935"></i>{{ $ev['municipality'] }}</span>
                    <span style="font-size:11px;background:#f5f5f5;padding:2px 8px;border-radius:6px;color:#666"><i class="fas fa-user" style="margin-right:3px;color:#1a3d1a"></i>{{ $ev['owner'] }}</span>
                    <span style="font-size:11px;background:#f5f5f5;padding:2px 8px;border-radius:6px;color:#666"><i class="fas fa-draw-polygon" style="margin-right:3px;color:#1a3d1a"></i>{{ $ev['lot_count'] }} lot{{ $ev['lot_count']!=1?'s':'' }}</span>
                    <span style="font-size:11px;background:#f5f5f5;padding:2px 8px;border-radius:6px;color:#666"><i class="fas fa-ruler-combined" style="margin-right:3px;color:#1a3d1a"></i>{{ number_format($ev['area_ha'],2) }} ha</span>
                    @if($ev['attachments'] > 0)
                    <span style="font-size:11px;background:#e8f5e9;padding:2px 8px;border-radius:6px;color:#2e7d32"><i class="fas fa-paperclip" style="margin-right:3px"></i>{{ $ev['attachments'] }} attachment{{ $ev['attachments']!=1?'s':'' }}</span>
                    @endif
                    @if($ev['registered'])
                    <span style="font-size:11px;background:#e3eeff;padding:2px 8px;border-radius:6px;color:#1565c0"><i class="fas fa-calendar" style="margin-right:3px"></i>Registered {{ \Carbon\Carbon::parse($ev['registered'])->format('M j, Y') }}</span>
                    @endif
                </div>
            </div>
            {{-- Status + date --}}
            <div style="text-align:right;flex-shrink:0">
                <span style="display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;
                    background:{{ $ev['status']==='validated'?'#e8f5e9':($ev['status']==='for_review'?'#fff8e1':'#ffebee') }};
                    color:{{ $ev['status']==='validated'?'#2e7d32':($ev['status']==='for_review'?'#e65100':'#c62828') }}">
                    {{ ucfirst(str_replace('_',' ',$ev['status'])) }}
                </span>
                <div style="font-size:11px;color:#aaa;margin-top:5px">{{ $ev['date']->format('M j, Y') }}</div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:40px;color:#bbb;font-size:13px">
            <i class="fas fa-history" style="font-size:28px;display:block;margin-bottom:10px"></i>
            No survey activity yet. Add surveys in Land Records.
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function filterActivity() {
    const q  = (document.getElementById('actSearch').value  || '').toLowerCase();
    const st = (document.getElementById('actStatus').value  || '');
    let shown = 0;
    document.querySelectorAll('#activityList .act-item').forEach(row => {
        const mq = !q  || row.dataset.search.includes(q);
        const ms = !st || row.dataset.status === st;
        row.style.display = (mq && ms) ? '' : 'none';
        if (mq && ms) shown++;
    });
    document.getElementById('actCount').textContent = shown + ' record' + (shown !== 1 ? 's' : '');
}
</script>
@endpush
@endsection
