<?php
// app/Http/Controllers/Api/ResourceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceDocumentResource;
use App\Models\ResourceDocument;
use App\Support\SectorConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sector = $request->query('sector');

        $resources = ResourceDocument::query()
            ->published()
            ->forSector($sector)
            ->latest()
            ->get();

        return response()->json([
            'resources' => ResourceDocumentResource::collection($resources),
            'sectors' => SectorConfig::sectors(),
        ]);
    }

    public function show(ResourceDocument $resource): JsonResponse
    {
        if (! $resource->is_published || ! Storage::disk('public')->exists($resource->file_path)) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(new ResourceDocumentResource($resource));
    }
}
