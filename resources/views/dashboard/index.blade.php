<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e8e8e8;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 200px;
            background: #1a2744;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            z-index: 100;
        }

        .logo-section {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo-section img { width: 50px; height: 50px; object-fit: contain; }

        .nav-menu { flex: 1; padding: 15px 0; }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            gap: 12px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: white; }

        .logout-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn {
            width: 100%;
            padding: 11px;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            transition: all 0.2s;
            text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* ── Main ── */
        .main-content {
            flex: 1;
            margin-left: 200px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top bar ── */
        .top-bar {
            background: white;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .search-box { flex: 1; max-width: 420px; position: relative; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; font-size: 13px; }
        .search-input {
            width: 100%;
            padding: 9px 14px 9px 35px;
            border: 1px solid #e8e8e8;
            border-radius: 20px;
            font-size: 13px;
            background: #f7f7f7;
            color: #444;
        }
        .search-input:focus { outline: none; border-color: #1a2744; background: white; }

        .top-right { display: flex; align-items: center; gap: 18px; }
        .bell-icon { font-size: 18px; color: #888; cursor: pointer; transition: color 0.2s; }
        .bell-icon:hover { color: #1a2744; }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #1a2744;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 13px;
            flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #999; text-transform: uppercase; }
        .chevron { font-size: 11px; color: #bbb; margin-left: 2px; }

        /* ── Content area ── */
        .content-area { padding: 26px 30px 40px; flex: 1; overflow-y: auto; }

        .page-title { font-size: 19px; font-weight: 700; color: #222; margin-bottom: 20px; }

        /* ── Top stat row ── */
        .top-stat-row {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 16px;
            margin-bottom: 16px;
            align-items: stretch;
        }

        /* Main blue stat card */
        .main-stat-card {
            background: #2979ff;
            border-radius: 14px;
            padding: 24px 22px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
        }
        .main-stat-label {
            font-size: 12px;
            font-weight: 600;
            opacity: 0.88;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }
        .main-stat-num {
            font-size: 48px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }
        .main-stat-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 6px 12px;
            border-radius: 20px;
            width: fit-content;
            transition: background 0.2s;
        }
        .main-stat-link:hover { background: rgba(255,255,255,0.25); color: white; }
        .main-stat-icon {
            align-self: flex-end;
            font-size: 38px;
            opacity: 0.22;
            position: absolute;
            right: 18px;
            bottom: 14px;
        }
        .main-stat-card { position: relative; overflow: hidden; }

        /* Secondary stat cards */
        .secondary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .sec-stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .sec-stat-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .sec-stat-icon.green  { background: #e8f5e9; color: #2e7d32; }
        .sec-stat-icon.orange { background: #fff3e0; color: #e65100; }
        .sec-stat-icon.purple { background: #f3e5f5; color: #6a1b9a; }
        .sec-stat-label { font-size: 11px; color: #999; margin-bottom: 4px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; }
        .sec-stat-num { font-size: 26px; font-weight: 700; color: #222; line-height: 1; }
        .sec-stat-sub { font-size: 11px; color: #aaa; margin-top: 3px; }

        /* ── Middle row ── */
        .middle-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        /* Bar chart card */
        .bar-card {
            background: white;
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .card-title {
            font-size: 13px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }

        .muni-row { margin-bottom: 10px; }
        .muni-label {
            font-size: 12px;
            color: #555;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .muni-count { font-size: 11px; color: #aaa; font-weight: 600; }
        .bar-track { background: #f0f0f0; border-radius: 4px; height: 11px; overflow: hidden; }
        .bar-fill { height: 100%; background: #2e7d32; border-radius: 4px; transition: width 0.6s ease; }
        .bar-footer { font-size: 11px; color: #bbb; text-align: right; margin-top: 12px; }

        /* Donut chart card */
        .donut-card {
            background: #d8d8d8;
            border-radius: 14px;
            padding: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 28px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .donut-wrapper {
            position: relative;
            width: 160px; height: 160px;
            flex-shrink: 0;
        }
        .donut-center {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }
        .donut-center .d-num { font-size: 26px; font-weight: 800; color: #222; }
        .donut-center .d-lbl { font-size: 11px; color: #888; }
        .donut-legend { display: flex; flex-direction: column; gap: 10px; }
        .legend-row { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #444; }
        .legend-dot { width: 11px; height: 11px; border-radius: 50%; flex-shrink: 0; }
        .legend-count { margin-left: auto; font-size: 11px; font-weight: 700; color: #888; padding-left: 14px; }

        /* ── Table card ── */
        .table-card {
            background: white;
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .table-header h3 { font-size: 15px; font-weight: 700; color: #222; }

        .table-search { position: relative; }
        .table-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 12px; }
        .table-search input {
            padding: 8px 12px 8px 30px;
            border: 1px solid #e8e8e8;
            border-radius: 20px;
            font-size: 12px;
            width: 200px;
            background: #f7f7f7;
        }
        .table-search input:focus { outline: none; border-color: #1a2744; background: white; }

        .records-table { width: 100%; border-collapse: collapse; }
        .records-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            color: #aaa;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 2px solid #f5f5f5;
        }
        .records-table td {
            padding: 13px 14px;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #f8f8f8;
            vertical-align: middle;
        }
        .records-table tbody tr:last-child td { border-bottom: none; }
        .records-table tbody tr:hover td { background: #fafbff; }

        .lsn-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #1a2744;
            background: #eef1f8;
            padding: 3px 9px;
            border-radius: 6px;
        }

        .lot-count-pill {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .area-text { font-size: 12px; color: #888; }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #bbb;
        }
        .empty-state i { font-size: 40px; margin-bottom: 10px; display: block; }
        .empty-state p { font-size: 13px; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="Land GIS">
        </div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item active"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer" class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
            <a href="/add-record" class="nav-item"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="nav-item"><i class="fas fa-users"></i><span>Users</span></a>
            @endif
        </nav>
        <div class="logout-section">
            <a href="/logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Top Bar -->
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" id="globalSearch" placeholder="Search surveys...">
            </div>
            <div class="top-right">
                <i class="fas fa-bell bell-icon"></i>
                <a href="/profile" class="user-chip" style="text-decoration:none;cursor:pointer">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-label">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
            <h1 class="page-title">Dashboard Overview</h1>

            <!-- Top stat row -->
            <div class="top-stat-row">

                <!-- Main blue card: total lots -->
                <div class="main-stat-card">
                    <div>
                        <div class="main-stat-label">Total Land Records</div>
                        <div class="main-stat-num">{{ number_format($totalLots) }}</div>
                    </div>
                    <a href="/land-records" class="main-stat-link">
                        <i class="fas fa-search"></i> View all records
                    </a>
                    <i class="fas fa-map-marked-alt main-stat-icon"></i>
                </div>

                <!-- Secondary stat cards -->
                <div class="secondary-stats">
                    <div class="sec-stat-card">
                        <div class="sec-stat-icon green"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <div class="sec-stat-label">Total Surveys</div>
                            <div class="sec-stat-num">{{ number_format($totalSurveys) }}</div>
                            <div class="sec-stat-sub">Active LSN records</div>
                        </div>
                    </div>
                    <div class="sec-stat-card">
                        <div class="sec-stat-icon orange"><i class="fas fa-ruler-combined"></i></div>
                        <div>
                            <div class="sec-stat-label">Total Area</div>
                            <div class="sec-stat-num">{{ number_format($totalArea / 10000, 1) }}</div>
                            <div class="sec-stat-sub">Hectares mapped</div>
                        </div>
                    </div>
                    <div class="sec-stat-card">
                        <div class="sec-stat-icon purple"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="sec-stat-label">Users</div>
                            <div class="sec-stat-num">{{ \App\Models\User::count() }}</div>
                            <div class="sec-stat-sub">Active accounts</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle row: bar chart + donut -->
            <div class="middle-row">

                <!-- Municipality bar chart -->
                <div class="bar-card">
                    <div class="card-title">Land Records by Municipality</div>

                    @php
                        $maxCount = $byMunicipality->max() ?: 1;
                        $muniColors = [
                            'Urdaneta City' => '#2e7d32',
                            'Dagupan City'  => '#1565c0',
                            'Binalonan'     => '#e65100',
                            'Villasis'      => '#6a1b9a',
                            'Lingayen'      => '#00838f',
                            'Alaminos'      => '#c62828',
                            'Other'         => '#888',
                        ];
                    @endphp

                    @forelse($byMunicipality->sortByDesc(fn($v) => $v) as $muni => $count)
                    @php $pct = round(($count / $maxCount) * 100); @endphp
                    <div class="muni-row">
                        <div class="muni-label">
                            <span>{{ $muni }}</span>
                            <span class="muni-count">{{ $count }}</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ $pct }}%; background:{{ $muniColors[$muni] ?? '#2e7d32' }}"></div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;padding:30px;color:#bbb;font-size:13px">
                        <i class="fas fa-map-marker-alt" style="font-size:28px;margin-bottom:8px;display:block"></i>
                        No survey data yet
                    </div>
                    @endforelse

                    <div class="bar-footer">Number of Lots</div>
                </div>

                <!-- Donut chart: lot distribution by survey -->
                <div class="donut-card">
                    @php
                        // Show top-5 surveys by lot count for the donut
                        $top5 = $surveys->sortByDesc('lot_count')->take(5);
                        $donutColors = ['#2979ff','#2e7d32','#ff6d00','#6a1b9a','#00838f'];
                        $donutData  = $top5->pluck('lot_count')->values()->toJson();
                        $donutLabels = $top5->pluck('lsn')->values()->toJson();
                        $donutBg    = collect($donutColors)->take($top5->count())->values()->toJson();
                    @endphp
                    <div class="donut-wrapper">
                        <canvas id="donutChart"></canvas>
                        <div class="donut-center">
                            <div class="d-num">{{ number_format($totalLots) }}</div>
                            <div class="d-lbl">Total Lots</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        @if($top5->isEmpty())
                            <div style="color:#aaa;font-size:12px;text-align:center">No data yet</div>
                        @else
                            @foreach($top5 as $i => $s)
                            <div class="legend-row">
                                <div class="legend-dot" style="background:{{ $donutColors[$i] ?? '#ccc' }}"></div>
                                <span>{{ $s->lsn }}</span>
                                <span class="legend-count">{{ $s->lot_count }}</span>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Land Survey Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Recent Land Surveys</h3>
                    <div class="table-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="tableSearch" placeholder="Search documents..." oninput="filterTable(this.value)">
                    </div>
                </div>

                @if($surveys->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No surveys recorded yet.</p>
                </div>
                @else
                <table class="records-table" id="recordsTable">
                    <thead>
                        <tr>
                            <th>LSN</th>
                            <th>Original Owner</th>
                            <th>Lots</th>
                            <th>Area (sqm)</th>
                            <th>Area (ha)</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveys->sortByDesc('created_at') as $survey)
                        <tr data-search="{{ strtolower($survey->lsn . ' ' . $survey->original_owner) }}">
                            <td><span class="lsn-badge"><i class="fas fa-tag"></i>{{ $survey->lsn }}</span></td>
                            <td>{{ $survey->original_owner }}</td>
                            <td><span class="lot-count-pill">{{ $survey->lot_count }} lots</span></td>
                            <td>{{ number_format($survey->total_area, 0) }}</td>
                            <td class="area-text">{{ number_format($survey->total_area / 10000, 4) }} ha</td>
                            <td style="color:#aaa;font-size:12px">{{ $survey->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Donut chart ──────────────────────────────────
        const donutData   = {!! $donutData ?? '[]' !!};
        const donutLabels = {!! $donutLabels ?? '[]' !!};
        const donutBg     = {!! $donutBg ?? '[]' !!};

        if (donutData.length > 0) {
            const ctx = document.getElementById('donutChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutData,
                        backgroundColor: donutBg,
                        borderWidth: 0,
                        hoverOffset: 5,
                    }]
                },
                options: {
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} lots`
                            }
                        }
                    },
                    animation: { animateScale: true }
                }
            });
        }

        // ── Table search ─────────────────────────────────
        function filterTable(q) {
            const rows = document.querySelectorAll('#recordsTable tbody tr');
            const term = q.toLowerCase();
            rows.forEach(r => {
                r.style.display = r.dataset.search.includes(term) ? '' : 'none';
            });
        }

        // Also wire up the top search bar to the table
        document.getElementById('globalSearch').addEventListener('input', function() {
            document.getElementById('tableSearch').value = this.value;
            filterTable(this.value);
            // Scroll to table
            if (this.value.length > 0) {
                document.querySelector('.table-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
</body>
</html>
