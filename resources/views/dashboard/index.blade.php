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
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; display: flex; }

        /* ── Sidebar ── */
        .sidebar { width: 190px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 100; }
        .logo-section { padding: 18px 16px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-section img { width: 42px; height: 42px; object-fit: contain; }
        .logo-text .dept { font-size: 8px; font-weight: 700; color: #adb5bd; text-transform: uppercase; }
        .logo-text .name { font-size: 7px; color: #6c757d; text-transform: uppercase; }
        .nav-menu { flex: 1; padding: 12px 0; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #adb5bd; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s; }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.12); color: white; font-weight: 600; }
        .logout-section { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.08); color: #adb5bd; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.15s; }
        .logout-btn:hover { background: rgba(255,255,255,0.15); color: white; }

        /* ── Main ── */
        .main-content { flex: 1; margin-left: 190px; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top bar ── */
        .top-bar { background: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .search-box { display: flex; align-items: center; gap: 10px; background: #f5f5f5; border-radius: 20px; padding: 8px 16px; width: 320px; }
        .search-box i { color: #999; font-size: 14px; }
        .search-box input { background: none; border: none; outline: none; font-size: 13px; color: #333; width: 100%; }
        .top-right { display: flex; align-items: center; gap: 14px; }
        .bell-btn { background: none; border: none; cursor: pointer; color: #888; font-size: 18px; }
        .user-chip { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: #1a2744; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
        .u-name { font-size: 13px; font-weight: 600; color: #333; }
        .u-role { font-size: 11px; color: #888; text-transform: uppercase; }

        /* ── Content ── */
        .content-area { padding: 24px 28px 40px; flex: 1; overflow-y: auto; }
        .page-title { font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px; }

        /* ── Stat card ── */
        .stat-card { background: #2979ff; border-radius: 12px; padding: 20px 22px; color: white; display: inline-flex; flex-direction: column; gap: 8px; min-width: 200px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(41,121,255,0.3); }
        .stat-label { font-size: 13px; font-weight: 500; opacity: 0.9; }
        .stat-value { font-size: 42px; font-weight: 800; line-height: 1; }
        .stat-link { font-size: 12px; opacity: 0.85; text-decoration: none; color: white; display: flex; align-items: center; gap: 6px; }
        .stat-link:hover { opacity: 1; }

        /* ── Chart card ── */
        .chart-card { background: white; border-radius: 12px; padding: 22px 26px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); margin-bottom: 24px; }
        .chart-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 18px; }
        .bar-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .bar-label { font-size: 12px; color: #555; width: 130px; flex-shrink: 0; text-align: right; }
        .bar-track { flex: 1; background: #f0f0f0; border-radius: 4px; height: 14px; overflow: hidden; }
        .bar-fill { height: 100%; background: #2e7d32; border-radius: 4px; transition: width 0.6s ease; }
        .bar-count { font-size: 12px; color: #888; width: 30px; flex-shrink: 0; }
        .chart-xlabel { text-align: right; font-size: 11px; color: #aaa; margin-top: 6px; }

        /* ── Table card ── */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); overflow: hidden; }
        .table-search { padding: 14px 18px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px; background: #fafafa; }
        .table-search i { color: #bbb; font-size: 13px; }
        .table-search input { border: none; background: none; outline: none; font-size: 13px; color: #333; width: 100%; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 11px 16px; font-size: 12px; color: #888; font-weight: 600; background: #f9f9f9; border-bottom: 1px solid #f0f0f0; }
        td { padding: 13px 16px; font-size: 13px; color: #444; border-bottom: 1px solid #f7f7f7; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafa; }
        .empty-row td { text-align: center; padding: 32px; color: #bbb; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
            <div class="logo-text">
                <div class="dept">Department of</div>
                <div class="name">Agrarian Reform</div>
            </div>
        </div>
        <nav class="nav-menu">
            <a href="/dashboard"    class="nav-item active"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer"   class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
            <a href="/add-record"   class="nav-item"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
        </nav>
        <div class="logout-section">
            <a href="/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search documents..." id="tableSearch" oninput="filterTable(this.value)">
            </div>
            <div class="top-right">
                <button class="bell-btn"><i class="fas fa-bell"></i></button>
                <a href="/profile" class="user-chip">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div>
                        <div class="u-name">{{ auth()->user()->name }}</div>
                        <div class="u-role">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size:10px;color:#bbb;margin-left:4px"></i>
                </a>
            </div>
        </div>

        <div class="content-area">
            <div class="page-title">Dashboard Overview</div>

            <!-- Stat Card -->
            <div class="stat-card">
                <div class="stat-label">Total Land Records</div>
                <div class="stat-value">{{ number_format($totalLots) }}</div>
                <a href="/land-records" class="stat-link">
                    <i class="fas fa-table"></i> View all records
                </a>
            </div>

            <!-- Bar Chart by Municipality/Group -->
            @if($byMunicipality->isNotEmpty())
            <div class="chart-card">
                <div class="chart-title">Land Records by Municipality</div>
                @php $maxCount = $byMunicipality->max('count'); @endphp
                @foreach($byMunicipality as $row)
                @php $label = trim($row->municipality) ?: 'Unknown'; @endphp
                <div class="bar-row">
                    <div class="bar-label">{{ $label }}</div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ $maxCount > 0 ? round(($row->count / $maxCount) * 100) : 0 }}%"></div>
                    </div>
                    <div class="bar-count">{{ $row->count }}</div>
                </div>
                @endforeach
                <div class="chart-xlabel">Numbers of Records</div>
            </div>
            @endif

            <!-- Recent Surveys Table -->
            <div class="table-card">
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search documents..." oninput="filterTable(this.value)">
                </div>
                <table id="surveyTable">
                    <thead>
                        <tr>
                            <th>LSN</th>
                            <th>Original Owner</th>
                            <th>Lot Count</th>
                            <th>Total Area (sqm)</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($recentSurveys as $survey)
                        <tr data-search="{{ strtolower($survey->lsn . ' ' . $survey->original_owner) }}">
                            <td>{{ $survey->lsn }}</td>
                            <td>{{ $survey->original_owner }}</td>
                            <td>{{ $survey->lot_count }}</td>
                            <td>{{ number_format($survey->total_area, 2) }}</td>
                            <td>{{ $survey->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr class="empty-row"><td colspan="5">No land survey records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filterTable(q) {
            q = q.toLowerCase();
            document.querySelectorAll('#tableBody tr[data-search]').forEach(row => {
                row.style.display = !q || row.dataset.search.includes(q) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
