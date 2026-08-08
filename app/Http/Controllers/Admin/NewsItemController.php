<?php
// app/Http/Controllers/Admin/NewsItemController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsItemController extends Controller
{
    public function index(): View
    {
        return view('admin.news-items.index', [
            'items' => NewsItem::latest('published_at')->paginate(30),
        ]);
    }

    public function togglePublish(NewsItem $item): RedirectResponse
    {
        $item->update(['is_published' => ! $item->is_published]);

        return back()->with('status', 'News item updated.');
    }

    public function destroy(NewsItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('status', 'News item removed.');
    }
}
