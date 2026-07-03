<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e8e8e8;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            background: #1a2744;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .logo-section {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo-section img {
            width: 50px;
            height: 50px;
        }

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
            transition: all 0.3s;
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
            transition: all 0.3s;
        }

        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 200px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .search-box {
            flex: 1;
            max-width: 450px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 14px;
        }

        .search-input {
            width: 100%;
            padding: 9px 15px 9px 36px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 13px;
            background: #f9f9f9;
        }

        .search-input:focus { outline: none; border-color: #1a2744; }

        .top-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .bell-icon { font-size: 18px; color: #666; cursor: pointer; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #1a2744;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
        }

        .user-details { display: flex; flex-direction: column; }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role { font-size: 11px; color: #999; }

        /* Content Area */
        .content-area { padding: 25px 30px; flex: 1; overflow-y: auto; }

        .page-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 20px; }

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            min-height: 100px;
        }

        .stat-card.blue { background: #2196f3; }
        .stat-card.green { background: #4caf50; }
        .stat-card.yellow { background: #ff9800; }
        .stat-card.purple { background: #9c27b0; }

        .stat-info h4 { font-size: 12px; opacity: 0.9; margin-bottom: 8px; font-weight: 500; }
        .stat-info .stat-num { font-size: 36px; font-weight: 700; line-height: 1; }
        .stat-info .stat-sub { font-size: 11px; opacity: 0.8; margin-top: 5px; }

        .stat-icon-right {
            font-size: 36px;
            opacity: 0.6;
            align-self: flex-end;
        }

        /* Middle Row */
        .middle-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: #d0d0d0;
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
        }

        .donut-wrapper {
            position: relative;
            width: 160px;
            height: 160px;
            flex-shrink: 0;
        }

        .donut-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .donut-center .num { font-size: 24px; font-weight: 700; color: #333; }
        .donut-center .label { font-size: 11px; color: #666; }

        .donut-legend { display: flex; flex-direction: column; gap: 8px; }

        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #555; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        .bar-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
        }

        .bar-card h3 { font-size: 13px; font-weight: 600; color: #333; margin-bottom: 15px; }

        .bar-item { margin-bottom: 12px; }
        .bar-label { font-size: 11px; color: #555; margin-bottom: 4px; display: flex; justify-content: space-between; }
        .bar-track { background: #f0f0f0; border-radius: 4px; height: 10px; overflow: hidden; }
        .bar-fill { height: 100%; background: #4caf50; border-radius: 4px; }

        .bar-footer { font-size: 11px; color: #999; text-align: right; margin-top: 10px; }

        /* Table */
        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .table-header h3 { font-size: 15px; font-weight: 600; color: #333; }

        .table-search {
            position: relative;
        }

        .table-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #999; font-size: 12px; }

        .table-search input {
            padding: 8px 12px 8px 30px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 12px;
            width: 200px;
        }

        .records-table { width: 100%; border-collapse: collapse; }

        .records-table th {
            text-align: left;
            padding: 10px 12px;
            font-size: 12px;
            color: #888;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
        }

        .records-table td {
            padding: 12px;
            font-size: 13px;
            color: #444;
            border-bottom: 1px solid #f5f5f5;
        }

        .land-type-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-residential { background: #e8f5e9; color: #2e7d32; }
        .badge-commercial { background: #e3f2fd; color: #1565c0; }
        .badge-agricultural { background: #f3e5f5; color: #6a1b9a; }
        .badge-industrial { background: #fff3e0; color: #e65100; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/DarlandIcon.png') }}" alt="Land GIS Logo">
        </div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item active">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
            <a href="/map-viewer" class="nav-item">
                <i class="fas fa-map"></i><span>Map Viewer</span>
            </a>
            <a href="/land-records" class="nav-item">
                <i class="fas fa-file-alt"></i><span>Land Records</span>
            </a>
        </nav>
        <div class="logout-section">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search documents...">
            </div>
            <div class="top-right">
                <i class="fas fa-bell bell-icon"></i>
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size:11px;color:#999;margin-left:4px"></i>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
            <h1 class="page-title">Dashboard Overview</h1>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h4>Total Records</h4>
                        <div class="stat-num">1,245</div>
                        <div class="stat-sub">Total all records</div>
                    </div>
                    <div class="stat-icon-right"><i class="fas fa-database"></i></div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info">
                        <h4>Registered Records</h4>
                        <div class="stat-num">892</div>
                    </div>
                    <div class="stat-icon-right"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-info">
                        <h4>Pending Records</h4>
                        <div class="stat-num">153</div>
                    </div>
                    <div class="stat-icon-right"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-info">
                        <h4>Land Types</h4>
                        <div class="stat-num">5</div>
                    </div>
                    <div class="stat-icon-right"><i class="fas fa-layer-group"></i></div>
                </div>
            </div>

            <!-- Middle Row -->
            <div class="middle-row">
                <!-- Donut Chart -->
                <div class="chart-card">
                    <div class="donut-wrapper">
                        <canvas id="donutChart"></canvas>
                        <div class="donut-center">
                            <div class="num">1,245</div>
                            <div class="label">Lots</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item"><div class="legend-dot" style="background:#4caf50"></div>Residential</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#2196f3"></div>Commercial</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#ff9800"></div>Agricultural</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#9c27b0"></div>Industrial</div>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="bar-card">
                    <h3>Land Records by Municipality</h3>
                    <div class="bar-item">
                        <div class="bar-label"><span>Urdaneta City</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:85%"></div></div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-label"><span>Binalonan</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:70%"></div></div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-label"><span>Villasis</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:55%"></div></div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-label"><span>Dagupan City</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:45%"></div></div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-label"><span>Natividad</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:40%"></div></div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-label"><span>Rosario</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:30%"></div></div>
                    </div>
                    <div class="bar-footer">Number of Records</div>
                </div>
            </div>

            <!-- Land Records Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Land Record</h3>
                    <div class="table-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search documents...">
                    </div>
                </div>
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Land ID</th>
                            <th>Owner</th>
                            <th>Location</th>
                            <th>Land Type</th>
                            <th>Area (sqm)</th>
                            <th>Status</th>
                            <th>Date Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>10293</td>
                            <td>Juan Dela Cruz</td>
                            <td>Brgy. Palina East, Urdaneta City</td>
                            <td><span class="land-type-badge badge-residential">Residential</span></td>
                            <td>600</td>
                            <td></td>
                            <td>May 12, 2023</td>
                        </tr>
                        <tr>
                            <td>10294</td>
                            <td>Juan Dela Cruz</td>
                            <td>Brgy. Palina East, Urdaneta City</td>
                            <td><span class="land-type-badge badge-commercial">Commercial</span></td>
                            <td>600</td>
                            <td></td>
                            <td>May 12, 2023</td>
                        </tr>
                        <tr>
                            <td>10295</td>
                            <td>Juan Dela Cruz</td>
                            <td>Brgy. Palina East, Urdaneta City</td>
                            <td><span class="land-type-badge badge-residential">Residential</span></td>
                            <td>600</td>
                            <td></td>
                            <td>May 12, 2023</td>
                        </tr>
                        <tr>
                            <td>10296</td>
                            <td>Juan Dela Cruz</td>
                            <td>Brgy. Palina East, Urdaneta City</td>
                            <td><span class="land-type-badge badge-residential">Residential</span></td>
                            <td>600</td>
                            <td></td>
                            <td>May 12, 2023</td>
                        </tr>
                        <tr>
                            <td>10297</td>
                            <td>Juan Dela Cruz</td>
                            <td>Brgy. Palina East, Urdaneta City</td>
                            <td><span class="land-type-badge badge-agricultural">Agricultural</span></td>
                            <td>600</td>
                            <td></td>
                            <td>May 12, 2023</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Donut Chart
        const ctx = document.getElementById('donutChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [450, 320, 280, 195],
                    backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#9c27b0'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                animation: { animateScale: true }
            }
        });
    </script>
</body>
</html>
