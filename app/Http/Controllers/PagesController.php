<?php
// app/Http/Controllers/PagesController.php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Models\NewsItem;
use App\Support\SectorConfig;
use App\Support\ServiceCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PagesController extends Controller
{
    public function home(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('public.home', [
            'services'     => array_slice(ServiceCatalog::serviceTiles(), 0, 6),
            'techServices' => ServiceCatalog::techServiceTiles(),
            'news'         => NewsItem::published()->latest('published_at')->take(3)->get(),
            'sectors'      => SectorConfig::sectors(),
        ]);
    }

    public function services(): View
    {
        return view('public.services', [
            'services' => ServiceCatalog::serviceTiles(),
        ]);
    }

    public function technology(): View
    {
        return view('public.technology', [
            'techServices' => ServiceCatalog::techServiceTiles(),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function submitContact(ContactRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        return redirect()->route('contact')->with('status', 'Thanks — your message has been received. Our team will get back to you shortly.');
    }
}
