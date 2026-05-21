<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LicenseActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['regulator_id' => 'required|integer|exists:regulators,id']);

        $query = LicenseActivity::active()
            ->where('suggested_regulator_id', $request->regulator_id)
            ->with('suggestedRegulator');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where('name', 'like', $term);
        }

        return response()->json([
            'data' => $query->orderBy('name')->get()->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'description' => $a->description,
                'sector' => $a->sector,
                'suggested_regulator_id' => $a->suggested_regulator_id,
                'additional_regulator_ids' => $a->additional_regulator_ids,
                'suggested_regulator' => $a->suggestedRegulator,
            ]),
        ]);
    }
}
