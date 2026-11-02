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

            // File Management roles — redirect to FMS app and log out from GIS
            $fmsRoles = ['ajdp', 'ltsp', 'arbdsp'];
            if (in_array(strtolower($user->role), $fmsRoles) ||
                ($user->role === 'admin' && str_contains($user->email, 'dar.gov.ph') &&
                 $user->email !== 'admin@dar.gov.ph')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('https://dar-file-management-system.onrender.com/login');
            }

            // GIS users stay here
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
