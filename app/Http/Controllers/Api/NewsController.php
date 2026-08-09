<?php
// app/Http/Controllers/Api/NewsController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsItemResource;
use App\Models\NewsItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $category = $request->query('category');

        $items = NewsItem::published()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return NewsItemResource::collection($items);
    }

    public function show(NewsItem $news): JsonResponse
    {
        if (! $news->is_published) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(new NewsItemResource($news));
    }
}
