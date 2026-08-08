<?php
// app/Http/Controllers/NewsController.php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $items = NewsItem::published()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.news.index', [
            'items'    => $items,
            'category' => $category,
            'categories' => ['aml' => 'AML', 'sanctions' => 'Sanctions', 'regulatory' => 'Regulatory', 'industry' => 'Industry', 'insight' => 'BA-Digest'],
        ]);
    }

    public function show(NewsItem $news): View
    {
        abort_unless($news->is_published, 404);

        $related = NewsItem::published()
            ->where('id', '!=', $news->id)
            ->where('category', $news->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.news.show', compact('news', 'related'));
    }
}
