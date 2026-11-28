@php $active='overview'; $pageTitle='GIS Overview'; $pageSubtitle='Live land survey records and parcel status across Pangasinan.' @endphp
@extends('monitoring._layout')
@section('content')

{{-- ── Stat Cards ── --}}
<div class="stat-row">
    <div class="stat-card blue">
        <div class="sc-label">Total Surveys</div>
        <div class="sc-val">{{ $total }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">LSN records on file</div>
    </div>
    <div class="stat-card green">
        <div class="sc-label">Validated</div>
        <div class="sc-val" style="color:#2e7d32">{{ $validated }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">≥2 attachments</div>
    </div>
    <div class="stat-card amber">
        <div class="sc-label">For Review</div>
        <div class="sc-val" style="color:#f59e0b">{{ $forReview }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">1 attachment or recent</div>
    </div>
    <div class="stat-card red">
        <div class="sc-label">Pending</div>
        <div class="sc-val" style="color:#e53935">{{ $pending }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">No attachments yet</div>
    </div>
</div>

{{-- ── Summary strip ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div style="background:white;border-radius:10px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;border-radius:9px;background:#e8f5e9;display:flex;align-items:center;justify-content:center;font-size:17px;color:#2e7d32"><i class="fas fa-ruler-combined"></i></div>
        <div><div style="font-size:11px;color:#aaa">Total Area</div><div style="font-size:18px;font-weight:800;color:#111">{{ number_format($totalAreaHa,2) }} ha</div></div>
    </div>
    <div style="background:white;border-radius:10px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;border-radius:9px;background:#e3eeff;display:flex;align-items:center;justify-content:center;font-size:17px;color:#2979ff"><i class="fas fa-map-marker-alt"></i></div>
        <div><div style="font-size:11px;color:#aaa">Municipalities</div><div style="font-size:18px;font-weight:800;color:#111">{{ $municipalities->count() }}</div></div>
    </div>
    <div style="background:white;border-radius:10px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;border-radius:9px;background:#fff3e0;display:flex;align-items:center;justify-content:center;font-size:17px;color:#e65100"><i class="fas fa-percentage"></i></div>
        <div><div style="font-size:11px;color:#aaa">Validation Rate</div><div style="font-size:18px;font-weight:800;color:#111">{{ $total > 0 ? round($validated/$total*100,1) : 0 }}%</div></div>
    </div>
</div>

{{-- ── Municipality status breakdown ── --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-map-marker-alt" style="color:#e53935;margin-right:7px"></i>Status by Municipality</div>
        <div class="card-hdr-sub">{{ $byMuni->count() }} municipalities with active surveys</div>
    </div>
    @if($byMuni->isEmpty())
    <div style="text-align:center;padding:36px;color:#bbb;font-size:13px"><i class="fas fa-map" style="font-size:24px;display:block;margin-bottom:8px"></i>No surveys yet. Add records in Land Records.</div>
    @else
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#fafafa;border-bottom:1px solid #f0f0f0">
                    <th style="text-align:left;padding:9px 16px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Municipality</th>
                    <th style="text-align:center;padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase">Total</th>
                    <th style="text-align:center;padding:9px 14px;font-size:11px;color:#2e7d32;font-weight:700;text-transform:uppercase">Validated</th>
                    <th style="text-align:center;padding:9px 14px;font-size:11px;color:#f59e0b;font-weight:700;text-transform:uppercase">For Review</th>
                    <th style="text-align:center;padding:9px 14px;font-size:11px;color:#e53935;font-weight:700;text-transform:uppercase">Pending</th>
                    <th style="padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;min-width:140px">Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byMuni as $row)
                @php $pct = $row['total'] > 0 ? round($row['validated']/$row['total']*100) : 0; @endphp
                <tr style="border-bottom:1px solid #f7f7f7">
                    <td style="padding:11px 16px;font-weight:700;color:#1a3d1a">{{ $row['municipality'] }}</td>
                    <td style="text-align:center;padding:11px 14px;font-weight:700">{{ $row['total'] }}</td>
                    <td style="text-align:center;padding:11px 14px;font-weight:700;color:#2e7d32">{{ $row['validated'] }}</td>
                    <td style="text-align:center;padding:11px 14px;font-weight:700;color:#f59e0b">{{ $row['for_review'] }}</td>
                    <td style="text-align:center;padding:11px 14px;font-weight:700;color:#e53935">{{ $row['pending'] }}</td>
                    <td style="padding:11px 14px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden">
                                <div style="height:100%;background:#2e7d32;border-radius:4px;width:{{ $pct }}%"></div>
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

{{-- ── Recent activity ── --}}
<div class="card">
    <div class="card-hdr">
        <div class="card-hdr-title"><i class="fas fa-history" style="color:#1a3d1a;margin-right:7px"></i>Recently Updated Surveys</div>
        <a href="{{ route('monitoring.activity') }}" style="font-size:12px;color:#1a3d1a;text-decoration:none;font-weight:600">View All →</a>
    </div>
    @if($recentActivity->isEmpty())
    <div style="text-align:center;padding:36px;color:#bbb;font-size:13px">No surveys yet.</div>
    @else
    @foreach($recentActivity as $s)
    <div style="display:flex;align-items:center;gap:14px;padding:12px 18px;border-bottom:1px solid #f7f7f7">
        <div style="width:38px;height:38px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;
            background:{{ $s->status==='validated'?'#e8f5e9':($s->status==='for_review'?'#fff8e1':'#ffebee') }};
            color:{{ $s->status==='validated'?'#2e7d32':($s->status==='for_review'?'#f59e0b':'#e53935') }}">
            <i class="fas {{ $s->status==='validated'?'fa-check-circle':($s->status==='for_review'?'fa-clock':'fa-exclamation-circle') }}"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:#1a3d1a">{{ $s->lsn }}</div>
            <div style="font-size:11.5px;color:#888">{{ $s->municipality }} · {{ $s->lot_count }} lot{{ $s->lot_count!=1?'s':'' }} · {{ number_format($s->area_ha,2) }} ha</div>
            <div style="font-size:11px;color:#aaa;margin-top:1px">Owner: {{ $s->original_owner }}</div>
        </div>
        <div style="text-align:right;flex-shrink:0">
            <span style="display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;
                background:{{ $s->status==='validated'?'#e8f5e9':($s->status==='for_review'?'#fff8e1':'#ffebee') }};
                color:{{ $s->status==='validated'?'#2e7d32':($s->status==='for_review'?'#e65100':'#c62828') }}">
                {{ ucfirst(str_replace('_',' ',$s->status)) }}
            </span>
            <div style="font-size:11px;color:#aaa;margin-top:3px">{{ $s->updated_at->format('M j, Y') }}</div>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endsection
