<?php

namespace App\Http\Controllers;

use App\Models\AccomplishmentReport;
use Illuminate\Http\Request;

class AccomplishmentReportController extends Controller
{
    // ── Submit Report page ───────────────────────────────────────
    public function submitForm()
    {
        return view('submit-report.index');
    }

    // ── My Reports page ──────────────────────────────────────────
    public function myReports(Request $request)
    {
        $selectedYear = (int) $request->get('year', date('Y'));
        $userId       = auth()->id();

        // Fetch all reports for this user in the selected year, keyed by month number
        $reports = AccomplishmentReport::forUser($userId)
            ->forYear($selectedYear)
            ->get();

        $reportsByMonth = $reports->keyBy(fn($r) => (int) $r->date_of_activity->format('n'));

        return view('my-reports.index', compact('selectedYear', 'reportsByMonth'));
    }

    // ── Monthly detail page (placeholder) ────────────────────────
    public function monthDetail(int $year, int $month)
    {
        $userId  = auth()->id();
        $reports = AccomplishmentReport::forUser($userId)
            ->whereYear('date_of_activity', $year)
            ->whereMonth('date_of_activity', $month)
            ->orderBy('date_of_activity')
            ->get();

        return response()->json($reports); // extend to a Blade view as needed
    }

    // ── API: store a new report ───────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program'          => ['required', 'string'],
            'date_of_activity' => ['required', 'date'],
            'description'      => ['required', 'string'],
            'documents.*'      => ['nullable', 'file', 'max:10240',
                                   'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        // Store uploaded files
        $paths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $paths[] = $file->store('reports/' . auth()->id(), 'public');
            }
        }

        $report = AccomplishmentReport::create([
            'user_id'          => auth()->id(),
            'program'          => $validated['program'],
            'date_of_activity' => $validated['date_of_activity'],
            'description'      => $validated['description'],
            'documents'        => $paths ?: null,
            'status'           => 'pending',
        ]);

        return response()->json(['success' => true, 'report' => $report]);
    }

    // ── Dashboard stat helpers (used by the dashboard route) ─────
    public static function dashboardStats(int $userId): array
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        return [
            'totalSubmitted' => AccomplishmentReport::forUser($userId)->forYear($year)->count(),
            'pendingReview'  => AccomplishmentReport::forUser($userId)->where('status', 'pending')->count(),
            'approved'       => AccomplishmentReport::forUser($userId)->forYear($year)
                                    ->whereMonth('date_of_activity', $month)
                                    ->where('status', 'approved')->count(),
            'returned'       => AccomplishmentReport::forUser($userId)->where('status', 'returned')->count(),
        ];
    }
}
