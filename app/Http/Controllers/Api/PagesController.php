<?php
// app/Http/Controllers/Api/PagesController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ServiceCatalog;
use Illuminate\Http\JsonResponse;

class PagesController extends Controller
{
    public function services(): JsonResponse
    {
        return response()->json([
            'services' => ServiceCatalog::serviceTiles(),
        ]);
    }

    public function technology(): JsonResponse
    {
        return response()->json([
            'tech_services' => ServiceCatalog::techServiceTiles(),
        ]);
    }
}
