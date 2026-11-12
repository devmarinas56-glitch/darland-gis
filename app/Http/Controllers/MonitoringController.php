<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    // ── Show the monitoring login form ────────────────────────────
    public function showLogin()
    {
        // Already logged in as monitoring user → go straight to map
        if (Auth::check() && Auth::user()->role === 'monitoring') {
            return redirect()->route('monitoring.map');
        }

        return view('monitoring.login');
    }

    // ── Handle monitoring login ───────────────────────────────────
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, false)) {
            $user = Auth::user();

            // Only allow users with role = 'monitoring'
            if ($user->role !== 'monitoring') {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'This account does not have monitoring access.'])
                    ->withInput($request->only('email'));
            }

            $request->session()->regenerate();
            return redirect()->route('monitoring.map');
        }

        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->withInput($request->only('email'));
    }

    // ── Monitoring map page ───────────────────────────────────────
    public function map()
    {
        // Must be logged in with monitoring role
        if (!Auth::check() || Auth::user()->role !== 'monitoring') {
            Auth::logout();
            return redirect()->route('monitoring.login')
                ->with('error', 'You must sign in with a monitoring account to access this page.');
        }

        return view('monitoring.map');
    }

    // ── Logout ───────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('monitoring.login');
    }
}
