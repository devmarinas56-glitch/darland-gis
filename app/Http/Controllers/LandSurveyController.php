<?php

namespace App\Http\Controllers;

use App\Models\LandSurvey;
use App\Models\SurveyLot;
use Illuminate\Http\Request;

class LandSurveyController extends Controller
{
    // ── Main page ────────────────────────────────────────────────
    public function index()
    {
        $surveys = LandSurvey::with('lots')->orderBy('lsn')->get();
        return view('land-records.index', compact('surveys'));
    }

    // ── Create a new LSN with lots ───────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lsn'            => ['required', 'string', 'unique:land_surveys,lsn'],
            'original_owner' => ['required', 'string'],
            'total_area'     => ['nullable', 'numeric', 'min:1'],
            'center_lat'     => ['nullable', 'numeric'],
            'center_lng'     => ['nullable', 'numeric'],
            'lot_count'      => ['required', 'integer', 'min:1', 'max:500'],
            'notes'          => ['nullable', 'string'],
            'mode'           => ['nullable', 'in:draw,grid'],
            'lots'           => ['required', 'array'],
            'lots.*.lot_number'  => ['required', 'integer', 'min:1'],
            'lots.*.owner_name'  => ['required', 'string'],
            'lots.*.polygon'     => ['nullable', 'array'],   // draw mode: [[lat,lng],...]
            'lots.*.area'        => ['nullable', 'numeric'],  // draw mode: pre-computed area
        ]);

        $mode = $validated['mode'] ?? 'grid';
        $isDrawMode = $mode === 'draw';

        // For draw mode, derive total_area from sum of lot areas
        if ($isDrawMode) {
            $totalArea = collect($validated['lots'])->sum(fn($l) => floatval($l['area'] ?? 0));
            $centerLat = $validated['center_lat'] ?? 0;
            $centerLng = $validated['center_lng'] ?? 0;
        } else {
            $totalArea = $validated['total_area'];
            $centerLat = $validated['center_lat'];
            $centerLng = $validated['center_lng'];
        }

        $survey = LandSurvey::create([
            'lsn'            => $validated['lsn'],
            'original_owner' => $validated['original_owner'],
            'total_area'     => round($totalArea, 2),
            'center_lat'     => $centerLat,
            'center_lng'     => $centerLng,
            'lot_count'      => $validated['lot_count'],
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        if ($isDrawMode) {
            // Use the polygons drawn by the user directly
            foreach ($validated['lots'] as $lotData) {
                SurveyLot::create([
                    'land_survey_id' => $survey->id,
                    'lot_number'     => $lotData['lot_number'],
                    'owner_name'     => $lotData['owner_name'],
                    'area'           => round(floatval($lotData['area'] ?? 0), 2),
                    'polygons'       => json_encode([$lotData['polygon']]),
                    'notes'          => $lotData['notes'] ?? null,
                ]);
            }
        } else {
            // Grid mode: auto-generate rectangles
            $areaPerLot = round($totalArea / $validated['lot_count'], 2);
            $polygons   = $this->generateRectangles(
                (float) $centerLat,
                (float) $centerLng,
                (float) $totalArea,
                (int)   $validated['lot_count']
            );

            foreach ($validated['lots'] as $i => $lotData) {
                SurveyLot::create([
                    'land_survey_id' => $survey->id,
                    'lot_number'     => $lotData['lot_number'],
                    'owner_name'     => $lotData['owner_name'],
                    'area'           => $areaPerLot,
                    'polygons'       => json_encode([$polygons[$i] ?? $polygons[0]]),
                ]);
            }
        }

        return response()->json(['success' => true, 'survey' => $survey->load('lots')]);
    }

    // ── Get a single LSN with all lots ───────────────────────────
    public function show(LandSurvey $survey)
    {
        return response()->json($survey->load('lots'));
    }

    // ── Update LSN info ──────────────────────────────────────────
    public function update(Request $request, LandSurvey $survey)
    {
        $validated = $request->validate([
            'original_owner' => ['required', 'string'],
            'notes'          => ['nullable', 'string'],
        ]);
        $survey->update($validated);
        return response()->json(['success' => true, 'survey' => $survey]);
    }

    // ── Delete LSN (cascades to lots) ────────────────────────────
    public function destroy(LandSurvey $survey)
    {
        $survey->delete();
        return response()->json(['success' => true]);
    }

    // ── Update a single lot ──────────────────────────────────────
    public function updateLot(Request $request, SurveyLot $lot)
    {
        $validated = $request->validate([
            'lot_number' => ['required', 'integer', 'min:1'],
            'owner_name' => ['required', 'string'],
            'notes'      => ['nullable', 'string'],
        ]);
        $lot->update($validated);
        return response()->json(['success' => true, 'lot' => $lot]);
    }

    // ── Add extra rectangle (bump) to a lot ─────────────────────
    public function addPolygon(Request $request, SurveyLot $lot)
    {
        $validated = $request->validate([
            'polygon' => ['required', 'array'], // [[lat,lng],[lat,lng],...]
        ]);

        $polygons = $lot->parsed_polygons;
        $polygons[] = $validated['polygon'];
        $lot->update(['polygons' => json_encode($polygons)]);

        return response()->json(['success' => true, 'lot' => $lot]);
    }

    // ── Delete a lot ─────────────────────────────────────────────
    public function destroyLot(SurveyLot $lot)
    {
        $lot->delete();
        return response()->json(['success' => true]);
    }

    // ── API: all surveys for map ─────────────────────────────────
    public function apiSurveys()
    {
        return response()->json(LandSurvey::with('lots')->get());
    }

    // ── Geometry: auto-generate rectangle grid ───────────────────
    // Returns an array of polygons, each polygon = [[lat,lng], [lat,lng], [lat,lng], [lat,lng]]
    private function generateRectangles(float $centerLat, float $centerLng, float $totalAreaSqm, int $count): array
    {
        // sqm → degrees (approx at Philippines latitude ~15°N)
        // 1 degree lat ≈ 111,000 m   →   1 sqm = (1/111000)² degrees²
        // 1 degree lng ≈ 111,000 * cos(lat) m
        $latPerMeter = 1 / 111000;
        $lngPerMeter = 1 / (111000 * cos(deg2rad($centerLat)));

        $areaPerLot   = $totalAreaSqm / $count;
        // Make each lot a square
        $sideMeter    = sqrt($areaPerLot);
        $halfLat      = ($sideMeter * $latPerMeter) / 2;
        $halfLng      = ($sideMeter * $lngPerMeter) / 2;

        // Arrange in a grid: cols = ceil(sqrt(count))
        $cols = (int) ceil(sqrt($count));
        $rows = (int) ceil($count / $cols);

        // Total grid size
        $gridWidthLng  = $cols * $halfLng * 2;
        $gridHeightLat = $rows * $halfLat * 2;

        // Top-left corner of grid
        $startLat = $centerLat + ($gridHeightLat / 2);
        $startLng = $centerLng - ($gridWidthLng / 2);

        $polygons = [];
        for ($i = 0; $i < $count; $i++) {
            $col = $i % $cols;
            $row = (int) floor($i / $cols);

            $topLat    = $startLat - ($row * $halfLat * 2);
            $bottomLat = $topLat - ($halfLat * 2);
            $leftLng   = $startLng + ($col * $halfLng * 2);
            $rightLng  = $leftLng + ($halfLng * 2);

            $polygons[] = [
                [$topLat,    $leftLng],
                [$topLat,    $rightLng],
                [$bottomLat, $rightLng],
                [$bottomLat, $leftLng],
            ];
        }

        return $polygons;
    }
}
