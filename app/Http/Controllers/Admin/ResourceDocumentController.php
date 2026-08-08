<?php
// app/Http/Controllers/Admin/ResourceDocumentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResourceDocument;
use App\Support\SectorConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ResourceDocumentController extends Controller
{
    private array $categories = ['checklist', 'guide', 'template', 'reference'];

    public function index(): View
    {
        return view('admin.resource-documents.index', [
            'resources'  => ResourceDocument::latest()->get(),
            'sectors'    => SectorConfig::sectors(),
            'categories' => $this->categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sector'      => ['nullable', 'string'],
            'category'    => ['required', 'in:' . implode(',', $this->categories)],
            'file'        => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $path = $request->file('file')->store('resources', 'public');

        ResourceDocument::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sector'      => $validated['sector'] ?: null,
            'category'    => $validated['category'],
            'file_path'   => $path,
            'file_size'   => $request->file('file')->getSize(),
            'is_published'=> true,
        ]);

        return back()->with('status', 'Resource uploaded.');
    }

    public function togglePublish(ResourceDocument $resource): RedirectResponse
    {
        $resource->update(['is_published' => ! $resource->is_published]);

        return back()->with('status', 'Resource updated.');
    }

    public function destroy(ResourceDocument $resource): RedirectResponse
    {
        Storage::disk('public')->delete($resource->file_path);
        $resource->delete();

        return back()->with('status', 'Resource removed.');
    }
}
