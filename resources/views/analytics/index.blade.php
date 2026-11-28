@php $active='analytics'; $pageTitle='Analytics'; $pageSubtitle='Land records data, area distribution, lot trends, and municipal breakdown.' @endphp
@extends('monitoring._layout')

@section('content')
<style>
    .stats-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:22px; }
    .stat-card  { background:white; border-radius:12px; padding:18px 20px; box-shadow:0 2px 12px rgba(0,0,0,0.07); }
    .stat-icon  { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-bottom:10px; }
    .stat-icon.blue   { background:#e3eeff; color:#2979ff; }
    .stat-icon.green  { background:#e8f5e9; color:#2e7d32; }
    .stat-icon.amber  { background:#fff8e1; color:#f59e0b; }
    .stat-icon.red    { background:#ffebee; color:#e53935; }
    .stat-icon.teal   { background:#e0f7fa; color:#00838f; }
    .stat-val { font-size:28px; font-weight:800; color:#111; line-height:1; }
    .stat-lbl { font-size:12px; color:#888; margin-top:4px; }
    .stat-sub { font-size:11px; color:#bbb; margin-top:2px; }

    .charts-row   { display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:22px; }
    .charts-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:18px; margin-bottom:22px; }

    .card         { background:white; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.07); overflow:hidden; }
    .card-header  { padding:14px 20px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; }
    .card-title   { font-size:13px; font-weight:700; color:#222; }
    .card-badge   { background:#e8edf5; color:#1a3d1a; border-radius:10px; padding:2px 10px; font-size:11px; font-weight:700; }
    .card-body    { padding:16px 20px; }

    .bar-row   { display:flex; align-items:center; gap:10px; margin-bottom:9px; }
    .bar-label { font-size:12px; color:#555; width:130px; flex-shrink:0; text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .bar-track { flex:1; background:#f0f0f0; border-radius:4px; height:12px; overflow:hidden; }
    .bar-fill  { height:100%; border-radius:4px; }
    .bar-fill.blue  { background:linear-gradient(90deg,#1a3d1a,#2e7d32); }
    .bar-fill.green { background:linear-gradient(90deg,#2e7d32,#4caf50); }
    .bar-count { font-size:11px; color:#888; width:36px; text-align:right; flex-shrink:0; }

    .data-table    { width:100%; border-collapse:collapse; }
    .data-table th { text-align:left; padding:9px 14px; font-size:11px; color:#aaa; font-weight:600; text-transform:uppercase; background:#fafafa; border-bottom:1px solid #f0f0f0; }
    .data-table td { padding:11px 14px; font-size:13px; color:#444; border-bottom:1px solid #f7f7f7; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tr:hover td { background:#fafffe; }
    .badge { display:inline-block; padding:2px 9px; border-radius:8px; font-size:11px; font-weight:600; background:#e8f5e9; color:#1a3d1a; }

    @media(max-width:1200px){ .stats-grid{grid-template-columns:repeat(3,1fr);} .charts-row{grid-template-columns:1fr;} .charts-row-3{grid-template-columns:1fr 1fr;} }
    @media(max-width:800px) { .stats-grid{grid-template-columns:repeat(2,1fr);} .charts-row-3{grid-template-columns:1fr;} }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- ── Stat Cards ── -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div class="stat-val">{{ number_format($totalSurveys) }}</div>
        <div class="stat-lbl">Total Surveys</div>
        <div class="stat-sub">LSN records on file</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-draw-polygon"></i></div>
        <div class="stat-val">{{ number_format($totalLots) }}</div>
        <div class="stat-lbl">Total Lots / Plots</div>
        <div class="stat-sub">Across all surveys</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-ruler-combined"></i></div>
        <div class="stat-val">{{ number_format($totalAreaHa, 1) }}</div>
        <div class="stat-lbl">Total Area (ha)</div>
        <div class="stat-sub">{{ number_format($totalAreaSqm) }} sqm</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-users"></i></div>
        <div class="stat-val">{{ number_format($totalOwners) }}</div>
        <div class="stat-lbl">Unique Plot Owners</div>
        <div class="stat-sub">From lot records</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-map-marker-alt"></i></div>
        <div class="stat-val">{{ number_format($totalMunicipalities) }}</div>
        <div class="stat-lbl">Municipalities</div>
        <div class="stat-sub">With active surveys</div>
    </div>
</div>

<!-- ── Monthly line + Municipality bar ── -->
<div class="charts-row">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line" style="color:#1a3d1a;margin-right:7px"></i>Surveys &amp; Lots Added Per Month</div>
            <span class="card-badge">Last 12 months</span>
        </div>
        <div class="card-body"><canvas id="monthlyChart" height="90"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-map-marker-alt" style="color:#e53935;margin-right:7px"></i>Top Municipalities</div>
            <span class="card-badge">By surveys</span>
        </div>
        <div class="card-body">
            @php $maxMuni = $byMunicipality->max('count') ?: 1; @endphp
            @forelse($byMunicipality->take(10) as $row)
            <div class="bar-row">
                <div class="bar-label">{{ trim($row->municipality) ?: 'Unknown' }}</div>
                <div class="bar-track"><div class="bar-fill blue" style="width:{{ round(($row->count/$maxMuni)*100) }}%"></div></div>
                <div class="bar-count">{{ $row->count }}</div>
            </div>
            @empty
            <div style="text-align:center;color:#bbb;padding:24px;font-size:13px">No data yet</div>
            @endforelse
        </div>
    </div>
</div>

<!-- ── Area + Lot size + Cumulative ── -->
<div class="charts-row-3">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-chart-bar" style="color:#2e7d32;margin-right:7px"></i>Area by Municipality (ha)</div></div>
        <div class="card-body"><canvas id="areaChart" height="150"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-circle-notch" style="color:#1a3d1a;margin-right:7px"></i>Lots per Survey</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;align-items:center;gap:14px">
            <canvas id="lotSizeDonut" width="160" height="160" style="max-width:160px"></canvas>
            <div style="font-size:11px;color:#888;text-align:center">Distribution by lot count</div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-chart-line" style="color:#e65100;margin-right:7px"></i>Cumulative Area Growth</div></div>
        <div class="card-body"><canvas id="cumulChart" height="150"></canvas></div>
    </div>
</div>

<!-- ── Top owners ── -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users" style="margin-right:7px"></i>Top 20 Plot Owners</div>
        <a href="/land-records" style="font-size:12px;color:#1a3d1a;text-decoration:none;font-weight:600">View All →</a>
    </div>
    <table class="data-table">
        <thead>
            <tr><th style="width:40px">#</th><th>Owner Name</th><th>Lots</th><th>Total Area (sqm)</th><th>Total Area (ha)</th></tr>
        </thead>
        <tbody>
            @forelse($topOwners as $i => $owner)
            <tr>
                <td style="color:#aaa;font-size:11px">{{ $i+1 }}</td>
                <td style="font-weight:600;color:#1a3d1a">{{ $owner->owner_name ?: '—' }}</td>
                <td><span class="badge">{{ $owner->lot_count }}</span></td>
                <td>{{ number_format($owner->total_area, 0) }}</td>
                <td style="color:#888">{{ number_format($owner->total_area / 10000, 3) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#bbb;padding:32px">No data yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
const monthLabels  = @json($monthLabels);
const lotCounts    = @json($lotCounts);
const surveyCounts = @json($surveyCounts);

new Chart(document.getElementById('monthlyChart'), {
    type:'line',
    data:{
        labels: monthLabels,
        datasets:[
            { label:'Lots Added',      data:lotCounts,    borderColor:'#2e7d32', backgroundColor:'rgba(46,125,50,0.08)', tension:0.4, pointRadius:3 },
            { label:'Surveys Created', data:surveyCounts, borderColor:'#1a3d1a', backgroundColor:'rgba(26,61,26,0.08)',  tension:0.4, pointRadius:3 },
        ]
    },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ font:{ size:12 } } } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0, font:{ size:11 } } }, x:{ ticks:{ font:{ size:11 } } } } }
});

const muniLabels = @json($byMunicipality->take(8)->pluck('municipality'));
const muniAreas  = @json($byMunicipality->take(8)->map(fn($r)=>round($r->total_area/10000,2)));
new Chart(document.getElementById('areaChart'), {
    type:'bar',
    data:{ labels: muniLabels.map(l=>l||'Unknown'), datasets:[{ label:'Area (ha)', data:muniAreas, backgroundColor:'rgba(46,125,50,0.75)', borderRadius:4 }] },
    options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0, font:{ size:10 } } }, x:{ ticks:{ font:{ size:9 }, maxRotation:40 } } } }
});

const lotSizeData = @json($lotSizeBuckets);
new Chart(document.getElementById('lotSizeDonut'), {
    type:'doughnut',
    data:{ labels:['1 lot','2–5 lots','6–10 lots','11+ lots'], datasets:[{ data:lotSizeData, backgroundColor:['#1a3d1a','#2e7d32','#4caf50','#a5d6a7'], borderWidth:2, borderColor:'white' }] },
    options:{ responsive:false, plugins:{ legend:{ position:'bottom', labels:{ font:{ size:10 } } } }, cutout:'60%' }
});

const cumulLabels = @json($cumulMonths);
const cumulValues = @json($cumulAreas);
new Chart(document.getElementById('cumulChart'), {
    type:'line',
    data:{ labels:cumulLabels, datasets:[{ label:'Cumul. Area (ha)', data:cumulValues, borderColor:'#e65100', backgroundColor:'rgba(230,81,0,0.08)', tension:0.4, fill:true, pointRadius:2 }] },
    options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0, font:{ size:11 } } }, x:{ ticks:{ font:{ size:9 }, maxRotation:40 } } } }
});
</script>
@endsection
