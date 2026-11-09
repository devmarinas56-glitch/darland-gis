<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DarLand GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #e8e8e8; display: flex; }

        /* ── Sidebar ── */
        .sidebar { width: 190px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 100; }
        .logo-section { padding: 18px 16px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-section img { width: 42px; height: 42px; object-fit: contain; }
        .logo-text .dept { font-size: 8px; font-weight: 700; color: rgba(255,255,255,0.7); text-transform: uppercase; }
        .logo-text .name { font-size: 7px; color: rgba(255,255,255,0.45); text-transform: uppercase; }
        .nav-menu { flex: 1; padding: 12px 0; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: rgba(255,255,255,0.65); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s; }
        .nav-item i { font-size: 15px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.14); color: white; font-weight: 600; }
        .logout-section { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.65); border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.15s; }
        .logout-btn:hover { background: rgba(255,255,255,0.15); color: white; }

        /* ── Main ── */
        .main-content { flex: 1; margin-left: 190px; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top bar ── */
        .top-bar { background: #e8e8e8; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .page-heading { font-size: 18px; font-weight: 700; color: #1a1a1a; }
        .top-right { display: flex; align-items: center; gap: 14px; }
        .user-chip { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: #1a2744; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; overflow: hidden; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .u-name { font-size: 13px; font-weight: 600; color: #333; }
        .u-role { font-size: 11px; color: #888; text-transform: uppercase; }

        /* ── Content ── */
        .content-area { padding: 20px 28px 40px; flex: 1; overflow-y: auto; }

        /* ── Stat cards grid ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
        .stat-card { background: white; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); display: flex; flex-direction: column; gap: 10px; }
        .stat-icon-row { display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .stat-icon.blue   { background: #e3eeff; color: #2979ff; }
        .stat-icon.green  { background: #e8f5e9; color: #2e7d32; }
        .stat-icon.amber  { background: #fff8e1; color: #f59e0b; }
        .stat-icon.red    { background: #ffebee; color: #e53935; }
        .stat-trend { font-size: 11px; color: #2e7d32; background: #e8f5e9; border-radius: 20px; padding: 3px 8px; font-weight: 600; }
        .stat-val { font-size: 28px; font-weight: 800; color: #111; line-height: 1; }
        .stat-lbl { font-size: 12px; color: #888; font-weight: 500; }
        .stat-sub { font-size: 11px; color: #aaa; }

        /* ── Section header ── */
        .section-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; margin-top: 8px; }
        .section-title { font-size: 14px; font-weight: 700; color: #1a1a1a; }
        .section-link { font-size: 12px; color: #1a2744; text-decoration: none; font-weight: 600; }
        .section-link:hover { text-decoration: underline; }

        /* ── Two-col layout ── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px; }
        .three-col { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 22px; }

        /* ── Card ── */
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); overflow: hidden; }
        .card-header { padding: 14px 18px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 13px; font-weight: 700; color: #1a1a1a; }
        .card-badge { background: #e8edf5; color: #1a2744; border-radius: 10px; padding: 2px 9px; font-size: 11px; font-weight: 700; }
        .card-body { padding: 14px 18px; }

        /* ── Bar chart ── */
        .bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .bar-label { font-size: 12px; color: #555; width: 120px; flex-shrink: 0; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .bar-track { flex: 1; background: #f0f0f0; border-radius: 4px; height: 12px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 4px; transition: width 0.7s ease; }
        .bar-fill.green { background: linear-gradient(90deg, #2e7d32, #4caf50); }
        .bar-fill.blue  { background: linear-gradient(90deg, #1a2744, #2979ff); }
        .bar-count { font-size: 11px; color: #888; width: 28px; flex-shrink: 0; text-align: right; }

        /* ── Month sparkline ── */
        .spark-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; }
        .spark-col { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .spark-bar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; height: 60px; }
        .spark-bar { width: 100%; border-radius: 3px 3px 0 0; transition: height 0.6s ease; min-height: 3px; }
        .spark-bar.green { background: linear-gradient(180deg, #4caf50, #2e7d32); }
        .spark-bar.blue  { background: linear-gradient(180deg, #2979ff, #1a2744); }
        .spark-lbl { font-size: 9.5px; color: #aaa; text-align: center; }
        .spark-val { font-size: 10px; font-weight: 700; color: #555; }

        /* ── Owner table ── */
        .owner-table { width: 100%; border-collapse: collapse; }
        .owner-table th { text-align: left; padding: 9px 12px; font-size: 11px; color: #aaa; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; background: #fafafa; border-bottom: 1px solid #f0f0f0; }
        .owner-table td { padding: 10px 12px; font-size: 12.5px; color: #444; border-bottom: 1px solid #f7f7f7; }
        .owner-table tr:last-child td { border-bottom: none; }
        .owner-table tr:hover td { background: #fafafa; }
        .rank-badge { width: 22px; height: 22px; border-radius: 50%; background: #e8edf5; color: #1a2744; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
        .rank-badge.top { background: #1a2744; color: white; }
        .owner-area-bar { display: flex; align-items: center; gap: 8px; }
        .owner-track { flex: 1; background: #f0f0f0; border-radius: 3px; height: 7px; overflow: hidden; }
        .owner-fill { height: 100%; background: linear-gradient(90deg, #2979ff, #64b5f6); border-radius: 3px; }

        /* ── Survey table ── */
        .survey-table { width: 100%; border-collapse: collapse; }
        .survey-table th { text-align: left; padding: 9px 14px; font-size: 11px; color: #aaa; font-weight: 600; text-transform: uppercase; background: #fafafa; border-bottom: 1px solid #f0f0f0; }
        .survey-table td { padding: 11px 14px; font-size: 12.5px; color: #444; border-bottom: 1px solid #f7f7f7; vertical-align: middle; }
        .survey-table tr:last-child td { border-bottom: none; }
        .survey-table tr:hover td { background: #fafafa; }
        .lsn-link { color: #1a2744; text-decoration: none; font-weight: 700; font-size: 13px; }
        .lsn-link:hover { text-decoration: underline; }
        .badge-muni { background: #e8edf5; color: #1a2744; border-radius: 8px; padding: 2px 8px; font-size: 10.5px; font-weight: 600; }
        .lot-chip { background: #f0f0f0; color: #555; border-radius: 8px; padding: 2px 8px; font-size: 11px; font-weight: 700; display: inline-block; }
        .area-text { font-size: 12px; color: #555; }
        .area-ha { font-size: 10.5px; color: #aaa; }

        /* ── Search input ── */
        .search-row { display: flex; align-items: center; gap: 8px; padding: 11px 14px; border-bottom: 1px solid #f0f0f0; background: #fafafa; }
        .search-row i { color: #bbb; font-size: 13px; }
        .search-row input { border: none; background: none; outline: none; font-size: 13px; color: #333; width: 100%; }

        /* ── Empty ── */
        .empty-row td { text-align: center; padding: 32px; color: #bbb; font-size: 13px; }

        /* ── Tabs ── */
        .tab-pills { display: flex; gap: 6px; margin-bottom: 14px; }
        .tab-pill { padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1.5px solid #e0e0e0; background: white; color: #666; transition: all 0.15s; }
        .tab-pill.active { background: #1a2744; color: white; border-color: #1a2744; }

        /* ── Responsive ── */
        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 900px)  { .two-col, .three-col { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
            <div class="logo-text">
                <div class="dept">Department of Agrarian Reform</div>
                <div class="name">DarLand GIS</div>
            </div>
        </div>
        <nav class="nav-menu">
            <a href="/dashboard"    class="nav-item active"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer"   class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-layer-group"></i><span>Land Records</span></a>
        </nav>
        <div class="logout-section">
            <a href="/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-heading">Dashboard</div>
            <div class="top-right">
                <a href="/profile" class="user-chip">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="u-name">{{ auth()->user()->name }}</div>
                        <div class="u-role">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="content-area">

            <!-- ── Stat Cards ── -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-row">
                        <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
                        <span class="stat-trend">Active</span>
                    </div>
                    <div class="stat-val">{{ number_format($totalSurveys) }}</div>
                    <div class="stat-lbl">Total Land Surveys</div>
                    <div class="stat-sub">LSN records on file</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-row">
                        <div class="stat-icon green"><i class="fas fa-draw-polygon"></i></div>
                    </div>
                    <div class="stat-val">{{ number_format($totalLots) }}</div>
                    <div class="stat-lbl">Total Plots / Lots</div>
                    <div class="stat-sub">Across all surveys</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-row">
                        <div class="stat-icon amber"><i class="fas fa-ruler-combined"></i></div>
                    </div>
                    <div class="stat-val">{{ number_format($totalAreaHa, 2) }}</div>
                    <div class="stat-lbl">Total Area (ha)</div>
                    <div class="stat-sub">{{ number_format($totalAreaSqm, 0) }} sqm</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-row">
                        <div class="stat-icon red"><i class="fas fa-paperclip"></i></div>
                    </div>
                    <div class="stat-val">{{ number_format($totalAttachments) }}</div>
                    <div class="stat-lbl">Attachments</div>
                    <div class="stat-sub">Docs &amp; photos uploaded</div>
                </div>
            </div>

            <!-- ── Activity Charts ── -->
            <div class="two-col">
                <!-- Lots added per month -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-chart-bar" style="color:#2e7d32;margin-right:7px"></i>Lots Added Per Month</div>
                        <span class="card-badge">Last 12 mo</span>
                    </div>
                    <div class="card-body">
                        @php
                            $maxLots = $lotsPerMonth->max('lot_count') ?: 1;
                            $lotMonths = $lotsPerMonth->takeLast(6);
                        @endphp
                        @if($lotMonths->isEmpty())
                            <div style="text-align:center;color:#bbb;padding:24px;font-size:13px">No data yet</div>
                        @else
                        <div class="spark-grid" style="grid-template-columns: repeat({{ $lotMonths->count() }}, 1fr)">
                            @foreach($lotMonths as $row)
                            <div class="spark-col">
                                <div class="spark-val">{{ $row->lot_count }}</div>
                                <div class="spark-bar-wrap">
                                    <div class="spark-bar green" style="height:{{ $maxLots > 0 ? round(($row->lot_count / $maxLots) * 100) : 0 }}%"></div>
                                </div>
                                <div class="spark-lbl">{{ Str::limit($row->month_label, 6, '') }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Surveys created per month -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-file-alt" style="color:#1a2744;margin-right:7px"></i>Surveys Created Per Month</div>
                        <span class="card-badge">Last 12 mo</span>
                    </div>
                    <div class="card-body">
                        @php
                            $maxSurv = $surveysByMonth->max('survey_count') ?: 1;
                            $survMonths = $surveysByMonth->takeLast(6);
                        @endphp
                        @if($survMonths->isEmpty())
                            <div style="text-align:center;color:#bbb;padding:24px;font-size:13px">No data yet</div>
                        @else
                        <div class="spark-grid" style="grid-template-columns: repeat({{ $survMonths->count() }}, 1fr)">
                            @foreach($survMonths as $row)
                            <div class="spark-col">
                                <div class="spark-val">{{ $row->survey_count }}</div>
                                <div class="spark-bar-wrap">
                                    <div class="spark-bar blue" style="height:{{ $maxSurv > 0 ? round(($row->survey_count / $maxSurv) * 100) : 0 }}%"></div>
                                </div>
                                <div class="spark-lbl">{{ Str::limit($row->month_label, 6, '') }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ── Municipality Chart ── -->
            @if($byMunicipality->isNotEmpty())
            <div class="card" style="margin-bottom:22px">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-map-marker-alt" style="color:#e53935;margin-right:7px"></i>Surveys by Municipality</div>
                    <span class="card-badge">Top 10</span>
                </div>
                <div class="card-body">
                    @php $maxMuni = $byMunicipality->max('count'); @endphp
                    @foreach($byMunicipality as $row)
                    <div class="bar-row">
                        <div class="bar-label">{{ trim($row->municipality) ?: 'Unknown' }}</div>
                        <div class="bar-track">
                            <div class="bar-fill green" style="width:{{ $maxMuni > 0 ? round(($row->count/$maxMuni)*100) : 0 }}%"></div>
                        </div>
                        <div class="bar-count">{{ $row->count }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- ── Plot Owners + Survey Table ── -->
            <div class="three-col">

                <!-- Plot Owners -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-users" style="color:#1a2744;margin-right:7px"></i>Plot Owners</div>
                        <span class="card-badge">{{ $plotOwners->count() }} owners</span>
                    </div>
                    <div class="search-row">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search owners…" id="ownerSearch" oninput="filterOwners(this.value)">
                    </div>
                    @php $maxOwnerArea = $plotOwners->max('total_area') ?: 1; @endphp
                    <div style="overflow-x:auto;max-height:420px;overflow-y:auto">
                        <table class="owner-table" id="ownerTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Owner</th>
                                    <th>Lots</th>
                                    <th>Area (ha)</th>
                                </tr>
                            </thead>
                            <tbody id="ownerTableBody">
                                @forelse($plotOwners as $i => $owner)
                                <tr data-search="{{ strtolower($owner->owner_name) }}">
                                    <td>
                                        <div class="rank-badge {{ $i < 3 ? 'top' : '' }}">{{ $i+1 }}</div>
                                    </td>
                                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $owner->owner_name }}">
                                        {{ $owner->owner_name }}
                                    </td>
                                    <td>
                                        <span class="lot-chip">{{ $owner->lot_count }}</span>
                                    </td>
                                    <td>
                                        <div class="owner-area-bar">
                                            <div class="owner-track">
                                                <div class="owner-fill" style="width:{{ round(($owner->total_area / $maxOwnerArea) * 100) }}%"></div>
                                            </div>
                                            <span style="font-size:11px;color:#666;white-space:nowrap">{{ number_format($owner->total_area / 10000, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr class="empty-row"><td colspan="4">No plot owners yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Stats sidebar -->
                <div style="display:flex;flex-direction:column;gap:14px">
                    <div class="card">
                        <div class="card-header"><div class="card-title"><i class="fas fa-chart-pie" style="color:#2979ff;margin-right:7px"></i>Summary</div></div>
                        <div class="card-body">
                            @php
                                $avgLotsPerSurvey = $totalSurveys > 0 ? round($totalLots / $totalSurveys, 1) : 0;
                                $avgAreaPerLot    = $totalLots > 0 ? round($totalAreaSqm / $totalLots) : 0;
                                $avgAreaPerSurvey = $totalSurveys > 0 ? round($totalAreaSqm / $totalSurveys) : 0;
                                $uniqueOwners     = $plotOwners->count();
                            @endphp
                            <div style="display:flex;flex-direction:column;gap:10px">
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5">
                                    <span style="font-size:12px;color:#666">Avg lots/survey</span>
                                    <span style="font-size:14px;font-weight:700;color:#1a2744">{{ $avgLotsPerSurvey }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5">
                                    <span style="font-size:12px;color:#666">Avg area/lot</span>
                                    <span style="font-size:14px;font-weight:700;color:#1a2744">{{ number_format($avgAreaPerLot) }} sqm</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5">
                                    <span style="font-size:12px;color:#666">Avg area/survey</span>
                                    <span style="font-size:14px;font-weight:700;color:#1a2744">{{ number_format($avgAreaPerSurvey) }} sqm</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0">
                                    <span style="font-size:12px;color:#666">Unique owners</span>
                                    <span style="font-size:14px;font-weight:700;color:#1a2744">{{ $uniqueOwners }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><div class="card-title"><i class="fas fa-map" style="color:#e53935;margin-right:7px"></i>Quick Links</div></div>
                        <div class="card-body" style="display:flex;flex-direction:column;gap:8px">
                            <a href="/land-records" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f0f4ff;border-radius:8px;text-decoration:none;color:#1a2744;font-size:13px;font-weight:600;transition:background 0.15s" onmouseover="this.style.background='#e3eeff'" onmouseout="this.style.background='#f0f4ff'">
                                <i class="fas fa-layer-group"></i> Land Records
                            </a>
                            <a href="/map-viewer" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f0f4ff;border-radius:8px;text-decoration:none;color:#1a2744;font-size:13px;font-weight:600;transition:background 0.15s" onmouseover="this.style.background='#e3eeff'" onmouseout="this.style.background='#f0f4ff'">
                                <i class="fas fa-map"></i> Map Viewer
                            </a>
                            <a href="/submit-report" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f0f4ff;border-radius:8px;text-decoration:none;color:#1a2744;font-size:13px;font-weight:600;transition:background 0.15s" onmouseover="this.style.background='#e3eeff'" onmouseout="this.style.background='#f0f4ff'">
                                <i class="fas fa-file-alt"></i> Submit Report
                            </a>
                        </div>
                    </div>
                </div>

            </div><!-- /three-col -->

            <!-- ── All Land Survey Records ── -->
            <div class="section-hdr">
                <div class="section-title">All Land Survey Records</div>
                <a href="/land-records" class="section-link">Manage in Land Records →</a>
            </div>
            <div class="card" style="margin-bottom:30px">
                <div class="search-row">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search by LSN, owner, municipality…" id="surveySearch" oninput="filterSurveys(this.value)">
                </div>
                <div style="overflow-x:auto">
                    <table class="survey-table" id="surveyTable">
                        <thead>
                            <tr>
                                <th>LSN</th>
                                <th>Original Owner</th>
                                <th>Municipality</th>
                                <th>Lots</th>
                                <th>Total Area</th>
                                <th>Date Registered</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody id="surveyTableBody">
                            @forelse($allSurveys as $survey)
                            <tr data-search="{{ strtolower($survey->lsn . ' ' . $survey->original_owner . ' ' . $survey->municipality) }}">
                                <td><a href="/land-records" class="lsn-link">{{ $survey->lsn }}</a></td>
                                <td>{{ $survey->original_owner }}</td>
                                <td>
                                    @if($survey->municipality)
                                        <span class="badge-muni">{{ $survey->municipality }}</span>
                                    @else
                                        <span style="color:#ccc;font-size:12px">—</span>
                                    @endif
                                </td>
                                <td><span class="lot-chip">{{ $survey->lot_count }}</span></td>
                                <td>
                                    <div class="area-text">{{ number_format($survey->total_area, 0) }} sqm</div>
                                    <div class="area-ha">{{ number_format($survey->total_area / 10000, 4) }} ha</div>
                                </td>
                                <td>{{ $survey->date_registered ? \Carbon\Carbon::parse($survey->date_registered)->format('M j, Y') : '—' }}</td>
                                <td style="font-size:12px;color:#888">{{ $survey->created_at->format('M j, Y') }}</td>
                            </tr>
                            @empty
                            <tr class="empty-row"><td colspan="7">No land survey records yet. <a href="/land-records" style="color:#1a2744">Add one →</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content-area -->
    </div><!-- /main-content -->

    <script>
        // ── Owner table filter ──────────────────────────────────
        function filterOwners(q) {
            q = q.toLowerCase();
            document.querySelectorAll('#ownerTableBody tr[data-search]').forEach(row => {
                row.style.display = !q || row.dataset.search.includes(q) ? '' : 'none';
            });
        }

        // ── Survey table filter ─────────────────────────────────
        function filterSurveys(q) {
            q = q.toLowerCase();
            document.querySelectorAll('#surveyTableBody tr[data-search]').forEach(row => {
                row.style.display = !q || row.dataset.search.includes(q) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
