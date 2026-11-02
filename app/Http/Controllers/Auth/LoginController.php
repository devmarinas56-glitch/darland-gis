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

        $system = $request->input('system', 'gis'); // 'gis' or 'fms'

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // File Management System roles — redirect to the FMS app
            $fmsRoles = ['ajdp', 'ltsp', 'arbdsp'];
            $fmsAdminEmail = 'admin@dar.gov.ph'; // FMS admin email

            // If user selected FMS tab or their role belongs to FMS
            if ($system === 'fms' || in_array(strtolower($user->role), $fmsRoles) ||
                $user->email === $fmsAdminEmail) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                // Redirect to FMS login with email pre-filled
                return redirect('https://dar-file-management-system.onrender.com/login')
                    ->with('info', 'Please log in to the File Management System.');
            }

            // GIS users
            return redirect('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials. Please check your email and password.'])
                     ->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
