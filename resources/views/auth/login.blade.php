<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — DAR Pangasinan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html,body { height:100%; font-family:'Segoe UI',Tahoma,sans-serif; }

        body {
            display:flex; align-items:stretch; min-height:100vh;
            background:#f5f7fa;
        }

        /* ── Left branding panel ── */
        .brand-panel {
            width:420px; flex-shrink:0;
            background:linear-gradient(160deg,#1a3d1a 0%,#2d6e2d 60%,#1a2744 100%);
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            padding:60px 48px; text-align:center;
        }
        .brand-panel img { width:100px; height:100px; object-fit:contain; margin-bottom:24px; }
        .brand-panel .title { font-size:28px; font-weight:900; color:white; letter-spacing:2px; text-transform:uppercase; margin-bottom:6px; }
        .brand-panel .subtitle { font-size:12px; font-weight:600; color:rgba(255,255,255,0.65); letter-spacing:1.5px; text-transform:uppercase; margin-bottom:36px; }
        .brand-divider { width:40px; height:3px; background:rgba(255,255,255,0.3); border-radius:2px; margin:0 auto 32px; }

        .brand-system { display:flex; flex-direction:column; gap:12px; width:100%; }
        .brand-sys-item {
            display:flex; align-items:center; gap:12px; padding:12px 16px;
            background:rgba(255,255,255,0.08); border-radius:10px;
            border:1px solid rgba(255,255,255,0.12);
        }
        .brand-sys-item i { font-size:18px; color:rgba(255,255,255,0.7); width:22px; }
        .brand-sys-name { font-size:13px; font-weight:700; color:white; }
        .brand-sys-desc { font-size:11px; color:rgba(255,255,255,0.5); }

        /* ── Right login panel ── */
        .login-panel {
            flex:1; display:flex; align-items:center; justify-content:center;
            padding:40px 24px;
        }
        .login-box { width:100%; max-width:400px; }

        .login-welcome { font-size:24px; font-weight:800; color:#1a2744; margin-bottom:4px; }
        .login-sub { font-size:13px; color:#888; margin-bottom:32px; }

        /* Role indicator — auto-detected from email */
        .role-indicator {
            display:none; align-items:center; gap:10px;
            padding:10px 14px; border-radius:9px; margin-bottom:16px;
            font-size:12.5px; font-weight:600;
        }
        .role-indicator.gis   { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; display:flex; }
        .role-indicator.fms   { background:#e3f2fd; color:#1565c0; border:1px solid #bbdefb; display:flex; }
        .role-indicator.mon   { background:#f3e5f5; color:#6a1b9a; border:1px solid #e1bee7; display:flex; }
        .role-indicator i { font-size:14px; }

        .field { margin-bottom:16px; }
        .field label { font-size:12px; font-weight:600; color:#555; display:block; margin-bottom:6px; }
        .input-wrap { position:relative; }
        .input-icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#bbb; font-size:14px; pointer-events:none; }
        .field input {
            width:100%; padding:12px 42px;
            border:1.5px solid #e0e0e0; border-radius:9px;
            font-size:13.5px; color:#333; background:#fafafa;
            transition:border-color 0.2s, background 0.2s;
        }
        .field input:focus { outline:none; border-color:#2d7a2d; background:white; }
        .field input::placeholder { color:#bbb; }
        .pw-toggle { position:absolute; right:13px; top:50%; transform:translateY(-50%); color:#bbb; cursor:pointer; font-size:14px; border:none; background:none; padding:2px; }
        .pw-toggle:hover { color:#555; }

        .login-btn {
            width:100%; padding:14px;
            background:#1a3d1a; color:white;
            border:none; border-radius:9px;
            font-size:14px; font-weight:700; letter-spacing:0.5px;
            cursor:pointer; transition:background 0.2s;
            display:flex; align-items:center; justify-content:center; gap:8px;
            margin-top:4px;
        }
        .login-btn:hover { background:#245c24; }
        .login-btn:disabled { background:#aaa; cursor:not-allowed; }

        .alert-error {
            background:#fff1f2; color:#c62828;
            border:1px solid #fca5a5; border-radius:8px;
            padding:11px 14px; font-size:12.5px; margin-bottom:18px;
            display:flex; align-items:flex-start; gap:8px;
        }

        @if(session('locked_until'))
        .lock-banner { /* handled inline */ }
        @endif

        .lock-banner {
            background:#fff8e1; border:1px solid #ffe082; border-radius:8px;
            padding:11px 14px; font-size:12.5px; margin-bottom:18px;
            display:flex; align-items:center; gap:8px; color:#5d4037;
        }
        .lock-countdown { font-weight:700; color:#e65100; }

        .login-footer { text-align:center; margin-top:28px; }
        .login-footer a { font-size:12px; color:#aaa; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
        .login-footer a:hover { color:#1a3d1a; }

        /* Responsive — stack on mobile */
        @media(max-width:720px) {
            body { flex-direction:column; }
            .brand-panel { width:100%; padding:40px 28px 32px; }
            .brand-panel img { width:72px; height:72px; }
            .brand-panel .title { font-size:22px; }
            .brand-system { display:none; }
        }
    </style>
</head>
<body>

    <!-- Left branding -->
    <div class="brand-panel">
        <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
        <div class="title">DAR</div>
        <div class="subtitle">Pangasinan — Integrated Portal</div>
        <div class="brand-divider"></div>
        <div class="brand-system">
            <div class="brand-sys-item">
                <i class="fas fa-map-marked-alt"></i>
                <div>
                    <div class="brand-sys-name">Land GIS</div>
                    <div class="brand-sys-desc">Geospatial land records & surveys</div>
                </div>
            </div>
            <div class="brand-sys-item">
                <i class="fas fa-folder-open"></i>
                <div>
                    <div class="brand-sys-name">File Management</div>
                    <div class="brand-sys-desc">Documents & accomplishment reports</div>
                </div>
            </div>
            <div class="brand-sys-item">
                <i class="fas fa-eye"></i>
                <div>
                    <div class="brand-sys-name">Monitoring Portal</div>
                    <div class="brand-sys-desc">Read-only oversight for DAR Heads</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right login -->
    <div class="login-panel">
        <div class="login-box">

            <div class="login-welcome">Welcome Back</div>
            <div class="login-sub">Sign in to access the DAR Pangasinan system</div>

            @if(session('locked_until'))
            <div class="lock-banner">
                <i class="fas fa-lock" style="color:#e65100"></i>
                Account temporarily locked. Try again in
                <span class="lock-countdown" id="countdown">—</span>
            </div>
            @endif

            @if($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- Auto-detected role indicator -->
            <div class="role-indicator" id="roleIndicator">
                <i id="roleIcon"></i>
                <span id="roleText"></span>
            </div>

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="field">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="emailInput"
                               placeholder="your@dar.gov.ph"
                               value="{{ old('email') }}"
                               oninput="detectRole(this.value)"
                               autocomplete="email" required>
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="pwInput"
                               placeholder="••••••••"
                               autocomplete="current-password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()" tabindex="-1">
                            <i class="fas fa-eye" id="pwEye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="login-footer">
                <a href="{{ route('monitoring.login') }}">
                    <i class="fas fa-eye"></i> Monitoring Portal (DAR Heads)
                </a>
            </div>

        </div>
    </div>

    <script>
        // ── Role detection from email ────────────────────────────
        const roleMap = {
            'gisadmin@dar.gov.ph' : { cls:'gis', icon:'fa-map-marked-alt', text:'Land GIS — Administrator' },
            'gis@dar.gov.ph'      : { cls:'gis', icon:'fa-map-marked-alt', text:'Land GIS — Staff' },
            'admin@dar.gov.ph'    : { cls:'fms', icon:'fa-folder-open',    text:'File Management — Admin' },
            'ajdp@dar.gov.ph'     : { cls:'fms', icon:'fa-folder-open',    text:'File Management — AJDP Staff' },
            'ltsp@dar.gov.ph'     : { cls:'fms', icon:'fa-folder-open',    text:'File Management — LTSP Staff' },
            'arbdsp@dar.gov.ph'   : { cls:'fms', icon:'fa-folder-open',    text:'File Management — ARBDSP Staff' },
            'monitoring@dar.gov.ph':{ cls:'mon', icon:'fa-eye',            text:'Monitoring Portal — DAR Heads' },
        };

        function detectRole(email) {
            const e   = email.toLowerCase().trim();
            const ind = document.getElementById('roleIndicator');
            const ico = document.getElementById('roleIcon');
            const txt = document.getElementById('roleText');

            // Exact match first
            if (roleMap[e]) {
                const r = roleMap[e];
                ind.className = 'role-indicator ' + r.cls;
                ico.className = 'fas ' + r.icon;
                txt.textContent = r.text;
                return;
            }

            // Partial — detect system by email pattern
            ind.className = 'role-indicator';  // hide
        }

        // ── Password toggle ──────────────────────────────────────
        function togglePw() {
            const inp = document.getElementById('pwInput');
            const eye = document.getElementById('pwEye');
            const show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            eye.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        }

        // ── Spinner on submit ────────────────────────────────────
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in…';
        });

        // ── Lock countdown ───────────────────────────────────────
        @if(session('locked_until'))
        const lockUntil = {{ session('locked_until') }} * 1000;
        function updateCountdown() {
            const left = Math.max(0, Math.ceil((lockUntil - Date.now()) / 1000));
            const el   = document.getElementById('countdown');
            if (el) {
                el.textContent = left + 's';
                if (left > 0) setTimeout(updateCountdown, 500);
                else {
                    document.getElementById('loginBtn').disabled = false;
                    el.closest('.lock-banner').style.display = 'none';
                }
            }
        }
        document.getElementById('loginBtn').disabled = true;
        updateCountdown();
        @endif
    </script>
</body>
</html>
