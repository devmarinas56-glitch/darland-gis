<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring Portal — DAR Pangasinan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
        body{display:flex;min-height:100vh}
        .left{width:45%;flex-shrink:0;background:linear-gradient(160deg,#1a3d1a 0%,#2a5c2a 55%,#163016 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 48px;text-align:center;position:relative;overflow:hidden}
        .left::before{content:'';position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Ccircle cx='50' cy='50' r='40' fill='none' stroke='rgba(255,255,255,0.04)' stroke-width='1'/%3E%3Ccircle cx='50' cy='50' r='30' fill='none' stroke='rgba(255,255,255,0.03)' stroke-width='1'/%3E%3C/svg%3E");background-size:120px 120px;opacity:0.6}
        .left-content{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center}
        .seal-ring{width:160px;height:160px;border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 32px rgba(0,0,0,0.25);margin-bottom:32px;border:4px solid rgba(255,255,255,0.3)}
        .seal-ring img{width:140px;height:140px;object-fit:contain;border-radius:50%}
        .left-republic{font-size:11px;font-weight:600;color:rgba(255,255,255,0.6);letter-spacing:2px;text-transform:uppercase;margin-bottom:10px}
        .left-agency{font-size:30px;font-weight:900;color:white;line-height:1.2;margin-bottom:6px}
        .left-province{font-size:14px;font-weight:600;color:rgba(255,255,255,0.7);letter-spacing:3px;text-transform:uppercase;margin-bottom:20px}
        .left-divider{width:36px;height:2px;background:rgba(255,255,255,0.4);border-radius:1px;margin:0 auto 16px}
        .left-system{font-size:16px;font-weight:400;color:rgba(255,255,255,0.8);font-style:italic}
        .right{flex:1;background:white;display:flex;align-items:center;justify-content:center;padding:60px 48px}
        .form-box{width:100%;max-width:380px}
        .access-label{font-size:11px;font-weight:700;color:#2a5c2a;letter-spacing:2.5px;text-transform:uppercase;margin-bottom:10px}
        .welcome{font-size:36px;font-weight:900;color:#111;line-height:1.1;margin-bottom:8px}
        .welcome-sub{font-size:14px;color:#888;margin-bottom:32px}
        .alert-box{padding:11px 14px;border-radius:8px;margin-bottom:20px;font-size:13px;display:flex;align-items:flex-start;gap:8px}
        .alert-err{background:#fef2f2;color:#c62828;border:1px solid #fecaca}
        .field{margin-bottom:16px}
        .field-label{font-size:12.5px;color:#555;font-weight:500;margin-bottom:6px;display:block}
        .field-input{width:100%;padding:13px 16px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;color:#222;background:#fafafa;transition:border-color 0.2s,background 0.15s}
        .field-input:focus{outline:none;border-color:#2a5c2a;background:white}
        .field-input::placeholder{color:#bbb}
        .pw-wrap{position:relative}
        .pw-wrap .field-input{padding-right:46px}
        .pw-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#bbb;font-size:16px;padding:2px}
        .pw-eye:hover{color:#555}
        .sign-btn{width:100%;padding:15px;background:#2a5c2a;color:white;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;transition:background 0.2s;margin-top:8px}
        .sign-btn:hover{background:#1e4212}
        .sign-btn:disabled{background:#a0a0a0;cursor:not-allowed}
        .form-footer{margin-top:28px;text-align:center;font-size:12.5px;color:#aaa}
        .form-footer a{color:#888;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
        .form-footer a:hover{color:#2a5c2a}
        @media(max-width:768px){body{flex-direction:column}.left{width:100%;padding:40px 28px 32px}.seal-ring{width:110px;height:110px;margin-bottom:20px}.seal-ring img{width:94px;height:94px}.left-agency{font-size:22px}.right{padding:40px 24px}}
    </style>
</head>
<body>
    <div class="left">
        <div class="left-content">
            <div class="seal-ring">
                <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR Seal">
            </div>
            <div class="left-republic">Republic of the Philippines</div>
            <div class="left-agency">Department of<br>Agrarian Reform</div>
            <div class="left-province">Pangasinan</div>
            <div class="left-divider"></div>
            <div class="left-system">Monitoring Portal</div>
        </div>
    </div>
    <div class="right">
        <div class="form-box">
            <div class="access-label">Authorized Access</div>
            <div class="welcome">Welcome back.</div>
            <div class="welcome-sub">GIS monitoring access for DAR Heads only.</div>

            @if($errors->any())
            <div class="alert-box alert-err">
                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="alert-box alert-err">
                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('monitoring.login.post') }}">
                @csrf
                <div class="field">
                    <label class="field-label">Email address</label>
                    <input class="field-input" type="email" name="email"
                           placeholder="monitoring@dar.gov.ph"
                           value="{{ old('email') }}" autocomplete="email" required>
                </div>
                <div class="field">
                    <label class="field-label">Password</label>
                    <div class="pw-wrap">
                        <input class="field-input" type="password" name="password" id="pwInput"
                               placeholder="Enter your password"
                               autocomplete="current-password" required>
                        <button type="button" class="pw-eye" onclick="togglePw()">
                            <i class="fas fa-eye" id="pwEye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="sign-btn">Sign In</button>
            </form>

            <div class="form-footer">
                <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Back to main login</a>
            </div>
        </div>
    </div>
    <script>
        function togglePw(){const i=document.getElementById('pwInput'),e=document.getElementById('pwEye');const s=i.type==='password';i.type=s?'text':'password';e.className=s?'fas fa-eye-slash':'fas fa-eye';}
    </script>
</body>
</html>
