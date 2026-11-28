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

    // ── Shared survey data — real records from DB ────────────────
    private function getSurveys(): \Illuminate\Support\Collection
    {
        return LandSurvey::with(['lots', 'attachments'])->orderBy('lsn')->get()->map(function ($survey, $si) {
            $attCount  = $survey->attachments->count()
                       + $survey->lots->flatMap(fn($l) => $l->attachments)->count();
            $ageMonths = $survey->created_at->diffInMonths(now());

            // Status: validated if has ≥2 attachments, for_review if 1 or recent, pending otherwise
            if ($attCount >= 2) {
                $status = 'validated';
            } elseif ($attCount === 1 || $ageMonths < 1) {
                $status = 'for_review';
            } else {
                $status = 'pending';
            }

            $totalAreaSqm = $survey->lots->sum('area');
            $areaHa       = round($totalAreaSqm / 10000, 4);

            // Extract barangay from notes field
            $barangay = '';
            if ($survey->notes) {
                preg_match('/Brgy[.:]\s*([^|]+)/i', $survey->notes, $m);
                $barangay = isset($m[1]) ? trim($m[1]) : '';
            }

            // Land types from lots
            $landTypes = $survey->lots->pluck('land_type')->filter()->unique()->values()->toArray();

            return (object)[
                'id'            => $survey->id,
                'lsn'           => $survey->lsn,
                'original_owner'=> $survey->original_owner,
                'municipality'  => $survey->municipality ?: 'Unknown',
                'date_registered'=> $survey->date_registered,
                'barangay'      => $barangay ?: 'N/A',
                'area_ha'       => $areaHa,
                'area_sqm'      => $totalAreaSqm,
                'lot_count'     => $survey->lots->count(),
                'lot_owners'    => $survey->lots->pluck('owner_name')->filter()->unique()->values(),
                'land_types'    => $landTypes,
                'status'        => $status,
                'attachments'   => $attCount,
                'notes'         => $survey->notes,
                'updated_at'    => $survey->updated_at,
                'created_at'    => $survey->created_at,
            ];
        });
    }

    // ── Overview ──────────────────────────────────────────────────
    public function overview()
    {
        $this->requireMonitoring();
        $surveys     = $this->getSurveys();
        $total       = $surveys->count();
        $validated   = $surveys->where('status', 'validated')->count();
        $forReview   = $surveys->where('status', 'for_review')->count();
        $pending     = $surveys->where('status', 'pending')->count();
        $totalAreaHa = $surveys->sum('area_ha');
        $municipalities = $surveys->pluck('municipality')->unique()->sort()->values();

        // Recent activity (last 10 updated)
        $recentActivity = $surveys->sortByDesc('updated_at')->take(10);

        // Status breakdown by municipality
        $byMuni = $surveys->groupBy('municipality')->map(fn($g) => [
            'municipality' => $g->first()->municipality,
            'total'        => $g->count(),
            'validated'    => $g->where('status','validated')->count(),
            'for_review'   => $g->where('status','for_review')->count(),
            'pending'      => $g->where('status','pending')->count(),
        ])->values()->sortByDesc('total');

        return view('monitoring.gis-overview', compact(
            'surveys','total','validated','forReview','pending','totalAreaHa',
            'municipalities','recentActivity','byMuni'
        ));
    }

    // ── Land Parcel Monitoring ────────────────────────────────────
    public function parcels()
    {
        $this->requireMonitoring();
        $surveys     = $this->getSurveys();
        $total       = $surveys->count();
        $validated   = $surveys->where('status','validated')->count();
        $forReview   = $surveys->where('status','for_review')->count();
        $pending     = $surveys->where('status','pending')->count();

        $municipalities = $surveys->pluck('municipality')->unique()->sort()->values();
        $barangays      = $surveys->pluck('barangay')->filter()->unique()->sort()->values();
        $landTypes      = $surveys->flatMap(fn($s) => $s->land_types)->unique()->sort()->values();

        return view('monitoring.gis-parcels', compact(
            'surveys','total','validated','forReview','pending',
            'municipalities','barangays','landTypes'
        ));
    }

    // ── GIS Activity History ──────────────────────────────────────
    public function activity()
    {
        $this->requireMonitoring();
        $surveys = $this->getSurveys()->sortByDesc('updated_at');

        $gisEvents    = $surveys->count();
        $validEvents  = $surveys->where('status','validated')->count();
        $reviewEvents = $surveys->where('status','for_review')->count();
        $locChecks    = $surveys->where('status','pending')->count();

        // Build activity items from real survey data
        $activities = $surveys->map(function ($s) {
            $action = match($s->status) {
                'validated'  => 'Survey record validated — LSN ' . $s->lsn,
                'for_review' => 'Survey flagged for attachment review — LSN ' . $s->lsn,
                default      => 'Survey record pending verification — LSN ' . $s->lsn,
            };
            return [
                'lsn'          => $s->lsn,
                'municipality' => $s->municipality,
                'owner'        => $s->original_owner,
                'action'       => $action,
                'status'       => $s->status,
                'attachments'  => $s->attachments,
                'lot_count'    => $s->lot_count,
                'area_ha'      => $s->area_ha,
                'date'         => $s->updated_at,
                'registered'   => $s->date_registered,
            ];
        })->values();

        return view('monitoring.gis-activity', compact(
            'activities','gisEvents','validEvents','reviewEvents','locChecks'
        ));
    }

    // ── GIS Reports ───────────────────────────────────────────────
    public function reports()
    {
        $this->requireMonitoring();
        $surveys   = $this->getSurveys();
        $total     = $surveys->count();
        $validated = $surveys->where('status','validated')->count();
        $forReview = $surveys->where('status','for_review')->count();
        $pending   = $surveys->where('status','pending')->count();
        $totalAreaHa = round($surveys->sum('area_ha'), 2);
        $totalLots   = $surveys->sum('lot_count');

        // Municipality breakdown
        $byMuni = $surveys->groupBy('municipality')->map(fn($g) => [
            'municipality' => $g->first()->municipality,
            'surveys'      => $g->count(),
            'lots'         => $g->sum('lot_count'),
            'area_ha'      => round($g->sum('area_ha'), 2),
            'validated'    => $g->where('status','validated')->count(),
            'for_review'   => $g->where('status','for_review')->count(),
            'pending'      => $g->where('status','pending')->count(),
        ])->sortByDesc('surveys')->values();

        // MPI
        $gisStaff    = max(\App\Models\User::whereIn('role',['admin','user'])->count(), 1);
        $mpiTarget   = $gisStaff * 10; // target: 10 validated surveys per staff
        $mpiRate     = $total > 0 ? round(($validated / $total) * 100, 1) : 0;

        // Monthly breakdown (last 12 months)
        $monthly = LandSurvey::selectRaw(
            "TO_CHAR(created_at,'YYYY-MM') as ym,
             TO_CHAR(created_at,'Mon YYYY') as period,
             DATE_TRUNC('month',created_at) as month_start,
             count(*) as surveys_added,
             sum(lot_count) as lots_added"
        )->groupByRaw("TO_CHAR(created_at,'YYYY-MM'), TO_CHAR(created_at,'Mon YYYY'), DATE_TRUNC('month',created_at)")
         ->orderByDesc('month_start')->take(12)->get();

        // Annual breakdown
        $annualYears = LandSurvey::selectRaw(
            "EXTRACT(YEAR FROM created_at)::int as yr,
             count(*) as surveys, sum(lot_count) as lots"
        )->groupByRaw("EXTRACT(YEAR FROM created_at)")
         ->orderByDesc('yr')->get();

        return view('monitoring.gis-reports', compact(
            'surveys','total','validated','forReview','pending',
            'totalAreaHa','totalLots','byMuni',
            'mpiTarget','mpiRate','gisStaff',
            'monthly','annualYears'
        ));
    }

    // -- Login -----------------------------------------------------
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
                return back()->withErrors(['email' => 'This account does not have monitoring access.'])->withInput($request->only('email'));
            }
            $request->session()->regenerate();
            return redirect()->route('monitoring.overview');
        }
        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('monitoring.login');
    }
    // -- GIS Analytics ---------------------------------------------
    public function analytics()
    {
        $this->requireMonitoring();

        $totalSurveys        = LandSurvey::count();
        $totalLots           = SurveyLot::count();
        $totalAreaSqm        = SurveyLot::sum('area');
        $totalAreaHa         = $totalAreaSqm / 10000;
        $totalOwners         = SurveyLot::whereNotNull('owner_name')->distinct('owner_name')->count('owner_name');
        $totalMunicipalities = LandSurvey::whereNotNull('municipality')->distinct('municipality')->count('municipality');

        // Top municipalities by survey count
        $byMunicipality = LandSurvey::selectRaw(
            "COALESCE(NULLIF(TRIM(municipality),''),'Unknown') as municipality, COUNT(*) as count"
        )->groupBy('municipality')->orderByDesc('count')->take(10)->get();

        // Municipality area � use correct FK: land_survey_id
        $muniArea = SurveyLot::selectRaw(
            "COALESCE(NULLIF(TRIM(land_surveys.municipality),''),'Unknown') as municipality,
             SUM(survey_lots.area) as total_area"
        )->join('land_surveys', 'land_surveys.id', '=', 'survey_lots.land_survey_id')
         ->groupBy('land_surveys.municipality')
         ->orderByDesc('total_area')->take(10)->get();

        $muniAreaMap    = $muniArea->keyBy('municipality');
        $byMunicipality = $byMunicipality->map(function ($row) use ($muniAreaMap) {
            $row->total_area = $muniAreaMap[$row->municipality]->total_area ?? 0;
            return $row;
        });

        // Municipality detailed breakdown: plots, owners, area, surveys
        $muniDetails = SurveyLot::selectRaw(
            "COALESCE(NULLIF(TRIM(land_surveys.municipality),''),'Unknown') as municipality,
             COUNT(survey_lots.id)                      as plot_count,
             COUNT(DISTINCT survey_lots.owner_name)     as owner_count,
             SUM(survey_lots.area)                      as total_area_sqm,
             COUNT(DISTINCT land_surveys.id)            as survey_count"
        )->join('land_surveys', 'land_surveys.id', '=', 'survey_lots.land_survey_id')
         ->groupBy('land_surveys.municipality')
         ->orderByDesc('plot_count')
         ->get();

        // Land type distribution
        $landTypes = SurveyLot::selectRaw(
            "COALESCE(NULLIF(TRIM(land_type),''),'agricultural') as land_type,
             COUNT(*) as count,
             SUM(area) as total_area"
        )->groupBy('land_type')->orderByDesc('count')->get();

        $typeConfig = [
            'agricultural' => ['label' => 'Agricultural',  'color' => '#2e7d32'],
            'residential'  => ['label' => 'Residential',   'color' => '#1565c0'],
            'commercial'   => ['label' => 'Commercial',    'color' => '#e65100'],
            'industrial'   => ['label' => 'Industrial',    'color' => '#6a1b9a'],
            'government'   => ['label' => 'Government',    'color' => '#00838f'],
            'other'        => ['label' => 'Other',         'color' => '#78909c'],
        ];

        // Top 20 owners
        $topOwners = SurveyLot::selectRaw(
            "owner_name, COUNT(*) as lot_count, SUM(area) as total_area"
        )->whereNotNull('owner_name')->where('owner_name', '<>', '')
         ->groupBy('owner_name')->orderByDesc('lot_count')->take(20)->get();

        // Monthly lots + surveys (last 12 months)
        $monthLabels = []; $lotCounts = []; $surveyCounts = [];
        $cumulMonths = []; $cumulAreas = []; $runningArea  = 0;
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthLabels[]  = $m->format('M Y');
            $cumulMonths[]  = $m->format('M Y');
            $lc = SurveyLot::whereMonth('created_at', $m->month)->whereYear('created_at', $m->year)->count();
            $sc = LandSurvey::whereMonth('created_at', $m->month)->whereYear('created_at', $m->year)->count();
            $ma = SurveyLot::whereMonth('created_at', $m->month)->whereYear('created_at', $m->year)->sum('area');
            $lotCounts[]    = $lc;
            $surveyCounts[] = $sc;
            $runningArea   += round($ma / 10000, 2);
            $cumulAreas[]   = $runningArea;
        }

        // Lot size distribution buckets
        $surveys        = LandSurvey::withCount('lots')->get();
        $lotSizeBuckets = [
            $surveys->where('lots_count', 1)->count(),
            $surveys->whereBetween('lots_count', [2, 5])->count(),
            $surveys->whereBetween('lots_count', [6, 10])->count(),
            $surveys->where('lots_count', '>=', 11)->count(),
        ];

        return view('analytics.index', compact(
            'totalSurveys', 'totalLots', 'totalAreaSqm', 'totalAreaHa',
            'totalOwners', 'totalMunicipalities',
            'byMunicipality', 'muniDetails', 'landTypes', 'typeConfig',
            'topOwners', 'monthLabels', 'lotCounts', 'surveyCounts',
            'cumulMonths', 'cumulAreas', 'lotSizeBuckets'
        ));
    }
}
