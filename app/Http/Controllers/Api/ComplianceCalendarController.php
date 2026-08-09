<?php
// app/Http/Controllers/Api/ComplianceCalendarController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplianceDeadlineResource;
use App\Models\ComplianceDeadline;
use App\Support\SectorConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceCalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sector = $request->query('sector');
        $user = $request->user('sanctum');

        $deadlines = ComplianceDeadline::query()
            ->forSector($sector)
            ->ordered()
            ->when($user, fn ($q) => $q->with(['userDeadlines' => fn ($q2) => $q2->where('public_user_id', $user->id)]))
            ->get();

        return response()->json([
            'deadlines' => ComplianceDeadlineResource::collection($deadlines),
            'sectors' => SectorConfig::sectors(),
        ]);
    }
}
