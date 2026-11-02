<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - DAR Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; background: #f5f5f5; font-family: 'Segoe UI', Tahoma, sans-serif; }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-wrapper {
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 380px;
            padding: 40px 36px 36px;
            text-align: center;
        }

        /* Logo */
        .logo-wrap {
            margin-bottom: 10px;
        }
        .logo-wrap img {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }

        .app-name {
            font-size: 28px;
            font-weight: 900;
            color: #1a2e52;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .app-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #555;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 28px;
        }

        /* Card divider */
        .card-inner {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 28px 26px;
            text-align: left;
        }

        .welcome-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a2e52;
            margin-bottom: 4px;
        }

        .welcome-sub {
            font-size: 12.5px;
            color: #888;
            margin-bottom: 22px;
        }

        /* System selector */
        .system-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .system-tab {
            flex: 1;
            padding: 9px 6px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-size: 11.5px;
            font-weight: 600;
            color: #888;
            text-align: center;
            transition: all 0.2s;
            line-height: 1.3;
        }

        .system-tab.active {
            border-color: #2d7a2d;
            color: #2d7a2d;
            background: #f0fdf4;
        }

        .system-tab i { display: block; font-size: 18px; margin-bottom: 4px; }

        /* Inputs */
        .input-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .input-wrap i.icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            font-size: 14px;
        }

        .input-wrap input {
            width: 100%;
            padding: 11px 12px 11px 36px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13.5px;
            color: #333;
            background: #fafafa;
            transition: border-color 0.2s;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: #2d7a2d;
            background: white;
        }

        .input-wrap input::placeholder { color: #bbb; }

        .eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            cursor: pointer;
            font-size: 14px;
        }

        .eye-toggle:hover { color: #666; }

        .forgot-row {
            text-align: left;
            margin-bottom: 18px;
        }

        .forgot-row a {
            font-size: 12px;
            color: #666;
            text-decoration: none;
        }

        .forgot-row a:hover { color: #2d7a2d; }

        /* Button */
        .login-btn {
            width: 100%;
            padding: 13px;
            background: #2d7a2d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.2s;
            text-transform: uppercase;
        }

        .login-btn:hover { background: #1e5e1e; }
        .login-btn:disabled { background: #aaa; cursor: not-allowed; }

        /* Bottom links */
        .bottom-links {
            margin-top: 20px;
            text-align: center;
        }

        .bottom-links .register-row {
            font-size: 12.5px;
            color: #888;
            margin-bottom: 8px;
        }

        .bottom-links .register-row a {
            color: #2d7a2d;
            font-weight: 700;
            text-decoration: none;
        }

        .bottom-links .back-home a {
            font-size: 12px;
            color: #aaa;
            text-decoration: none;
        }

        .bottom-links .back-home a:hover { color: #2d7a2d; }

        /* Alert */
        .alert-error {
            background: #fff1f2;
            color: #c62828;
            border: 1px solid #fca5a5;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 12.5px;
            margin-bottom: 16px;
        }

        .system-hint {
            font-size: 11px;
            color: #aaa;
            text-align: center;
            margin-top: 10px;
        }

        .system-hint span {
            font-weight: 600;
            color: #2d7a2d;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">

        <!-- Logo & App Name -->
        <div class="logo-wrap">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR Logo">
        </div>
        <div class="app-name">LAND GIS</div>
        <div class="app-subtitle">Land Geographic Information System</div>

        <!-- Login Card -->
        <div class="card-inner">
            <div class="welcome-title">Welcome Back!</div>
            <div class="welcome-sub">Sign in to continue to your account</div>

            @if($errors->any())
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- System selector tabs --}}
            <div class="system-tabs">
                <div class="system-tab active" id="tab-gis" onclick="selectSystem('gis')">
                    <i class="fas fa-map"></i>
                    Land GIS
                </div>
                <div class="system-tab" id="tab-fms" onclick="selectSystem('fms')">
                    <i class="fas fa-folder-open"></i>
                    File Management
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <input type="hidden" name="system" id="systemField" value="gis">

                <div class="input-wrap">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="Email address" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="input-wrap">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fas fa-eye eye-toggle" id="eyeToggle" onclick="togglePw()"></i>
                </div>

                <div class="forgot-row">
                    <a href="#">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">LOG IN</button>
            </form>

            <div class="system-hint" id="systemHint">
                Logging in to <span id="systemLabel">Land GIS</span>
            </div>
        </div>

        <div class="bottom-links">
            <div class="back-home">
                <a href="#">Back to Home</a>
            </div>
        </div>

    </div>

    <script>
        let selectedSystem = 'gis';

        function selectSystem(sys) {
            selectedSystem = sys;
            document.getElementById('systemField').value = sys;

            document.getElementById('tab-gis').classList.toggle('active', sys === 'gis');
            document.getElementById('tab-fms').classList.toggle('active', sys === 'fms');

            const label = sys === 'gis' ? 'Land GIS' : 'File Management System';
            document.getElementById('systemLabel').textContent = label;
        }

        function togglePw() {
            const inp = document.getElementById('password');
            const ico = document.getElementById('eyeToggle');
            const show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            ico.classList.toggle('fa-eye', !show);
            ico.classList.toggle('fa-eye-slash', show);
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'Signing in...';
        });
    </script>
</body>
</html>
