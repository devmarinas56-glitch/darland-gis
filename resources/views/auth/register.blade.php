<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* Left Side - clean image only */
        .left-panel {
            flex: 1;
            overflow: hidden;
            min-height: 100vh;
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
            width: 440px;
            min-width: 440px;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 25px 35px;
            overflow-y: auto;
        }

        .signup-header { margin-bottom: 12px; }
        .signup-header h2 { font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
        .signup-header p { font-size: 12px; color: #666; }

        .register-card {
            background: white;
            border-radius: 16px;
            padding: 22px 25px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .form-group { margin-bottom: 10px; }

        .form-input {
            width: 100%;
            padding: 11px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            color: #333;
            background: white;
            transition: border-color 0.3s;
        }

        .form-input:focus { outline: none; border-color: #2d7a2d; }
        .form-input::placeholder { color: #aaa; }

        .terms-text {
            font-size: 11px;
            color: #888;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .terms-text a { color: #2d7a2d; text-decoration: none; }

        .signup-btn {
            width: 100%;
            padding: 12px;
            background: #555;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 12px;
        }

        .signup-btn:hover { background: #2d7a2d; }

        .back-login {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #2d7a2d;
            text-decoration: none;
            display: block;
        }

        .back-login:hover { text-decoration: underline; }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; min-width: unset; }
        }
    </style>
</head>
<body>
    <!-- Left Panel - image only -->
    <div class="left-panel">
        <img src="{{ asset('images/DarlandBG.png') }}" alt="Land GIS">
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
        <div class="signup-header">
            <h2>SIGN UP</h2>
            <p>Please complete the registration form</p>
        </div>

        <div class="register-card">
            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <input type="text" name="first_name" class="form-input" placeholder="First Name" value="{{ old('first_name') }}" required>
                </div>
                <div class="form-group">
                    <input type="text" name="last_name" class="form-input" placeholder="Last Name" value="{{ old('last_name') }}" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Email address" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="mobile_number" class="form-input" placeholder="Mobile Number" value="{{ old('mobile_number') }}">
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-input" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Password confirmation" required>
                </div>

                <p class="terms-text">
                    By clicking Sign Up, you agree to our <a href="#">Terms and Conditions</a>.
                    You may receive Email Notifications from us and can opt out any time.
                </p>

                <button type="submit" class="signup-btn">SIGN UP</button>
            </form>

            <a href="{{ route('login') }}" class="back-login">CLICK HERE TO GO BACK TO LOG IN</a>
        </div>
    </div>
</body>
</html>
