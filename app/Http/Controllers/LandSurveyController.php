<?php

namespace App\Http\Controllers;

use App\Models\LandSurvey;
use App\Models\SurveyLot;
use App\Models\SurveyAttachment;
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
            'municipality'   => ['nullable', 'string'],
            'date_registered'=> ['nullable', 'date'],
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

        // Server-side overlap check for draw mode lots
        if ($isDrawMode) {
            $existingLots = SurveyLot::with('survey')->get();
            foreach ($validated['lots'] as $lotData) {
                if (!empty($lotData['polygon'])) {
                    foreach ($existingLots as $existing) {
                        foreach ($existing->parsed_polygons as $existingPoly) {
                            if ($this->polygonsOverlap($lotData['polygon'], $existingPoly)) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "Overlap detected! Lot {$lotData['lot_number']} overlaps with existing Lot {$existing->lot_number} ({$existing->survey->lsn}). Cannot save overlapping plots.",
                                ], 422);
                            }
                        }
                    }
                }
            }
        }

        $survey = LandSurvey::create([
            'lsn'            => $validated['lsn'],
            'original_owner' => $validated['original_owner'],
            'municipality'   => $validated['municipality'] ?? null,
            'date_registered'=> $validated['date_registered'] ?? null,
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
            'polygon'    => ['nullable', 'array'],  // optional geometry update
            'area'       => ['nullable', 'numeric'],
        ]);

        $update = [
            'lot_number' => $validated['lot_number'],
            'owner_name' => $validated['owner_name'],
            'notes'      => $validated['notes'] ?? null,
        ];

        // Update geometry if provided
        if (!empty($validated['polygon'])) {
            $update['polygons'] = json_encode([$validated['polygon']]);
            if (!empty($validated['area'])) {
                $update['area'] = round(floatval($validated['area']), 2);
            }
        }

        $lot->update($update);

        // Recalculate survey total_area from all lots
        $survey = $lot->survey;
        $survey->total_area = round($survey->lots()->sum('area'), 2);
        $survey->lot_count  = $survey->lots()->count();
        $survey->save();

        return response()->json(['success' => true, 'lot' => $lot]);
    }

    // ── Add a new lot to existing survey ─────────────────────────
    public function addLotToSurvey(Request $request, LandSurvey $survey)
    {
        $validated = $request->validate([
            'owner_name' => ['required', 'string'],
            'polygon'    => ['required', 'array'],
            'area'       => ['required', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string'],
        ]);

        // Server-side overlap check
        $newPoly = $validated['polygon'];
        $lots = SurveyLot::with('survey')->get();
        foreach ($lots as $lot) {
            foreach ($lot->parsed_polygons as $existingPoly) {
                if ($this->polygonsOverlap($newPoly, $existingPoly)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Overlap detected! This lot overlaps with Lot {$lot->lot_number} ({$lot->survey->lsn}). Cannot add overlapping lot.",
                    ], 422);
                }
            }
        }

        $nextLotNumber = ($survey->lots()->max('lot_number') ?? 0) + 1;

        $lot = SurveyLot::create([
            'land_survey_id' => $survey->id,
            'lot_number'     => $nextLotNumber,
            'owner_name'     => $validated['owner_name'],
            'area'           => round(floatval($validated['area']), 2),
            'polygons'       => json_encode([$validated['polygon']]),
            'notes'          => $validated['notes'] ?? null,
        ]);

        // Recalculate survey totals
        $survey->total_area = round($survey->lots()->sum('area'), 2);
        $survey->lot_count  = $survey->lots()->count();
        $survey->save();

        return response()->json(['success' => true, 'lot' => $lot, 'survey' => $survey->load('lots')]);
    }

    // ── Divide a lot into two ─────────────────────────────────────
    public function divideLot(Request $request, SurveyLot $lot)
    {
        $validated = $request->validate([
            'polygon_a'   => ['required', 'array'],  // first half [[lat,lng],...]
            'polygon_b'   => ['required', 'array'],  // second half
            'area_a'      => ['required', 'numeric', 'min:0'],
            'area_b'      => ['required', 'numeric', 'min:0'],
            'owner_a'     => ['required', 'string'],
            'owner_b'     => ['required', 'string'],
        ]);

        $survey       = $lot->survey;
        $oldLotNumber = $lot->lot_number;

        // Server-side overlap check for both divided parts
        $existingLots = SurveyLot::with('survey')->where('id', '!=', $lot->id)->get();
        foreach (['polygon_a' => 'Part A', 'polygon_b' => 'Part B'] as $polyKey => $label) {
            foreach ($existingLots as $existing) {
                foreach ($existing->parsed_polygons as $existingPoly) {
                    if ($this->polygonsOverlap($validated[$polyKey], $existingPoly)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Overlap detected! {$label} overlaps with existing Lot {$existing->lot_number} ({$existing->survey->lsn}). Cannot divide into overlapping plots.",
                        ], 422);
                    }
                }
            }
        }

        // Update the original lot to be polygon A
        $lot->update([
            'polygons'   => json_encode([$validated['polygon_a']]),
            'area'       => round(floatval($validated['area_a']), 2),
            'owner_name' => $validated['owner_a'],
        ]);

        // Create the new lot for polygon B
        $nextNum = ($survey->lots()->max('lot_number') ?? 0) + 1;
        SurveyLot::create([
            'land_survey_id' => $survey->id,
            'lot_number'     => $nextNum,
            'owner_name'     => $validated['owner_b'],
            'area'           => round(floatval($validated['area_b']), 2),
            'polygons'       => json_encode([$validated['polygon_b']]),
            'notes'          => "Divided from Lot {$oldLotNumber}",
        ]);

        // Recalculate survey totals
        $survey->total_area = round($survey->lots()->sum('area'), 2);
        $survey->lot_count  = $survey->lots()->count();
        $survey->save();

        return response()->json(['success' => true, 'survey' => $survey->load('lots')]);
    }

    // ── Check for overlapping polygons ────────────────────────────
    public function checkOverlap(Request $request)
    {
        $validated = $request->validate([
            'polygon'     => ['required', 'array'],
            'exclude_lot' => ['nullable', 'integer'],
        ]);

        $newPoly    = $validated['polygon'];
        $excludeLot = $validated['exclude_lot'] ?? null;

        $overlapping = [];
        $lots = SurveyLot::with('survey')
            ->when($excludeLot, fn($q) => $q->where('id', '!=', $excludeLot))
            ->get();

        foreach ($lots as $lot) {
            foreach ($lot->parsed_polygons as $existingPoly) {
                if ($this->polygonsOverlap($newPoly, $existingPoly)) {
                    $overlapping[] = [
                        'lot_id'     => $lot->id,
                        'lot_number' => $lot->lot_number,
                        'lsn'        => $lot->survey->lsn ?? '',
                        'owner'      => $lot->owner_name,
                    ];
                    break;
                }
            }
        }

        return response()->json(['overlaps' => $overlapping]);
    }

    // ── Simple AABB overlap test (fast pre-check) ─────────────────
    private function polygonsOverlap(array $polyA, array $polyB): bool
    {
        if (empty($polyA) || empty($polyB)) return false;

        $latsA = array_column($polyA, 0);
        $lngsA = array_column($polyA, 1);
        $latsB = array_column($polyB, 0);
        $lngsB = array_column($polyB, 1);

        $minLatA = min($latsA); $maxLatA = max($latsA);
        $minLngA = min($lngsA); $maxLngA = max($lngsA);
        $minLatB = min($latsB); $maxLatB = max($latsB);
        $minLngB = min($lngsB); $maxLngB = max($lngsB);

        // AABB no-overlap check
        if ($maxLatA < $minLatB || $minLatA > $maxLatB) return false;
        if ($maxLngA < $minLngB || $minLngA > $maxLngB) return false;

        // Point-in-polygon check (centroid of A inside B or vice versa)
        $centLat = ($minLatA + $maxLatA) / 2;
        $centLng = ($minLngA + $maxLngA) / 2;
        if ($this->pointInPolygon($centLat, $centLng, $polyB)) return true;

        $centLat = ($minLatB + $maxLatB) / 2;
        $centLng = ($minLngB + $maxLngB) / 2;
        if ($this->pointInPolygon($centLat, $centLng, $polyA)) return true;

        return false;
    }

    private function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $n = count($polygon);
        $inside = false;
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0]; $yi = $polygon[$i][1];
            $xj = $polygon[$j][0]; $yj = $polygon[$j][1];
            if ((($yi > $lng) !== ($yj > $lng)) &&
                ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }
        return $inside;
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
        $surveys = LandSurvey::with(['lots', 'attachments'])->get();
        return response()->json($surveys->map(function ($survey) {
            $data = $survey->toArray();
            $data['attachment_count'] = $survey->attachments->count();
            $data['lots'] = $survey->lots->map(function ($lot) {
                $lotData = $lot->toArray();
                $lotData['attachment_count'] = $lot->attachments()->count();
                return $lotData;
            });
            return $data;
        }));
    }

    // ── Upload attachment (survey-level) ─────────────────────────
    public function uploadSurveyAttachment(Request $request, LandSurvey $survey)
    {
        return $this->storeAttachment($request, $survey->id, null);
    }

    // ── Upload attachment (lot-level) ────────────────────────────
    public function uploadLotAttachment(Request $request, SurveyLot $lot)
    {
        return $this->storeAttachment($request, $lot->land_survey_id, $lot->id);
    }

    private function storeAttachment(Request $request, int $surveyId, ?int $lotId)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480',    // 20 MB
                       'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp'],
        ]);

        $file     = $request->file('file');
        $fileData = base64_encode(file_get_contents($file->getRealPath()));

        $att = SurveyAttachment::create([
            'land_survey_id' => $surveyId,
            'survey_lot_id'  => $lotId,
            'original_name'  => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
            'file_data'      => $fileData,
            'uploaded_by'    => auth()->id(),
        ]);

        return response()->json([
            'success'    => true,
            'attachment' => $this->formatAttachment($att),
        ]);
    }

    // ── List attachments for a survey (survey-level only) ────────
    public function listSurveyAttachments(LandSurvey $survey)
    {
        $atts = $survey->attachments()->get();
        return response()->json(['attachments' => $atts->map(fn($a) => $this->formatAttachment($a))]);
    }

    // ── List attachments for a lot ───────────────────────────────
    public function listLotAttachments(SurveyLot $lot)
    {
        $atts = $lot->attachments()->get();
        return response()->json(['attachments' => $atts->map(fn($a) => $this->formatAttachment($a))]);
    }

    // ── Download an attachment ───────────────────────────────────
    public function downloadAttachment(SurveyAttachment $attachment)
    {
        $binary = $attachment->readBinary();
        return response($binary, 200, [
            'Content-Type'        => $attachment->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"',
            'Content-Length'      => strlen($binary),
        ]);
    }

    // ── Delete an attachment ─────────────────────────────────────
    public function deleteAttachment(SurveyAttachment $attachment)
    {
        $attachment->delete();
        return response()->json(['success' => true]);
    }

    // ── Format attachment for JSON response ──────────────────────
    private function formatAttachment(SurveyAttachment $att): array
    {
        return [
            'id'            => $att->id,
            'name'          => $att->original_name,
            'mime_type'     => $att->mime_type,
            'size_label'    => $att->file_size_label,
            'file_type'     => $att->file_type,
            'land_survey_id'=> $att->land_survey_id,
            'survey_lot_id' => $att->survey_lot_id,
            'created_at'    => $att->created_at?->format('M j, Y'),
        ];
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
