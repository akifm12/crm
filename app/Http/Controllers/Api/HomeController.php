<?php
// app/Http/Controllers/Api/HomeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsItemResource;
use App\Models\NewsItem;
use App\Support\SectorConfig;
use App\Support\ServiceCatalog;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'services' => array_slice(ServiceCatalog::serviceTiles(), 0, 6),
            'tech_services' => ServiceCatalog::techServiceTiles(),
            'news' => NewsItemResource::collection(
                NewsItem::published()->latest('published_at')->take(3)->get()
            ),
            'sectors' => SectorConfig::sectors(),
        ]);
    }
}
