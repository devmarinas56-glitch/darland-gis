<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - DAR</title>
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
            transition: all 0.15s;
        }
        .nav-item i { font-size: 15px; width: 17px; flex-shrink: 0; }
        .nav-item:hover { background: #f5f5f5; color: #333; }
        .nav-item.active { background: #e8f5e9; color: #2e7d32; font-weight: 600; }

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
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .top-bar-left { }
        .top-bar-title { font-size: 16px; font-weight: 700; color: #1a1a1a; }
        .breadcrumb { font-size: 12px; color: #aaa; margin-top: 1px; }
        .breadcrumb a { color: #aaa; text-decoration: none; }
        .breadcrumb a:hover { color: #555; }
        .breadcrumb span { margin: 0 5px; }

        .top-bar-right { display: flex; align-items: center; gap: 16px; }
        .bell-btn {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: #666;
            font-size: 15px;
        }
        .bell-btn:hover { background: #eee; }

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
        }
        .user-info .user-name { font-size: 13px; font-weight: 600; color: #222; line-height: 1.2; }
        .user-info .user-role { font-size: 11px; color: #888; }

        /* ── Content ── */
        .content-area { padding: 24px 28px 40px; flex: 1; }

        /* ── Year filter ── */
        .content-header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .year-select {
            padding: 7px 30px 7px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            font-size: 13px;
            color: #444;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            cursor: pointer;
            font-family: inherit;
            font-weight: 500;
        }
        .year-select:focus { outline: none; border-color: #2e7d32; }

        /* ── Month grid ── */
        .month-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .month-card {
            background: white;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            display: block;
            transition: box-shadow 0.15s, transform 0.15s;
            aspect-ratio: 1.1 / 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .month-card:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        .month-card.has-report { border-top: 3px solid #2e7d32; }
        .month-card.has-pending { border-top: 3px solid #f59e0b; }

        .month-name {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .month-sub {
            font-size: 11px;
            color: #aaa;
        }
        .month-sub.submitted { color: #2e7d32; }
        .month-sub.pending   { color: #f59e0b; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .month-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 650px) {
            .month-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .sidebar { width: 54px; }
            .logo-text, .nav-item span, .logout-btn span { display: none; }
            .nav-item { justify-content: center; padding: 13px; gap: 0; }
            .main-content { margin-left: 54px; }
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
            <a href="/dashboard"     class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="/submit-report" class="nav-item"><i class="fas fa-file-alt"></i><span>Submit Report</span></a>
            <a href="/my-reports"    class="nav-item active"><i class="fas fa-folder-open"></i><span>My Reports</span></a>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="nav-item"><i class="fas fa-users-cog"></i><span>Users</span></a>
            @endif
            <a href="/profile" class="nav-item"><i class="fas fa-cog"></i><span>Account Setting</span></a>
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
            <div class="top-bar-left">
                <div class="top-bar-title">My Reports</div>
                <div class="breadcrumb">
                    <a href="/dashboard">Dashboard</a>
                    <span>›</span>
                    My Reports
                </div>
            </div>
            <div class="top-bar-right">
                <button class="bell-btn"><i class="fas fa-bell"></i></button>
                <a href="/profile" class="user-chip">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ strtoupper(auth()->user()->role ?? 'Staff') }}</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">

            <!-- Year filter -->
            <div class="content-header">
                <select class="year-select" id="yearSelect" onchange="changeYear(this.value)">
                    @php $currentYear = date('Y'); @endphp
                    @for ($y = $currentYear; $y >= $currentYear - 4; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Month grid -->
            <div class="month-grid">
                @php
                    $months = [
                        1  => 'January',  2  => 'February', 3  => 'March',
                        4  => 'April',    5  => 'May',       6  => 'June',
                        7  => 'July',     8  => 'August',    9  => 'September',
                        10 => 'October',  11 => 'November',  12 => 'December',
                    ];
                @endphp
                @foreach ($months as $num => $name)
                    @php
                        $report = $reportsByMonth[$num] ?? null;
                        $cardClass = '';
                        $subText = 'No Report';
                        $subClass = '';
                        if ($report) {
                            if ($report->status === 'approved') {
                                $cardClass = 'has-report';
                                $subText = 'Submitted';
                                $subClass = 'submitted';
                            } elseif ($report->status === 'pending') {
                                $cardClass = 'has-pending';
                                $subText = 'Pending';
                                $subClass = 'pending';
                            } else {
                                $subText = ucfirst($report->status);
                            }
                        }
                    @endphp
                    <a href="/my-reports/{{ $selectedYear }}/{{ $num }}"
                       class="month-card {{ $cardClass }}">
                        <div class="month-name">{{ strtoupper($name) }}</div>
                        <div class="month-sub {{ $subClass }}">{{ $subText }}</div>
                    </a>
                @endforeach
            </div>

        </div><!-- /content-area -->
    </div><!-- /main-content -->

    <script>
        function changeYear(year) {
            window.location.href = '/my-reports?year=' + year;
        }
    </script>

</body>
</html>
