<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            height: 100vh;
        }

        /* Left Side - clean image only */
        .left-panel {
            flex: 1;
            overflow: hidden;
            height: 100vh;
        }

        .left-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Right Side */
        .right-panel {
            width: 420px;
            min-width: 420px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            height: 100vh;
            overflow: hidden;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 45px 40px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 15px;
        }

        .form-input {
            width: 100%;
            padding: 14px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            background: white;
            transition: border-color 0.3s;
        }

        .form-input:focus { outline: none; border-color: #2d7a2d; }
        .form-input::placeholder { color: #aaa; }

        .eye-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            font-size: 16px;
        }

        .forgot-password { text-align: left; margin-bottom: 20px; }
        .forgot-password a { font-size: 13px; color: #2d7a2d; text-decoration: none; }
        .forgot-password a:hover { text-decoration: underline; }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: #555;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 25px;
        }

        .login-btn:hover { background: #2d7a2d; }

        .register-row { text-align: center; font-size: 13px; color: #666; margin-bottom: 12px; }
        .register-row a { color: #1a1a1a; font-weight: 700; text-decoration: none; }
        .register-row a:hover { color: #2d7a2d; }

        .back-home { text-align: center; font-size: 13px; }
        .back-home a { color: #888; text-decoration: none; }
        .back-home a:hover { color: #2d7a2d; }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; min-width: unset; }
        }
    </style>
</head>
<body>
    <!-- Left Panel - image only, no overlaid text -->
    <div class="left-panel">
        <img src="{{ asset('images/DarlandBG.png') }}" alt="Land GIS">
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
        <div class="login-card">

            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <i class="fas fa-user form-icon"></i>
                    <input type="email" name="email" class="form-input" placeholder="Email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group">
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" name="password" id="password" class="form-input" placeholder="Password" required>
                    <i class="fas fa-eye eye-toggle" id="eyeToggle" onclick="togglePassword()"></i>
                </div>
                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn">LOG IN</button>
            </form>

            <div class="register-row">
                Don't Have an account? <a href="{{ route('register') }}">REGISTER NOW</a>
            </div>
            <div class="back-home">
                <a href="#">Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeToggle');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
