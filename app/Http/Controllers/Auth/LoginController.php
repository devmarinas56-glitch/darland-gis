<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // FMS roles → SSO-redirect to File Management System
            $fmsRoles = ['fms', 'ajdp', 'ltsp', 'arbdsp', 'program_officer', 'planning_officer'];
            if (in_array(strtolower($user->role), $fmsRoles)) {
                // Build a short-lived signed token: base64(email|timestamp|hmac)
                $email     = $user->email;
                $ts        = time();
                $secret    = env('SSO_SECRET', 'dar-pangasinan-sso-2026-shared-secret');
                $signature = hash_hmac('sha256', $email . '|' . $ts, $secret);
                $token     = base64_encode($email . '|' . $ts . '|' . $signature);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('https://dar-file-management-system.onrender.com/sso?token=' . urlencode($token));
            }

            // Monitoring role → GIS monitoring portal
            if ($user->role === 'monitoring') {
                return redirect()->route('monitoring.overview');
            }

            // GIS users (admin, user) → GIS dashboard
            return redirect('/dashboard');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Invalid credentials. Please check your email and password.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
