@php $active='reports'; $pageTitle='GIS Reports'; $pageSubtitle='Geospatial distribution, MPI tracking and monitoring summaries.' @endphp
@extends('monitoring._layout')
@section('content')

{{-- ── Stat cards ── --}}
<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Parcels Tracked</div><div class="sc-val">{{ $total }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validated }}</div></div>
    <div class="stat-card amber"><div class="sc-label">For Review</div><div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $pending }}</div></div>
</div>

{{-- ── MPI Table ── --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-tachometer-alt" style="color:#1a3d1a;margin-right:7px"></i>GIS Measurable Performance Indicators</div>
        <div class="card-hdr-sub">Land survey validation rate against GIS staff targets</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f0f7f0;border-bottom:2px solid #c8e6c9">
                    <th style="text-align:left;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase;letter-spacing:0.4px">Indicator</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">GIS Staff</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Target</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Validated</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">For Review</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Pending</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Total</th>
                    <th style="min-width:150px;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Validation Rate</th>
                    <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $rc = $mpiRate >= 75 ? 'good' : ($mpiRate >= 50 ? 'mid' : 'low'); @endphp
                <tr>
                    <td style="padding:11px 14px;font-weight:700;color:#1a3d1a">Land Survey Validation</td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700">{{ $gisStaff }}</td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700;color:#1565c0;font-size:15px">{{ $mpiTarget }}</td>
                    <td style="padding:11px 14px;text-align:center;font-weight:800;color:#2e7d32;font-size:15px">{{ $validated }}</td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700;color:#f59e0b">{{ $forReview }}</td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700;color:#e53935">{{ $pending }}</td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700">{{ $total }}</td>
                    <td style="padding:11px 14px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:8px;background:#e8f5e9;border-radius:4px;overflow:hidden">
                                <div style="height:100%;border-radius:4px;background:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#f59e0b':'#e53935') }};width:{{ min($mpiRate,100) }}%"></div>
                            </div>
                            <span style="font-size:11.5px;font-weight:700;color:#333;width:40px;text-align:right">{{ $mpiRate }}%</span>
                        </div>
                    </td>
                    <td style="padding:11px 14px;text-align:center">
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:{{ $rc==='good'?'#e8f5e9':($rc==='mid'?'#fff8e1':'#ffebee') }};color:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#e65100':'#c62828') }}">
                            {{ $rc==='good' ? 'On Track' : ($rc==='mid' ? 'Monitor' : 'Below Target') }}
                        </span>
                    </td>
                </tr>
                {{-- Office total row --}}
                <tr style="background:#f0f7f0;font-weight:800">
                    <td style="padding:11px 14px"><strong>GIS Office Total</strong></td>
                    <td style="padding:11px 14px;text-align:center">{{ $gisStaff }}</td>
                    <td style="padding:11px 14px;text-align:center;color:#1565c0">{{ $mpiTarget }}</td>
                    <td style="padding:11px 14px;text-align:center;color:#2e7d32;font-size:15px">{{ $validated }}</td>
                    <td style="padding:11px 14px;text-align:center;color:#f59e0b">{{ $forReview }}</td>
                    <td style="padding:11px 14px;text-align:center;color:#e53935">{{ $pending }}</td>
                    <td style="padding:11px 14px;text-align:center">{{ $total }}</td>
                    <td style="padding:11px 14px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:8px;background:#e8f5e9;border-radius:4px;overflow:hidden">
                                <div style="height:100%;border-radius:4px;background:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#f59e0b':'#e53935') }};width:{{ min($mpiRate,100) }}%"></div>
                            </div>
                            <span style="font-size:11.5px;font-weight:800;color:#333;width:40px;text-align:right">{{ $mpiRate }}%</span>
                        </div>
                    </td>
                    <td style="padding:11px 14px;text-align:center">
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:{{ $rc==='good'?'#e8f5e9':($rc==='mid'?'#fff8e1':'#ffebee') }};color:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#e65100':'#c62828') }}">
                            {{ $rc==='good' ? 'On Track' : ($rc==='mid' ? 'Monitor' : 'Below Target') }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="two-col">
    {{-- Parcel Status Breakdown --}}
    <div class="card">
        <div class="card-hdr">
            <div class="card-hdr-title"><i class="fas fa-chart-bar" style="color:#1a3d1a;margin-right:7px"></i>Parcel Status Breakdown</div>
            <div class="card-hdr-sub">Distribution breakdown of GIS records</div>
        </div>
        <div class="card-body">
            @php $t = max($total,1); @endphp
            <div class="prog-row"><div class="prog-label">Validated</div><div class="prog-track"><div class="prog-fill green" style="width:{{ round(($validated/$t)*100) }}%"></div></div><div class="prog-pct">{{ round(($validated/$t)*100) }}%</div></div>
            <div class="prog-row"><div class="prog-label">For Review</div><div class="prog-track"><div class="prog-fill amber" style="width:{{ round(($forReview/$t)*100) }}%"></div></div><div class="prog-pct">{{ round(($forReview/$t)*100) }}%</div></div>
            <div class="prog-row"><div class="prog-label">Pending</div><div class="prog-track"><div class="prog-fill red" style="width:{{ round(($pending/$t)*100) }}%"></div></div><div class="prog-pct">{{ round(($pending/$t)*100) }}%</div></div>
        </div>
    </div>

    {{-- Monitoring by Municipality --}}
    <div class="card">
        <div class="card-hdr">
            <div class="card-hdr-title"><i class="fas fa-map-marker-alt" style="color:#e53935;margin-right:7px"></i>Monitoring by Municipality</div>
            <div class="card-hdr-sub">Record count by municipality</div>
        </div>
        <div class="card-body">
            @php $maxMuni = $byMuni->max('count') ?: 1; @endphp
            @forelse($byMuni as $m)
            <div class="prog-row" style="margin-bottom:6px">
                <div class="prog-label" style="font-size:11px;width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $m['municipality'] }}">{{ Str::limit($m['municipality'],10) }}</div>
                <div class="prog-track" style="height:22px;border-radius:4px">
                    <div class="prog-fill green" style="width:{{ round(($m['count']/$maxMuni)*100) }}%;height:100%;display:flex;align-items:center;padding-left:6px">
                        <span style="font-size:10px;color:white;font-weight:700">{{ $m['count'] }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#bbb;padding:16px;font-size:12px">No municipality data yet.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Monthly Summary ── --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-calendar-alt" style="color:#1a3d1a;margin-right:7px"></i>Monthly Accomplishment Summary</div>
        <div class="card-hdr-sub">Land surveys and lots recorded per month — last 12 months</div>
    </div>
    <div style="overflow-x:auto">
        <table class="mon-table" style="width:100%">
            <thead>
                <tr><th>Period</th><th style="text-align:center">Surveys Added</th><th style="text-align:center">Lots Added</th><th style="min-width:140px">Activity</th></tr>
            </thead>
            <tbody>
                @forelse($monthly as $m)
                @php $maxSurv = $monthly->max('surveys_added') ?: 1; @endphp
                <tr>
                    <td style="font-weight:600">{{ $m->period }}</td>
                    <td style="text-align:center;font-weight:800;color:#2e7d32;font-size:15px">{{ $m->surveys_added }}</td>
                    <td style="text-align:center;font-weight:700;color:#1565c0">{{ $m->lots_added }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:7px;background:#e8f5e9;border-radius:4px;overflow:hidden">
                                <div style="height:100%;background:#2e7d32;border-radius:4px;width:{{ round(($m->surveys_added/$maxSurv)*100) }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="4">No monthly data yet. Add land surveys to see activity here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Annual Summary ── --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-chart-bar" style="color:#1a3d1a;margin-right:7px"></i>Annual Summary</div>
        <div class="card-hdr-sub">Year-over-year land survey and lot tracking</div>
    </div>
    <div style="overflow-x:auto">
        <table class="mon-table" style="width:100%">
            <thead>
                <tr><th>Year</th><th style="text-align:center">Surveys Recorded</th><th style="text-align:center">Total Lots</th><th>Coverage</th></tr>
            </thead>
            <tbody>
                @forelse($annualYears as $ay)
                @php $maxY = $annualYears->max('surveys') ?: 1; @endphp
                <tr>
                    <td style="font-weight:700;color:#1a3d1a">{{ $ay->yr }}</td>
                    <td style="text-align:center;font-weight:800;color:#2e7d32;font-size:15px">{{ $ay->surveys }}</td>
                    <td style="text-align:center;font-weight:700;color:#1565c0">{{ $ay->lots }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:8px;background:#e8f5e9;border-radius:4px;overflow:hidden">
                                <div style="height:100%;background:#1a3d1a;border-radius:4px;width:{{ round(($ay->surveys/$maxY)*100) }}%"></div>
                            </div>
                            <span style="font-size:11px;color:#555;width:40px;text-align:right">{{ $ay->surveys }}</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="4">No annual data yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Full parcel table ── --}}
<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-table" style="color:#1a3d1a;margin-right:7px"></i>GIS Report Summary</div>
        <div class="card-hdr-sub">Read-only status by parcel reference</div>
    </div>
    <table class="mon-table" style="width:100%">
        <thead><tr><th>Parcel</th><th>Municipality</th><th>Area</th><th>Status</th><th>Last Updated</th></tr></thead>
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
