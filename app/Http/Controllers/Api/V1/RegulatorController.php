<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Regulator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegulatorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Regulator::active();

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('acronym', 'like', $term);
            });
        }

        return response()->json(['data' => $query->orderBy('name')->get()]);
    }

    public function show(Regulator $regulator): JsonResponse
    {
        return response()->json(['data' => $regulator]);
    }
}
