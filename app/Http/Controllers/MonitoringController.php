<?php

namespace App\Http\Controllers;

use App\Models\LandSurvey;
use App\Models\SurveyLot;
use App\Models\SurveyAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    // ── Guard ─────────────────────────────────────────────────────
    private function requireMonitoring()
    {
        if (!Auth::check() || Auth::user()->role !== 'monitoring') {
            Auth::logout();
            abort(redirect()->route('monitoring.login')
                ->with('error', 'Monitoring access required.'));
        }
    }

    // ── Shared parcel data (uses real survey/lot rows) ─────────────
    private function getParcels(): array
    {
        $surveys = LandSurvey::with(['lots', 'attachments'])->orderBy('lsn')->get();

        $parcels = [];
        foreach ($surveys as $si => $survey) {
            // Status derived from attachment count + age
            $attCount  = $survey->attachments->count() + $survey->lots->flatMap->attachments->count();
            $ageMonths = $survey->created_at->diffInMonths(now());
            if ($attCount >= 2) {
                $status = 'validated';
            } elseif ($attCount === 1 || $ageMonths < 1) {
                $status = 'for_review';
            } else {
                $status = 'pending';
            }

            $totalArea = $survey->lots->sum('area');
            $areaHa    = round($totalArea / 10000, 2);

            $parcels[] = [
                'id'          => $survey->id,
                'ref'         => 'PARCEL-PGN-' . str_pad($si + 1, 3, '0', STR_PAD_LEFT),
                'lsn'         => $survey->lsn,
                'municipality'=> $survey->municipality ?: 'Unknown',
                'barangay'    => $this->extractBarangay($survey->notes),
                'area_ha'     => $areaHa,
                'lot_count'   => $survey->lot_count,
                'status'      => $status,
                'owner'       => $survey->original_owner,
                'attachments' => $attCount,
                'updated'     => $survey->updated_at,
                'created'     => $survey->created_at,
                'notes'       => $survey->notes,
            ];
        }
        return $parcels;
    }

    private function extractBarangay(?string $notes): string
    {
        if (!$notes) return 'Sample Barangay';
        preg_match('/Brgy[.:]\s*([^|]+)/i', $notes, $m);
        return isset($m[1]) ? trim($m[1]) : 'Sample Barangay';
    }

    // ── Login ─────────────────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'monitoring') {
            return redirect()->route('monitoring.overview');
        }
        return view('monitoring.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, false)) {
            $user = Auth::user();
            if ($user->role !== 'monitoring') {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'This account does not have monitoring access.'])
                    ->withInput($request->only('email'));
            }
            $request->session()->regenerate();
            return redirect()->route('monitoring.overview');
        }

        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('monitoring.login');
    }

    // ── GIS Overview ──────────────────────────────────────────────
    public function overview()
    {
        $this->requireMonitoring();

        $parcels    = $this->getParcels();
        $total      = count($parcels);
        $validated  = count(array_filter($parcels, fn($p) => $p['status'] === 'validated'));
        $forReview  = count(array_filter($parcels, fn($p) => $p['status'] === 'for_review'));
        $pending    = count(array_filter($parcels, fn($p) => $p['status'] === 'pending'));

        // Alerts = parcels needing review
        $alerts = array_filter($parcels, fn($p) => in_array($p['status'], ['for_review', 'pending']));

        // Municipality list for filter
        $municipalities = collect($parcels)->pluck('municipality')->unique()->sort()->values();

        return view('monitoring.gis-overview', compact(
            'parcels', 'total', 'validated', 'forReview', 'pending',
            'alerts', 'municipalities'
        ));
    }

    // ── Land Parcel Monitoring ────────────────────────────────────
    public function parcels()
    {
        $this->requireMonitoring();

        $parcels       = $this->getParcels();
        $total         = count($parcels);
        $validated     = count(array_filter($parcels, fn($p) => $p['status'] === 'validated'));
        $forReview     = count(array_filter($parcels, fn($p) => $p['status'] === 'for_review'));
        $pending       = count(array_filter($parcels, fn($p) => $p['status'] === 'pending'));
        $municipalities= collect($parcels)->pluck('municipality')->unique()->sort()->values();
        $barangays     = collect($parcels)->pluck('barangay')->unique()->sort()->values();

        return view('monitoring.gis-parcels', compact(
            'parcels', 'total', 'validated', 'forReview', 'pending',
            'municipalities', 'barangays'
        ));
    }

    // ── GIS Activity History ──────────────────────────────────────
    public function activity()
    {
        $this->requireMonitoring();

        $parcels = $this->getParcels();

        $activities = collect($parcels)->map(function ($p, $i) {
            $actions = [
                'validated'  => 'Parcel record validated',
                'for_review' => 'Boundary record flagged for review',
                'pending'    => 'Location reference pending verification',
            ];
            $units = ['GIS Records Unit', 'Field Monitoring Unit'];
            return [
                'ref'         => $p['ref'],
                'lsn'         => $p['lsn'],
                'municipality'=> $p['municipality'],
                'unit'        => $units[$i % 2],
                'action'      => $actions[$p['status']] ?? 'Record updated',
                'status'      => $p['status'],
                'date'        => $p['updated'],
            ];
        })->sortByDesc('date')->values();

        $gisEvents    = $activities->count();
        $validEvents  = $activities->where('status', 'validated')->count();
        $reviewEvents = $activities->where('status', 'for_review')->count();
        $locChecks    = $activities->where('status', 'pending')->count();

        return view('monitoring.gis-activity', compact(
            'activities', 'gisEvents', 'validEvents', 'reviewEvents', 'locChecks'
        ));
    }

    // ── GIS Reports (MPI + summaries) ────────────────────────────
    public function reports()
    {
        $this->requireMonitoring();

        $parcels   = $this->getParcels();
        $total     = count($parcels);
        $validated = count(array_filter($parcels, fn($p) => $p['status'] === 'validated'));
        $forReview = count(array_filter($parcels, fn($p) => $p['status'] === 'for_review'));
        $pending   = count(array_filter($parcels, fn($p) => $p['status'] === 'pending'));

        // Municipality breakdown
        $byMuni = collect($parcels)->groupBy('municipality')->map(fn($g) => [
            'municipality' => $g->first()['municipality'],
            'count'        => $g->count(),
            'validated'    => $g->where('status','validated')->count(),
        ])->values();

        // ── MPI: targets for GIS (parcels validated per month) ───
        $monthsTracked = max(1, \App\Models\LandSurvey::selectRaw(
            "COUNT(DISTINCT TO_CHAR(created_at,'YYYY-MM'))"
        )->value('count'));
        $gisStaff = \App\Models\User::whereIn('role',['admin','user'])->count();
        $mpiTarget = max($gisStaff * $monthsTracked, 1);
        $mpiRate   = $total > 0 ? round(($validated / $total) * 100, 1) : 0;

        // ── Monthly breakdown (last 12 months) ────────────────────
        $monthly = LandSurvey::selectRaw(
            "TO_CHAR(created_at,'YYYY-MM') as ym,
             TO_CHAR(created_at,'Mon YYYY') as period,
             DATE_TRUNC('month',created_at) as month_start,
             count(*) as surveys_added,
             sum(lot_count) as lots_added"
        )->groupByRaw("TO_CHAR(created_at,'YYYY-MM'), TO_CHAR(created_at,'Mon YYYY'), DATE_TRUNC('month',created_at)")
         ->orderByDesc('month_start')->take(12)->get();

        // ── Annual breakdown ──────────────────────────────────────
        $annualYears = LandSurvey::selectRaw("EXTRACT(YEAR FROM created_at)::int as yr, count(*) as surveys, sum(lot_count) as lots")
            ->groupByRaw("EXTRACT(YEAR FROM created_at)")
            ->orderByDesc('yr')->get();

        return view('monitoring.gis-reports', compact(
            'parcels', 'total', 'validated', 'forReview', 'pending', 'byMuni',
            'mpiTarget', 'mpiRate', 'gisStaff', 'monthsTracked',
            'monthly', 'annualYears'
        ));
    }
}
