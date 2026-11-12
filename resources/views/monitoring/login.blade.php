<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring Access — DarLand GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Tahoma, sans-serif; }

        body {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0d1b3e 0%, #1a2744 50%, #0d2b1a 100%);
        }

        .login-wrap {
            width: 100%; max-width: 420px;
            padding: 20px;
        }

        /* Logo area */
        .brand {
            text-align: center; margin-bottom: 32px;
        }
        .brand img {
            width: 68px; height: 68px; object-fit: contain;
            margin-bottom: 12px; display: block; margin-inline: auto;
        }
        .brand-title {
            font-size: 22px; font-weight: 800; color: white; margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 13px; color: rgba(255,255,255,0.55);
            font-style: italic;
        }
        .brand-badge {
            display: inline-block; margin-top: 8px;
            background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.75);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px; padding: 3px 14px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        }

        /* Card */
        .card {
            background: white; border-radius: 16px;
            padding: 32px; box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        }
        .card-title {
            font-size: 16px; font-weight: 700; color: #1a2744;
            margin-bottom: 4px; display: flex; align-items: center; gap: 8px;
        }
        .card-sub { font-size: 12.5px; color: #aaa; margin-bottom: 24px; }

        /* Form */
        .field { margin-bottom: 16px; }
        .field label { font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 5px; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #bbb; font-size: 14px; pointer-events: none;
        }
        .field input {
            width: 100%; padding: 11px 40px 11px 38px;
            border: 1.5px solid #e0e0e0; border-radius: 9px;
            font-size: 13.5px; color: #333; outline: none;
            transition: border-color 0.2s;
        }
        .field input:focus { border-color: #1a2744; }
        .field input.error { border-color: #e53935; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #bbb; font-size: 14px; padding: 2px;
        }
        .pw-toggle:hover { color: #1a2744; }

        .err-msg {
            background: #ffebee; border: 1px solid #ffcdd2;
            border-radius: 8px; padding: 10px 14px;
            font-size: 12.5px; color: #c62828;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 16px;
        }

        .submit-btn {
            width: 100%; padding: 13px;
            background: #1a2744; color: white;
            border: none; border-radius: 9px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .submit-btn:hover { background: #2d4070; }

        .back-link {
            display: block; text-align: center; margin-top: 20px;
            font-size: 12.5px; color: rgba(255,255,255,0.55); text-decoration: none;
        }
        .back-link:hover { color: rgba(255,255,255,0.85); }

        .info-box {
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px; padding: 12px 16px; margin-top: 20px;
            font-size: 12px; color: rgba(255,255,255,0.55);
            line-height: 1.6;
        }
        .info-box i { margin-right: 5px; color: rgba(255,255,255,0.4); }
    </style>
</head>
<body>
    <div class="login-wrap">

        <div class="brand">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DarLand">
            <div class="brand-title">DarLand GIS</div>
            <div class="brand-sub">Department of Agrarian Reform — Pangasinan</div>
            <span class="brand-badge"><i class="fas fa-eye" style="margin-right:5px"></i>Monitoring Portal</span>
        </div>

        <div class="card">
            <div class="card-title">
                <i class="fas fa-map-marked-alt" style="color:#1a2744;font-size:18px"></i>
                Monitoring Access
            </div>
            <div class="card-sub">Sign in with your monitoring credentials to view land survey data.</div>

            @if($errors->any())
            <div class="err-msg">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('error'))
            <div class="err-msg">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('monitoring.login.post') }}">
                @csrf
                <div class="field">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="monitoring@dar.gov.ph"
                               class="{{ $errors->has('email') ? 'error' : '' }}"
                               autocomplete="email" required>
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="monPwField"
                               placeholder="••••••••"
                               class="{{ $errors->has('password') ? 'error' : '' }}"
                               autocomplete="current-password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()">
                            <i class="fas fa-eye" id="monPwEye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Monitoring
                </button>
            </form>
        </div>

        <a href="{{ route('login') }}" class="back-link">
            <i class="fas fa-arrow-left" style="margin-right:5px"></i>Back to main login
        </a>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            This portal is for authorised monitoring personnel only.
            Contact the DAR administrator if you need access.
        </div>

    </div>

    <script>
        function togglePw() {
            const f = document.getElementById('monPwField');
            const e = document.getElementById('monPwEye');
            if (f.type === 'password') { f.type = 'text'; e.className = 'fas fa-eye-slash'; }
            else { f.type = 'password'; e.className = 'fas fa-eye'; }
        }
    </script>
</body>
</html>
