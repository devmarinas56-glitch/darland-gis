@php $active='analytics'; $pageTitle='Analytics'; $pageSubtitle='Land records data, area distribution, land types, and municipal breakdown.' @endphp
@extends('monitoring._layout')

@section('content')
<style>
    /* ── Stats grid ── */
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

    /* ── Layout rows ── */
    .row-2   { display:grid; grid-template-columns:1fr 1fr;   gap:18px; margin-bottom:22px; }
    .row-2l  { display:grid; grid-template-columns:2fr 1fr;   gap:18px; margin-bottom:22px; }
    .row-3   { display:grid; grid-template-columns:1fr 1fr 1fr; gap:18px; margin-bottom:22px; }

    /* ── Card ── */
    .card         { background:white; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.07); overflow:hidden; }
    .card-header  { padding:14px 20px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; }
    .card-title   { font-size:13px; font-weight:700; color:#222; }
    .card-badge   { background:#e8f5e9; color:#1a3d1a; border-radius:10px; padding:2px 10px; font-size:11px; font-weight:700; }
    .card-body    { padding:16px 20px; }

    /* ── Bar rows ── */
    .bar-row   { display:flex; align-items:center; gap:10px; margin-bottom:9px; }
    .bar-label { font-size:12px; color:#555; width:120px; flex-shrink:0; text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .bar-track { flex:1; background:#f0f0f0; border-radius:4px; height:12px; overflow:hidden; }
    .bar-fill  { height:100%; border-radius:4px; }
    .bar-count { font-size:11px; color:#888; width:36px; text-align:right; flex-shrink:0; }

    /* ── Municipality table ── */
    .muni-table    { width:100%; border-collapse:collapse; }
    .muni-table th { text-align:left; padding:9px 14px; font-size:11px; color:#aaa; font-weight:700; text-transform:uppercase; background:#fafafa; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
    .muni-table td { padding:11px 14px; font-size:12.5px; color:#444; border-bottom:1px solid #f7f7f7; vertical-align:middle; }
    .muni-table tr:last-child td { border-bottom:none; }
    .muni-table tr:hover td { background:#f9fff9; }
    .muni-name { font-weight:700; color:#1a3d1a; }
    .muni-bar-wrap { display:flex; align-items:center; gap:8px; }
    .muni-bar-track { flex:1; background:#f0f0f0; border-radius:3px; height:8px; min-width:60px; overflow:hidden; }
    .muni-bar-fill  { height:100%; background:linear-gradient(90deg,#1a3d1a,#2e7d32); border-radius:3px; }

    /* ── Owners table ── */
    .data-table    { width:100%; border-collapse:collapse; }
    .data-table th { text-align:left; padding:9px 14px; font-size:11px; color:#aaa; font-weight:700; text-transform:uppercase; background:#fafafa; border-bottom:1px solid #f0f0f0; }
    .data-table td { padding:10px 14px; font-size:12.5px; color:#444; border-bottom:1px solid #f7f7f7; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:none; }
    .data-table tr:hover td { background:#f9fff9; }
    .badge { display:inline-block; padding:2px 8px; border-radius:8px; font-size:11px; font-weight:600; background:#e8f5e9; color:#1a3d1a; }

    /* ── Land type legend ── */
    .type-legend { display:flex; flex-direction:column; gap:8px; margin-top:14px; }
    .type-row { display:flex; align-items:center; gap:10px; }
    .type-dot { width:12px; height:12px; border-radius:3px; flex-shrink:0; }
    .type-label { font-size:12px; color:#444; flex:1; }
    .type-count { font-size:11px; color:#888; font-weight:600; }
    .type-pct   { font-size:11px; color:#aaa; }

    @media(max-width:1200px) { .stats-grid{grid-template-columns:repeat(3,1fr);} .row-2l{grid-template-columns:1fr;} .row-3{grid-template-columns:1fr 1fr;} }
    @media(max-width:800px)  { .stats-grid{grid-template-columns:repeat(2,1fr);} .row-2,.row-3{grid-template-columns:1fr;} }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- ── Stat Cards ── --}}
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

{{-- ── Monthly line + Land Type pie ── --}}
<div class="row-2l">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line" style="color:#1a3d1a;margin-right:7px"></i>Surveys &amp; Lots Added Per Month</div>
            <span class="card-badge">Last 12 months</span>
        </div>
        <div class="card-body"><canvas id="monthlyChart" height="100"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-pie" style="color:#1a3d1a;margin-right:7px"></i>Land Type Distribution</div>
            <span class="card-badge">By plots</span>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;align-items:center">
            @php $totalLotCount = $landTypes->sum('count') ?: 1; @endphp
            <canvas id="landTypePie" width="180" height="180" style="max-width:180px"></canvas>
            <div class="type-legend" style="width:100%;margin-top:12px">
                @foreach($landTypes as $lt)
                @php
                    $cfg   = $typeConfig[$lt->land_type] ?? ['label'=>ucfirst($lt->land_type),'color'=>'#90a4ae'];
                    $pct   = round($lt->count / $totalLotCount * 100, 1);
                    $ha    = round($lt->total_area / 10000, 2);
                @endphp
                <div class="type-row">
                    <div class="type-dot" style="background:{{ $cfg['color'] }}"></div>
                    <div class="type-label">{{ $cfg['label'] }}</div>
                    <div class="type-count">{{ number_format($lt->count) }} plots</div>
                    <div class="type-pct">{{ $pct }}%</div>
                </div>
                @endforeach
                @if($landTypes->isEmpty())
                <div style="text-align:center;color:#bbb;font-size:12px;padding:12px 0">No data yet — set land type when adding plots.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Municipality Breakdown table ── --}}
<div class="card" style="margin-bottom:22px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-map-marker-alt" style="color:#e53935;margin-right:7px"></i>Municipality Breakdown</div>
        <span class="card-badge">{{ $muniDetails->count() }} municipalities</span>
    </div>
    @if($muniDetails->isEmpty())
    <div style="text-align:center;padding:36px;color:#bbb;font-size:13px"><i class="fas fa-map" style="font-size:24px;display:block;margin-bottom:8px"></i>No data yet.</div>
    @else
    @php $maxPlots = $muniDetails->max('plot_count') ?: 1; @endphp
    <div style="overflow-x:auto">
    <table class="muni-table">
        <thead>
            <tr>
                <th style="width:36px">#</th>
                <th>Municipality / City</th>
                <th style="text-align:right">Surveys</th>
                <th style="text-align:right">Plots</th>
                <th style="text-align:right">Owners</th>
                <th style="text-align:right">Area (ha)</th>
                <th style="min-width:140px">Plot Distribution</th>
            </tr>
        </thead>
        <tbody>
            @foreach($muniDetails as $i => $muni)
            <tr>
                <td style="color:#aaa;font-size:11px">{{ $i+1 }}</td>
                <td><span class="muni-name">{{ $muni->municipality }}</span></td>
                <td style="text-align:right"><span class="badge">{{ number_format($muni->survey_count) }}</span></td>
                <td style="text-align:right;font-weight:700;color:#1a3d1a">{{ number_format($muni->plot_count) }}</td>
                <td style="text-align:right;color:#555">{{ number_format($muni->owner_count) }}</td>
                <td style="text-align:right;color:#888">{{ number_format($muni->total_area_sqm / 10000, 2) }}</td>
                <td>
                    <div class="muni-bar-wrap">
                        <div class="muni-bar-track">
                            <div class="muni-bar-fill" style="width:{{ round($muni->plot_count / $maxPlots * 100) }}%"></div>
                        </div>
                        <span style="font-size:10.5px;color:#aaa;white-space:nowrap">{{ round($muni->plot_count / $totalLots * 100, 1) }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

{{-- ── Area by municipality + Cumulative ── --}}
<div class="row-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-bar" style="color:#2e7d32;margin-right:7px"></i>Area by Municipality (ha)</div>
        </div>
        <div class="card-body">
            @php $maxMuni = $byMunicipality->max('total_area') ?: 1; @endphp
            @forelse($byMunicipality->take(10) as $row)
            <div class="bar-row">
                <div class="bar-label">{{ trim($row->municipality) ?: 'Unknown' }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ round(($row->total_area/$maxMuni)*100) }}%;background:linear-gradient(90deg,#1a3d1a,#4caf50)"></div>
                </div>
                <div class="bar-count" style="width:60px;font-size:10.5px">{{ number_format($row->total_area/10000,1) }} ha</div>
            </div>
            @empty
            <div style="text-align:center;color:#bbb;padding:24px;font-size:13px">No data yet</div>
            @endforelse
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line" style="color:#e65100;margin-right:7px"></i>Cumulative Area Growth</div>
        </div>
        <div class="card-body"><canvas id="cumulChart" height="160"></canvas></div>
    </div>
</div>

{{-- ── Top owners ── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users" style="margin-right:7px"></i>Top 20 Plot Owners</div>
        <a href="/land-records" style="font-size:12px;color:#1a3d1a;text-decoration:none;font-weight:600">View All →</a>
    </div>
    <table class="data-table">
        <thead>
            <tr><th style="width:40px">#</th><th>Owner Name</th><th>Plots</th><th>Total Area (sqm)</th><th>Total Area (ha)</th></tr>
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
// Monthly line chart
const monthLabels  = @json($monthLabels);
const lotCounts    = @json($lotCounts);
const surveyCounts = @json($surveyCounts);
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [
            { label:'Lots Added',      data:lotCounts,    borderColor:'#2e7d32', backgroundColor:'rgba(46,125,50,0.08)', tension:0.4, pointRadius:3 },
            { label:'Surveys Created', data:surveyCounts, borderColor:'#1a3d1a', backgroundColor:'rgba(26,61,26,0.08)',  tension:0.4, pointRadius:3 },
        ]
    },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ font:{ size:12 } } } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0, font:{ size:11 } } }, x:{ ticks:{ font:{ size:11 } } } } }
});

// Land type pie chart
@php
    $typeLabels = $landTypes->map(fn($lt) => ($typeConfig[$lt->land_type]['label'] ?? ucfirst($lt->land_type)))->values();
    $typeCounts = $landTypes->pluck('count')->values();
    $typeColors = $landTypes->map(fn($lt) => $typeConfig[$lt->land_type]['color'] ?? '#90a4ae')->values();
    $typeHa     = $landTypes->map(fn($lt) => round($lt->total_area / 10000, 2))->values();
@endphp
const landTypePieData = {
    labels: @json($typeLabels),
    counts: @json($typeCounts),
    colors: @json($typeColors),
    ha:     @json($typeHa),
};
new Chart(document.getElementById('landTypePie'), {
    type: 'pie',
    data: {
        labels: landTypePieData.labels,
        datasets:[{
            data:            landTypePieData.counts,
            backgroundColor: landTypePieData.colors,
            borderWidth: 2,
            borderColor: 'white',
        }]
    },
    options:{
        responsive: false,
        plugins:{
            legend:{ display:false },
            tooltip:{
                callbacks:{
                    label: function(ctx) {
                        const ha = landTypePieData.ha[ctx.dataIndex];
                        return ` ${ctx.label}: ${ctx.formattedValue} plots (${ha} ha)`;
                    }
                }
            }
        }
    }
});

// Cumulative area growth
const cumulLabels = @json($cumulMonths);
const cumulValues = @json($cumulAreas);
new Chart(document.getElementById('cumulChart'), {
    type: 'line',
    data:{ labels:cumulLabels, datasets:[{ label:'Cumul. Area (ha)', data:cumulValues, borderColor:'#e65100', backgroundColor:'rgba(230,81,0,0.08)', tension:0.4, fill:true, pointRadius:2 }] },
    options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0, font:{ size:11 } } }, x:{ ticks:{ font:{ size:9 }, maxRotation:40 } } } }
});
</script>
@endsection
