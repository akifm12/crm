<?php
// app/Http/Controllers/ResourceController.php

namespace App\Http\Controllers;

use App\Models\ResourceDocument;
use App\Support\SectorConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(Request $request): View
    {
        $sector = $request->query('sector');

        $resources = ResourceDocument::query()
            ->published()
            ->forSector($sector)
            ->latest()
            ->get();

        return view('public.resources', [
            'resources' => $resources,
            'sectors'   => SectorConfig::sectors(),
            'sector'    => $sector,
        ]);
    }

    public function download(ResourceDocument $resource): RedirectResponse
    {
        abort_unless($resource->is_published && Storage::disk('public')->exists($resource->file_path), 404);

        return redirect(Storage::disk('public')->url($resource->file_path));
    }
}
