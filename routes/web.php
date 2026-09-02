<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LandRecordsController;
use App\Http\Controllers\LandSurveyController;
use App\Http\Controllers\Admin\UserManagementController;

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
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout']);
// Registration disabled — admin creates accounts
// Route::get('/register', ...)

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        try {
            $surveys  = \App\Models\LandSurvey::with('lots')->get();
            $totalLots    = \App\Models\SurveyLot::count();
            $totalSurveys = $surveys->count();
            $totalArea    = $surveys->sum('total_area');

            // Recent lots (latest 10) with their survey info
            $recentLots = \App\Models\SurveyLot::with('survey')
                ->latest()
                ->take(10)
                ->get();

            // Lots per survey (use survey LSN as "location" proxy)
            // Group by first part of LSN to simulate municipality
            $byMunicipality = $surveys->groupBy(function($s) {
                // Extract city/municipality from the notes or use LSN prefix
                // Fall back to grouping by LSN series
                $lsn = strtoupper($s->lsn ?? '');
                if (str_contains($lsn, 'URD'))  return 'Urdaneta City';
                if (str_contains($lsn, 'DAG'))  return 'Dagupan City';
                if (str_contains($lsn, 'BIN'))  return 'Binalonan';
                if (str_contains($lsn, 'VIL'))  return 'Villasis';
                if (str_contains($lsn, 'LIN'))  return 'Lingayen';
                if (str_contains($lsn, 'ALM'))  return 'Alaminos';
                return 'Other';
            })->map(fn($g) => $g->sum('lot_count'));

            return view('dashboard.index', compact('surveys','totalLots','totalSurveys','totalArea','recentLots','byMunicipality'));
        } catch (\Exception $e) {
            return response('Dashboard error: ' . $e->getMessage(), 500);
        }
    })->name('dashboard');
    Route::get('/map-viewer', fn() => view('map.viewer'))->name('map.viewer');
    Route::get('/land-records', [LandSurveyController::class, 'index'])->name('land-records.index');
    Route::get('/add-record', fn() => view('add-record.index'))->name('add-record');

    // Land Survey API routes
    Route::get('/api/surveys', [LandSurveyController::class, 'apiSurveys'])->name('api.surveys');
    Route::post('/api/surveys', [LandSurveyController::class, 'store'])->name('api.surveys.store');
    Route::get('/api/surveys/{survey}', [LandSurveyController::class, 'show'])->name('api.surveys.show');
    Route::put('/api/surveys/{survey}', [LandSurveyController::class, 'update'])->name('api.surveys.update');
    Route::delete('/api/surveys/{survey}', [LandSurveyController::class, 'destroy'])->name('api.surveys.destroy');
    Route::put('/api/lots/{lot}', [LandSurveyController::class, 'updateLot'])->name('api.lots.update');
    Route::post('/api/lots/{lot}/polygon', [LandSurveyController::class, 'addPolygon'])->name('api.lots.polygon');
    Route::delete('/api/lots/{lot}', [LandSurveyController::class, 'destroyLot'])->name('api.lots.destroy');
    // Admin user management
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
});
