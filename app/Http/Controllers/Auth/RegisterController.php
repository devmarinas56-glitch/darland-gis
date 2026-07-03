<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'password'      => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'          => $validated['first_name'] . ' ' . $validated['last_name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => 'user',
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
