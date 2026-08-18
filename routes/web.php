<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandRecordsController;

Route::get('/', function () { return redirect('/login'); });

// Debug route for Render
Route::get('/debug', function () {
    return response()->json([
        'app_key_set'    => !empty(config('app.key')),
        'db_connection'  => config('database.default'),
        'session_driver' => config('session.driver'),
        'session_secure' => config('session.secure'),
        'app_env'        => config('app.env'),
        'app_url'        => config('app.url'),
        'php_version'    => PHP_VERSION,
        'is_https'       => request()->isSecure(),
        'forwarded_proto'=> request()->header('X-Forwarded-Proto'),
        'session_id'     => session()->getId(),
        'auth_check'     => auth()->check(),
        'session_data'   => session()->all(),
        'trusted_proxies'=> config('trustedproxy.proxies'),
    ]);
});

// Diagnostic: check if admin user exists in DB
Route::get('/debug-users', function () {
    $users = \App\Models\User::select('id','name','email','role','username',
        \Illuminate\Support\Facades\DB::raw('LEFT(password,20) as password_prefix'))
        ->get();

    // Also test password verification directly
    $admin = \App\Models\User::where('email','admin@darland.com')->first();
    $hashTest = null;
    if ($admin) {
        $rawHash = \Illuminate\Support\Facades\DB::table('users')
            ->where('email','admin@darland.com')
            ->value('password');
        $hashTest = [
            'raw_hash_prefix' => substr($rawHash, 0, 30),
            'hash_length'     => strlen($rawHash),
            'verify_admin123' => \Illuminate\Support\Facades\Hash::check('admin123', $rawHash),
            'verify_via_model'=> \Illuminate\Support\Facades\Hash::check('admin123', $admin->password),
        ];
    }

    return response()->json([
        'count'     => $users->count(),
        'users'     => $users,
        'hash_test' => $hashTest,
    ]);
});

// Diagnostic: test auth attempt directly
Route::get('/debug-auth', function () {
    $email = request('email', 'admin@darland.com');
    $password = request('password', 'admin123');

    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }

    $rawHash = \Illuminate\Support\Facades\DB::table('users')->where('email', $email)->value('password');

    $result = [
        'user_found'       => true,
        'email'            => $user->email,
        'role'             => $user->role,
        'hash_prefix'      => substr($rawHash, 0, 30),
        'hash_length'      => strlen($rawHash),
        'verify_direct'    => \Illuminate\Support\Facades\Hash::check($password, $rawHash),
        'verify_model'     => \Illuminate\Support\Facades\Hash::check($password, $user->password),
        'auth_attempt'     => \Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password]),
        'auth_check_after' => auth()->check(),
    ];

    \Illuminate\Support\Facades\Auth::logout();

    return response()->json($result);
});
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        try {
            return view('dashboard.index');
        } catch (\Exception $e) {
            return response('Dashboard error for user ' . auth()->user()->email . ' role=' . auth()->user()->role . ': ' . $e->getMessage(), 500);
        }
    })->name('dashboard');
    Route::get('/map-viewer', fn() => view('map.viewer'))->name('map.viewer');
    Route::get('/land-records', [LandRecordsController::class, 'index'])->name('land-records.index');
    Route::post('/land-records', [LandRecordsController::class, 'store'])->name('land-records.store');
    Route::put('/land-records/{landLot}', [LandRecordsController::class, 'update'])->name('land-records.update');
    Route::delete('/land-records/{landLot}', [LandRecordsController::class, 'destroy'])->name('land-records.destroy');
    Route::get('/api/lots', [LandRecordsController::class, 'apiLots'])->name('api.lots');
    Route::post('/api/check-overlap', [LandRecordsController::class, 'checkOverlap'])->name('api.check-overlap');
});
