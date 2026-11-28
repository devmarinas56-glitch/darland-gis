@php $active='reports'; $pageTitle='GIS Reports'; $pageSubtitle='Monthly and annual summaries, MPI tracking, and municipal performance.' @endphp
@extends('monitoring._layout')
@section('content')

<div class="stat-row">
    <div class="stat-card blue"><div class="sc-label">Total Surveys</div><div class="sc-val">{{ $total }}</div></div>
    <div class="stat-card green"><div class="sc-label">Validated</div><div class="sc-val" style="color:#2e7d32">{{ $validated }}</div></div>
    <div class="stat-card amber"><div class="sc-label">For Review</div><div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div></div>
    <div class="stat-card red"><div class="sc-label">Pending</div><div class="sc-val" style="color:#e53935">{{ $pending }}</div></div>
</div>

{{-- ── Summary strip ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div style="background:white;border-radius:10px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.06)">
        <div style="font-size:11px;color:#aaa;margin-bottom:4px">Total Area Covered</div>
        <div style="font-size:22px;font-weight:800;color:#111">{{ number_format($totalAreaHa,2) }} ha</div>
    </div>
    <div style="background:white;border-radius:10px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.06)">
        <div style="font-size:11px;color:#aaa;margin-bottom:4px">Total Lots / Plots</div>
        <div style="font-size:22px;font-weight:800;color:#111">{{ number_format($totalLots) }}</div>
    </div>
    <div style="background:white;border-radius:10px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.06)">
        <div style="font-size:11px;color:#aaa;margin-bottom:4px">Validation Rate</div>
        @php $rc = $mpiRate >= 75 ? 'good' : ($mpiRate >= 50 ? 'mid' : 'low'); @endphp
        <div style="font-size:22px;font-weight:800;color:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#e65100':'#c62828') }}">{{ $mpiRate }}%</div>
    </div>
</div>

{{-- ── MPI Table ── --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-tachometer-alt" style="color:#1a3d1a;margin-right:7px"></i>GIS Measurable Performance Indicators (MPI)</div>
        <div class="card-hdr-sub">Land survey validation performance</div>
    </div>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:#f0f7f0;border-bottom:2px solid #c8e6c9">
                <th style="text-align:left;padding:10px 16px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Indicator</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Target</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Validated</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">For Review</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Pending</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Total</th>
                <th style="min-width:160px;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Rate</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;color:#1a3d1a;font-weight:700;text-transform:uppercase">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom:1px solid #f0f0f0">
                <td style="padding:12px 16px;font-weight:700;color:#1a3d1a">Land Survey Validation</td>
                <td style="padding:12px 14px;text-align:center;font-weight:700;color:#1565c0;font-size:15px">{{ $mpiTarget }}</td>
                <td style="padding:12px 14px;text-align:center;font-weight:800;color:#2e7d32;font-size:15px">{{ $validated }}</td>
                <td style="padding:12px 14px;text-align:center;font-weight:700;color:#f59e0b">{{ $forReview }}</td>
                <td style="padding:12px 14px;text-align:center;font-weight:700;color:#e53935">{{ $pending }}</td>
                <td style="padding:12px 14px;text-align:center;font-weight:700">{{ $total }}</td>
                <td style="padding:12px 14px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:10px;background:#e8f5e9;border-radius:5px;overflow:hidden">
                            <div style="height:100%;border-radius:5px;background:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#f59e0b':'#e53935') }};width:{{ min($mpiRate,100) }}%"></div>
                        </div>
                        <span style="font-size:12px;font-weight:700;width:40px">{{ $mpiRate }}%</span>
                    </div>
                </td>
                <td style="padding:12px 14px;text-align:center">
                    <span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:11.5px;font-weight:700;
                        background:{{ $rc==='good'?'#e8f5e9':($rc==='mid'?'#fff8e1':'#ffebee') }};
                        color:{{ $rc==='good'?'#2e7d32':($rc==='mid'?'#e65100':'#c62828') }}">
                        {{ $rc==='good'?'On Track':($rc==='mid'?'Monitor':'Below Target') }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

{{-- ── Municipality performance ── --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-map-marker-alt" style="color:#e53935;margin-right:7px"></i>Municipal Survey Performance</div>
        <div class="card-hdr-sub">{{ $byMuni->count() }} municipalities</div>
    </div>
    @if($byMuni->isEmpty())
    <div style="text-align:center;padding:36px;color:#bbb;font-size:13px">No data yet.</div>
    @else
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:#fafafa;border-bottom:1px solid #f0f0f0">
                <th style="text-align:left;padding:9px 16px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Municipality</th>
                <th style="text-align:center;padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Surveys</th>
                <th style="text-align:center;padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Lots</th>
                <th style="text-align:right;padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Area (ha)</th>
                <th style="text-align:center;padding:9px 14px;font-size:11px;color:#2e7d32;font-weight:700;text-transform:uppercase">Validated</th>
                <th style="text-align:center;padding:9px 14px;font-size:11px;color:#f59e0b;font-weight:700;text-transform:uppercase">For Review</th>
                <th style="text-align:center;padding:9px 14px;font-size:11px;color:#e53935;font-weight:700;text-transform:uppercase">Pending</th>
                <th style="padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;min-width:120px">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byMuni as $row)
            @php $pct = $row['surveys'] > 0 ? round($row['validated']/$row['surveys']*100) : 0; @endphp
            <tr style="border-bottom:1px solid #f7f7f7">
                <td style="padding:11px 16px;font-weight:700;color:#1a3d1a">{{ $row['municipality'] }}</td>
                <td style="text-align:center;padding:11px 14px;font-weight:700">{{ $row['surveys'] }}</td>
                <td style="text-align:center;padding:11px 14px;color:#555">{{ number_format($row['lots']) }}</td>
                <td style="text-align:right;padding:11px 14px;color:#555">{{ number_format($row['area_ha'],2) }}</td>
                <td style="text-align:center;padding:11px 14px;font-weight:700;color:#2e7d32">{{ $row['validated'] }}</td>
                <td style="text-align:center;padding:11px 14px;font-weight:700;color:#f59e0b">{{ $row['for_review'] }}</td>
                <td style="text-align:center;padding:11px 14px;font-weight:700;color:#e53935">{{ $row['pending'] }}</td>
                <td style="padding:11px 14px">
                    <div style="display:flex;align-items:center;gap:6px">
                        <div style="flex:1;height:7px;background:#f0f0f0;border-radius:3px;overflow:hidden">
                            <div style="height:100%;background:{{ $pct>=75?'#2e7d32':($pct>=50?'#f59e0b':'#e53935') }};border-radius:3px;width:{{ $pct }}%"></div>
                        </div>
                        <span style="font-size:11px;color:#888;width:32px">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

{{-- ── Monthly breakdown ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px">
    <div class="card">
        <div class="card-hdr">
            <div class="card-hdr-title"><i class="fas fa-calendar-alt" style="color:#1a3d1a;margin-right:7px"></i>Monthly Breakdown</div>
            <div class="card-hdr-sub">Last 12 months</div>
        </div>
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#fafafa;border-bottom:1px solid #f0f0f0">
                    <th style="text-align:left;padding:8px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Month</th>
                    <th style="text-align:center;padding:8px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Surveys</th>
                    <th style="text-align:center;padding:8px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Lots</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthly as $row)
                <tr style="border-bottom:1px solid #f7f7f7">
                    <td style="padding:9px 14px;font-weight:600;color:#333;font-size:12.5px">{{ $row->period }}</td>
                    <td style="text-align:center;padding:9px 14px;font-weight:700;color:#1a3d1a">{{ $row->surveys_added }}</td>
                    <td style="text-align:center;padding:9px 14px;color:#555">{{ $row->lots_added ?? 0 }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;padding:28px;color:#bbb;font-size:13px">No data yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="card">
        <div class="card-hdr">
            <div class="card-hdr-title"><i class="fas fa-chart-bar" style="color:#1a3d1a;margin-right:7px"></i>Annual Summary</div>
        </div>
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#fafafa;border-bottom:1px solid #f0f0f0">
                    <th style="text-align:left;padding:8px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Year</th>
                    <th style="text-align:center;padding:8px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Surveys</th>
                    <th style="text-align:center;padding:8px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Lots</th>
                </tr>
            </thead>
            <tbody>
                @forelse($annualYears as $row)
                <tr style="border-bottom:1px solid #f7f7f7">
                    <td style="padding:9px 14px;font-weight:700;color:#1a3d1a;font-size:13px">{{ $row->yr }}</td>
                    <td style="text-align:center;padding:9px 14px;font-weight:700">{{ $row->surveys }}</td>
                    <td style="text-align:center;padding:9px 14px;color:#555">{{ $row->lots ?? 0 }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;padding:28px;color:#bbb;font-size:13px">No data yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
