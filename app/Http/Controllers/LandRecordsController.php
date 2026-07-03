<?php

namespace App\Http\Controllers;

use App\Models\LandLot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandRecordsController extends Controller
{
    public function index(Request $request)
    {
        $query = LandLot::query();

        if ($request->barangay && $request->barangay !== 'all') {
            $query->where('barangay', $request->barangay);
        }

        if ($request->land_type && $request->land_type !== 'all') {
            $query->where('land_type', $request->land_type);
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('land_id', 'like', "%$search%")
                  ->orWhere('owner_name', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%");
            });
        }

        $lots = $query->orderBy('land_id')->get();
        $allLots = LandLot::all(); // for map
        $barangays = LandLot::distinct()->pluck('barangay');

        return view('land-records.index', compact('lots', 'allLots', 'barangays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'land_id'    => ['required', 'string', 'unique:land_lots'],
            'owner_name' => ['required', 'string'],
            'barangay'   => ['required', 'string'],
            'location'   => ['required', 'string'],
            'land_type'  => ['required', 'in:residential,commercial,agricultural,industrial'],
            'geojson'    => ['required', 'string'],
            'area'       => ['nullable', 'numeric'],
            'notes'      => ['nullable', 'string'],
        ]);

        $lot = LandLot::create([
            'land_id'    => $validated['land_id'],
            'owner_name' => $validated['owner_name'],
            'barangay'   => $validated['barangay'],
            'location'   => $validated['location'],
            'land_type'  => $validated['land_type'],
            'area'       => $validated['area'] ?? null,
            'geojson'    => $validated['geojson'],
            'notes'      => $validated['notes'] ?? null,
            'status'     => 'pending',
            'user_id'    => auth()->id(),
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'lot' => $lot]);
        }

        return redirect()->route('land-records.index')->with('success', 'Land plot submitted successfully!');
    }

    public function apiLots()
    {
        return response()->json(LandLot::all());
    }

    public function update(Request $request, $landId)
    {
        $landLot = LandLot::where('land_id', $landId)->firstOrFail();

        if (auth()->user()->role !== 'admin' && $landLot->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'owner_name' => ['required', 'string'],
            'barangay'   => ['required', 'string'],
            'location'   => ['required', 'string'],
            'land_type'  => ['required', 'in:residential,commercial,agricultural,industrial'],
            'notes'      => ['nullable', 'string'],
        ]);

        $landLot->update($validated);

        return response()->json(['success' => true, 'lot' => $landLot->fresh()]);
    }

    public function destroy($landId)
    {
        $landLot = LandLot::where('land_id', $landId)->firstOrFail();

        if (auth()->user()->role !== 'admin' && $landLot->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $landLot->delete();

        return response()->json(['success' => true]);
    }

    public function checkOverlap(Request $request)
    {
        $newCoords = json_decode($request->geojson, true);
        if (!$newCoords) {
            return response()->json(['overlaps' => false]);
        }

        // Get all lots with geojson
        $lots = LandLot::whereNotNull('geojson')->get();

        foreach ($lots as $lot) {
            $existing = json_decode($lot->geojson, true);
            if (!$existing) continue;

            // Simple bounding box overlap check
            if ($this->polygonsOverlap($newCoords[0], $existing[0])) {
                return response()->json([
                    'overlaps' => true,
                    'lot_id'   => $lot->land_id,
                    'owner'    => $lot->owner_name,
                ]);
            }
        }

        return response()->json(['overlaps' => false]);
    }

    private function polygonsOverlap(array $poly1, array $poly2): bool
    {
        // Check if bounding boxes intersect
        $bb1 = $this->getBoundingBox($poly1);
        $bb2 = $this->getBoundingBox($poly2);

        return !($bb1['maxLng'] < $bb2['minLng'] ||
                 $bb1['minLng'] > $bb2['maxLng'] ||
                 $bb1['maxLat'] < $bb2['minLat'] ||
                 $bb1['minLat'] > $bb2['maxLat']);
    }

    private function getBoundingBox(array $coords): array
    {
        $lngs = array_column($coords, 0);
        $lats = array_column($coords, 1);
        return [
            'minLng' => min($lngs), 'maxLng' => max($lngs),
            'minLat' => min($lats), 'maxLat' => max($lats),
        ];
    }
}
