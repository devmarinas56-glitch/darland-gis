<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - DAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; background: #f5f5f5; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }

        .login-wrapper {
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 24px rgba(0,0,0,0.13);
            width: 100%;
            max-width: 400px;
            padding: 44px 38px 36px;
            text-align: center;
        }

        .logo-wrap { margin-bottom: 12px; }
        .logo-wrap img { width: 90px; height: 90px; object-fit: contain; }

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
            margin-bottom: 30px;
        }

        .card-inner {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 28px 26px;
            text-align: left;
        }

        .welcome-title { font-size: 18px; font-weight: 700; color: #1a2e52; margin-bottom: 4px; }
        .welcome-sub { font-size: 12.5px; color: #888; margin-bottom: 24px; }

        /* System buttons */
        .system-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 24px;
        }

        .sys-btn {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 14px 10px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .sys-btn:hover { border-color: #2d7a2d; background: #f0fdf4; }
        .sys-btn.active { border-color: #2d7a2d; background: #f0fdf4; }

        .sys-btn i { font-size: 24px; color: #2d7a2d; }
        .sys-btn-label { font-size: 12.5px; font-weight: 700; color: #1a2e52; }
        .sys-btn-sub { font-size: 10px; color: #888; }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #ccc;
            font-size: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e8e8e8;
        }

        /* Inputs */
        .input-wrap { position: relative; margin-bottom: 14px; }
        .input-wrap i.icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%); color: #bbb; font-size: 14px;
        }
        .input-wrap input {
            width: 100%; padding: 12px 36px;
            border: 1px solid #ddd; border-radius: 5px;
            font-size: 13.5px; color: #333; background: #fafafa;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus { outline: none; border-color: #2d7a2d; background: white; }
        .input-wrap input::placeholder { color: #bbb; }

        .eye-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%); color: #bbb;
            cursor: pointer; font-size: 14px;
        }

        .forgot-row { margin-bottom: 18px; }
        .forgot-row a { font-size: 12px; color: #666; text-decoration: none; }
        .forgot-row a:hover { color: #2d7a2d; }

        .login-btn {
            width: 100%; padding: 13px;
            background: #2d7a2d; color: white;
            border: none; border-radius: 5px;
            font-size: 14px; font-weight: 700;
            letter-spacing: 1px; cursor: pointer;
            transition: background 0.2s; text-transform: uppercase;
        }
        .login-btn:hover { background: #1e5e1e; }
        .login-btn:disabled { background: #aaa; cursor: not-allowed; }

        .alert-error {
            background: #fff1f2; color: #c62828;
            border: 1px solid #fca5a5; border-radius: 5px;
            padding: 10px 14px; font-size: 12.5px; margin-bottom: 16px;
        }

        .bottom-links { margin-top: 18px; text-align: center; }
        .bottom-links a { font-size: 12px; color: #aaa; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-wrapper">

        <div class="logo-wrap">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR Logo">
        </div>
        <div class="app-name">DAR PANGASINAN</div>
        <div class="app-subtitle">Department of Agrarian Reform</div>

        <div class="card-inner">
            <div class="welcome-title">Welcome Back!</div>
            <div class="welcome-sub">Select a system to continue</div>

            {{-- System buttons --}}
            <div class="system-btns">
                <button class="sys-btn" id="btn-gis" onclick="selectSystem('gis')">
                    <i class="fas fa-map"></i>
                    <div class="sys-btn-label">Land GIS</div>
                    <div class="sys-btn-sub">Geographic System</div>
                </button>
                <button class="sys-btn" id="btn-fms" onclick="selectSystem('fms')">
                    <i class="fas fa-folder-open"></i>
                    <div class="sys-btn-label">File Management</div>
                    <div class="sys-btn-sub">Document System</div>
                </button>
            </div>

            <div class="divider">or sign in with your account</div>

            @if($errors->any())
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <input type="hidden" name="system" id="systemField" value="">

                <div class="input-wrap">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" id="emailInput"
                           placeholder="Email address"
                           value="{{ old('email') }}" required>
                </div>

                <div class="input-wrap">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" id="password"
                           placeholder="Password" required>
                    <i class="fas fa-eye eye-toggle" id="eyeToggle" onclick="togglePw()"></i>
                </div>

                <div class="forgot-row">
                    <a href="#">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">LOG IN</button>
            </form>
        </div>

        <div class="bottom-links">
            <a href="#">Back to Home</a>
        </div>
    </div>

    <script>
        // Pre-select system based on email domain if typed
        document.getElementById('emailInput').addEventListener('input', function() {
            const email = this.value.toLowerCase();
            if (email.includes('gis@') || email.includes('gisadmin@')) {
                setActive('gis');
            } else if (email.length > 3) {
                setActive('fms');
            }
        });

        function selectSystem(sys) {
            setActive(sys);
            // Focus email field
            document.getElementById('emailInput').focus();
        }

        function setActive(sys) {
            document.getElementById('systemField').value = sys;
            document.getElementById('btn-gis').classList.toggle('active', sys === 'gis');
            document.getElementById('btn-fms').classList.toggle('active', sys === 'fms');
        }

        function togglePw() {
            const inp = document.getElementById('password');
            const ico = document.getElementById('eyeToggle');
            const show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            ico.classList.toggle('fa-eye', !show);
            ico.classList.toggle('fa-eye-slash', show);
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // If FMS button selected, go directly to FMS login page
            const sys = document.getElementById('systemField').value;
            if (sys === 'fms') {
                e.preventDefault();
                const email    = document.getElementById('emailInput').value;
                const password = document.getElementById('password').value;
                // POST to FMS via a hidden form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'https://dar-file-management-system.onrender.com/login';
                form.innerHTML = `
                    <input name="_token" value="">
                    <input name="email" value="${email}">
                    <input name="password" value="${password}">
                `;
                // Can't pass CSRF across domains — just redirect with email pre-filled
                window.location.href = 'https://dar-file-management-system.onrender.com/login?email=' + encodeURIComponent(email);
                return;
            }
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'Signing in...';
        });
    </script>
</body>
</html>
