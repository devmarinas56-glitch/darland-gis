{{-- GIS Monitoring shared layout --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'GIS Monitoring' }} — DarLand GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;font-family:'Segoe UI',Tahoma,sans-serif;background:#f4f5f7}
        body{display:flex}

        /* ── Sidebar ── */
        .sidebar{width:200px;background:#1a3d1a;display:flex;flex-direction:column;position:fixed;height:100vh;left:0;top:0;z-index:100}
        .sb-brand{padding:16px 14px 12px;border-bottom:1px solid rgba(255,255,255,0.1)}
        .sb-brand-row{display:flex;align-items:center;gap:9px;margin-bottom:4px}
        .sb-brand-row img{width:36px;height:36px;object-fit:contain}
        .sb-brand-name{font-size:13px;font-weight:800;color:white;line-height:1.1}
        .sb-brand-sub{font-size:9px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.5px}
        .sb-workspace-label{font-size:9px;font-weight:700;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:0.8px;padding:12px 14px 2px}
        .sb-section-label{font-size:9px;font-weight:700;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:0.8px;padding:14px 14px 4px}

        /* System switcher */
        .sys-chips{display:flex;flex-direction:column;gap:4px;padding:6px 10px 10px}
        .sys-chip{display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;text-decoration:none;font-size:11.5px;font-weight:600;transition:all 0.15s;cursor:pointer}
        .sys-chip.active{background:rgba(255,255,255,0.18);color:white}
        .sys-chip.inactive{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5)}
        .sys-chip.inactive:hover{background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.8)}
        .sys-chip i{font-size:12px;width:14px}
        .sys-chip-sub{font-size:9.5px;color:rgba(255,255,255,0.4);font-weight:400}

        .nav-menu{flex:1;padding:4px 0;overflow-y:auto}
        .nav-item{display:flex;align-items:center;gap:10px;padding:9px 14px;color:rgba(255,255,255,0.6);text-decoration:none;font-size:12.5px;font-weight:500;transition:all 0.15s;border-left:2px solid transparent}
        .nav-item i{font-size:13px;width:15px}
        .nav-item:hover{background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.9)}
        .nav-item.active{background:rgba(255,255,255,0.12);color:white;font-weight:600;border-left-color:rgba(255,255,255,0.6)}

        .sb-footer{padding:12px 14px;border-top:1px solid rgba(255,255,255,0.1)}
        .view-only-label{font-size:9.5px;font-weight:700;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px}
        .view-only-desc{font-size:10px;color:rgba(255,255,255,0.3);line-height:1.4;margin-bottom:10px}
        .logout-btn{width:100%;padding:8px 12px;background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.65);border:none;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:7px;transition:all 0.15s}
        .logout-btn:hover{background:rgba(255,255,255,0.15);color:white}

        /* ── Main ── */
        .main{flex:1;margin-left:200px;display:flex;flex-direction:column;min-height:100vh}

        /* ── Top bar ── */
        .top-bar{background:white;padding:14px 32px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #eee;flex-shrink:0}
        .tb-breadcrumb{font-size:10.5px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px}
        .tb-title{font-size:20px;font-weight:800;color:#111}
        .tb-sub{font-size:12.5px;color:#aaa;margin-top:1px}
        .tb-right{display:flex;align-items:center;gap:10px}
        .tb-user-name{font-size:13px;font-weight:700;color:#333;text-align:right}
        .tb-user-role{font-size:11px;color:#aaa;text-align:right}
        .tb-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;background:#e8f5e9;color:#1a3d1a}

        /* ── Content ── */
        .content{padding:28px 32px 40px;flex:1;overflow-y:auto}

        /* ── Stat cards ── */
        .stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
        .stat-card{background:white;border-radius:10px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);border-left:3px solid transparent}
        .stat-card.blue{border-left-color:#1565c0}
        .stat-card.green{border-left-color:#2e7d32}
        .stat-card.amber{border-left-color:#f59e0b}
        .stat-card.red{border-left-color:#e53935}
        .sc-label{font-size:10.5px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px}
        .sc-val{font-size:28px;font-weight:800;color:#111;line-height:1}

        /* ── Card ── */
        .card{background:white;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;margin-bottom:20px}
        .card-hdr{padding:16px 20px;border-bottom:1px solid #f0f0f0}
        .card-hdr-title{font-size:14px;font-weight:700;color:#111}
        .card-hdr-sub{font-size:11.5px;color:#2e7d32;margin-top:1px}

        /* ── Table ── */
        .mon-table{width:100%;border-collapse:collapse}
        .mon-table th{text-align:left;padding:9px 14px;font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;background:#fafafa;border-bottom:1px solid #f0f0f0}
        .mon-table td{padding:11px 14px;font-size:12.5px;color:#444;border-bottom:1px solid #f7f7f7;vertical-align:middle}
        .mon-table tr:last-child td{border-bottom:none}
        .mon-table tr:hover td{background:#fafafa}
        .mon-table td a{color:#1a3d1a;font-weight:700;text-decoration:none}
        .mon-table td a:hover{text-decoration:underline}
        .empty-row td{text-align:center;padding:28px;color:#bbb;font-size:13px}

        /* ── Status badges ── */
        .badge{display:inline-block;padding:3px 9px;border-radius:10px;font-size:11px;font-weight:600;white-space:nowrap}
        .badge-validated{background:#e8f5e9;color:#2e7d32}
        .badge-for_review{background:#fff8e1;color:#e65100}
        .badge-pending{background:#ffebee;color:#c62828}

        /* ── Progress bar ── */
        .prog-row{display:flex;align-items:center;gap:12px;margin-bottom:10px}
        .prog-label{font-size:12.5px;color:#444;width:80px;flex-shrink:0}
        .prog-track{flex:1;background:#f0f0f0;border-radius:4px;height:10px;overflow:hidden}
        .prog-fill{height:100%;border-radius:4px}
        .prog-fill.green{background:#1a3d1a}
        .prog-fill.amber{background:#f59e0b}
        .prog-fill.red{background:#e53935}
        .prog-pct{font-size:11.5px;color:#888;width:32px;text-align:right;flex-shrink:0}

        /* ── Filter row ── */
        .filter-row{display:flex;gap:10px;align-items:center;padding:12px 16px;border-bottom:1px solid #f0f0f0;background:#fafafa;flex-wrap:wrap}
        .filter-search{flex:1;min-width:200px;position:relative}
        .filter-search i{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#bbb;font-size:12px}
        .filter-search input{width:100%;padding:7px 10px 7px 30px;border:1px solid #e0e0e0;border-radius:7px;font-size:12.5px;color:#333;outline:none}
        .filter-search input:focus{border-color:#1a3d1a}
        .filter-select{padding:7px 10px;border:1px solid #e0e0e0;border-radius:7px;font-size:12.5px;color:#333;background:white;outline:none}
        .filter-count{font-size:11.5px;color:#888;padding:0 4px}

        /* ── Two-col ── */
        .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}

        /* ── Prototype banner ── */
        .proto-banner{background:#fff8e1;border:1px solid #ffe082;border-radius:0;padding:8px 16px;font-size:11.5px;color:#5d4037;border-left:none;border-right:none}

        /* ── Footnote ── */
        .footnote{font-size:11px;color:#ccc;text-align:center;padding:14px;text-transform:uppercase;letter-spacing:0.5px}

        /* ── Activity item ── */
        .act-item{display:flex;align-items:flex-start;gap:14px;padding:12px 20px;border-bottom:1px solid #f7f7f7}
        .act-item:last-child{border-bottom:none}
        .act-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px}
        .act-dot.validated{background:#2e7d32}
        .act-dot.for_review{background:#f59e0b}
        .act-dot.pending{background:#e53935}
        .act-title{font-size:13px;font-weight:700;color:#1a3d1a;margin-bottom:2px}
        .act-sub{font-size:11.5px;color:#888}

        @media(max-width:1100px){.stat-row{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:800px){.two-col{grid-template-columns:1fr}}
    </style>
    @stack('styles')
</head>
<body>
<aside class="sidebar">
    <div class="sb-brand">
        <div class="sb-brand-row">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
            <div>
                <div class="sb-brand-name">DAR</div>
                <div class="sb-brand-sub">Pangasinan Monitoring</div>
            </div>
        </div>
    </div>

    <div class="sb-workspace-label">Monitoring Workspace</div>

    <!-- System switcher -->
    <div class="sb-section-label">GIS Monitoring</div>
    <div class="sys-chips">
        <a href="https://dar-file-management-system.onrender.com/monitoring" class="sys-chip inactive">
            <i class="fas fa-folder-open"></i>
            <div>
                <div>File Management</div>
                <div class="sys-chip-sub">Files, documents, reports</div>
            </div>
        </a>
        <div class="sys-chip active">
            <i class="fas fa-map-marked-alt"></i>
            <div>
                <div>+ GIS Monitoring</div>
                <div class="sys-chip-sub">Map, land parcels, GIS reports</div>
            </div>
        </div>
    </div>

    <div class="nav-menu">
        <a href="{{ route('monitoring.overview') }}"  class="nav-item {{ ($active??'')==='overview'  ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> GIS Overview</a>
        <a href="{{ route('monitoring.parcels') }}"   class="nav-item {{ ($active??'')==='parcels'   ? 'active' : '' }}"><i class="fas fa-map"></i> Land Parcel Monitoring</a>
        <a href="{{ route('monitoring.activity') }}"  class="nav-item {{ ($active??'')==='activity'  ? 'active' : '' }}"><i class="fas fa-history"></i> GIS Activity History</a>
        <a href="{{ route('monitoring.reports') }}"   class="nav-item {{ ($active??'')==='reports'   ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> GIS Reports</a>
    </div>

    <div class="sb-footer">
        <div class="view-only-label">View-Only Access</div>
        <div class="view-only-desc">For two authorised DAR Heads.<br>No file modifications allowed.</div>
        <a href="{{ route('monitoring.logout') }}" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="main">
    <div class="top-bar">
        <div>
            <div class="tb-breadcrumb">DAR Pangasinan &rsaquo; GIS Monitoring</div>
            <div class="tb-title">{{ $pageTitle ?? 'GIS Monitoring' }}</div>
            @if(isset($pageSubtitle))<div class="tb-sub">{{ $pageSubtitle }}</div>@endif
        </div>
        <div class="tb-right">
            <div>
                <div class="tb-user-name">{{ auth()->user()->name }}</div>
                <div class="tb-user-role">Read-only monitoring</div>
            </div>
            <span class="tb-badge">DAR Heads</span>
        </div>
    </div>

    <div class="content">
        @yield('content')
        <div class="footnote">Illustrative Data &bull; UI Prototype Only</div>
    </div>
</div>
@stack('scripts')
</body>
</html>
