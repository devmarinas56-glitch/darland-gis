<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 190px;
            background: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            z-index: 100;
            border-right: 1px solid #e5e7eb;
        }

        .logo-section {
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        .logo-section img { width: 42px; height: 42px; object-fit: contain; flex-shrink: 0; }
        .logo-text { line-height: 1.2; }
        .logo-text .dept { font-size: 8px; font-weight: 700; color: #333; text-transform: uppercase; letter-spacing: 0.3px; }
        .logo-text .name { font-size: 7px; color: #666; text-transform: uppercase; letter-spacing: 0.2px; }

        .nav-menu { flex: 1; padding: 10px 0; }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 11px 18px;
            color: #555;
            text-decoration: none;
            gap: 11px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.15s;
            position: relative;
        }
        .nav-item i { font-size: 15px; width: 17px; flex-shrink: 0; }
        .nav-item:hover { background: #f5f5f5; color: #333; }
        .nav-item.active {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 600;
        }
        .nav-item.active::left { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: #2e7d32; border-radius: 0 2px 2px 0; }

        .logout-section { padding: 12px 14px; border-top: 1px solid #f0f0f0; }
        .logout-btn {
            width: 100%;
            padding: 10px 14px;
            background: #fff;
            color: #555;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
        }
        .logout-btn:hover { background: #fef2f2; color: #c62828; border-color: #f5c6c6; }

        /* ── Main ── */
        .main-content {
            flex: 1;
            margin-left: 190px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top bar ── */
        .top-bar {
            background: white;
            padding: 10px 28px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .bell-btn {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: #666;
            font-size: 15px;
            transition: background 0.15s;
        }
        .bell-btn:hover { background: #eee; color: #333; }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            background: #f8f8f8;
            padding: 5px 12px 5px 5px;
            border-radius: 50px;
            border: 1px solid #e8e8e8;
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #4a5568;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
            flex-shrink: 0;
        }
        .user-info .user-name { font-size: 13px; font-weight: 600; color: #222; line-height: 1.2; }
        .user-info .user-role { font-size: 11px; color: #888; }

        /* ── Content area ── */
        .content-area { padding: 24px 28px 40px; flex: 1; }

        .page-title { font-size: 20px; font-weight: 700; color: #1a1a1a; }
        .page-subtitle { font-size: 13px; color: #888; margin-top: 2px; margin-bottom: 24px; }

        /* ── Calendar filter ── */
        .content-header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }
        .calendar-btn {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 13px;
            color: #444;
            cursor: pointer;
            font-weight: 500;
            transition: border-color 0.15s;
        }
        .calendar-btn:hover { border-color: #aaa; }
        .calendar-btn i { font-size: 13px; color: #777; }
        .calendar-btn .chevron { font-size: 11px; color: #bbb; }

        /* ── Stat cards ── */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .stat-card-label { font-size: 12px; color: #888; font-weight: 500; margin-bottom: 8px; }
        .stat-card-num { font-size: 36px; font-weight: 800; color: #1a1a1a; line-height: 1; margin-bottom: 6px; }
        .stat-card-sub { font-size: 11px; color: #aaa; }

        /* ── Announcements card ── */
        .announcements-card {
            background: white;
            border-radius: 12px;
            padding: 22px 26px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .announcements-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 18px;
        }

        .announcement-item {
            padding: 14px 0;
            border-bottom: 1px solid #f3f3f3;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .announcement-item:last-child { border-bottom: none; padding-bottom: 0; }
        .announcement-item:first-child { padding-top: 0; }

        .ann-body { flex: 1; }
        .ann-title { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .ann-desc { font-size: 12px; color: #4a90d9; }
        .ann-date { font-size: 12px; color: #aaa; white-space: nowrap; flex-shrink: 0; margin-top: 2px; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .stat-cards { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .sidebar { width: 54px; }
            .logo-text, .nav-item span, .logout-btn span { display: none; }
            .nav-item { justify-content: center; padding: 13px; gap: 0; }
            .main-content { margin-left: 54px; }
            .stat-cards { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
            <div class="logo-text">
                <div class="dept">Department of</div>
                <div class="name">Agrarian Reform</div>
            </div>
        </div>
        <nav class="nav-menu">
            <a href="/dashboard"     class="nav-item active"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="/submit-report" class="nav-item"><i class="fas fa-file-alt"></i><span>Submit Report</span></a>
            <a href="/my-reports"    class="nav-item"><i class="fas fa-folder-open"></i><span>My Reports</span></a>
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
            <button class="bell-btn"><i class="fas fa-bell"></i></button>
            <a href="/profile" class="user-chip">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ strtoupper(auth()->user()->role ?? 'Staff') }}</div>
                </div>
            </a>
        </div>

        <!-- Content -->
        <div class="content-area">
            <div class="page-title">Dashboard</div>
            <div class="page-subtitle">Welcome back, {{ auth()->user()->name }}!</div>

            <!-- Calendar filter -->
            <div class="content-header">
                <button class="calendar-btn">
                    <i class="fas fa-calendar-alt"></i>
                    Calendar
                    <i class="fas fa-chevron-down chevron"></i>
                </button>
            </div>

            <!-- Stat Cards -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-card-label">Total Submitted</div>
                    <div class="stat-card-num">{{ $totalSubmitted ?? 0 }}</div>
                    <div class="stat-card-sub">This Year</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Pending Review</div>
                    <div class="stat-card-num">{{ $pendingReview ?? 0 }}</div>
                    <div class="stat-card-sub">For Approval</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Approved</div>
                    <div class="stat-card-num">{{ $approved ?? 0 }}</div>
                    <div class="stat-card-sub">This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Return</div>
                    <div class="stat-card-num">{{ $returned ?? 0 }}</div>
                    <div class="stat-card-sub">For Revision</div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="announcements-card">
                <div class="announcements-title">Recent Announcements</div>

                @forelse($announcements ?? [] as $ann)
                    <div class="announcement-item">
                        <div class="ann-body">
                            <div class="ann-title">{{ $ann['title'] }}</div>
                            <div class="ann-desc">{{ $ann['description'] }}</div>
                        </div>
                        <div class="ann-date">{{ $ann['date'] }}</div>
                    </div>
                @empty
                    <div class="announcement-item">
                        <div class="ann-body">
                            <div class="ann-title">Deadline of Submission - May 31, 2026</div>
                            <div class="ann-desc">Please submit all accomplishment on or before the deadline.</div>
                        </div>
                        <div class="ann-date">May 15, 2026</div>
                    </div>
                    <div class="announcement-item">
                        <div class="ann-body">
                            <div class="ann-title">New Template Available</div>
                            <div class="ann-desc">Use the new accomplishment template for accurate reporting.</div>
                        </div>
                        <div class="ann-date">May 01, 2026</div>
                    </div>
                @endforelse
            </div>

        </div><!-- /content-area -->
    </div><!-- /main-content -->

</body>
</html>
