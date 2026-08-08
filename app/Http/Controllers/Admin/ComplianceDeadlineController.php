<?php
// app/Http/Controllers/Admin/ComplianceDeadlineController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceDeadline;
use App\Support\SectorConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceDeadlineController extends Controller
{
    private array $categories = ['goaml', 'licensing', 'screening', 'training', 'reporting', 'other'];
    private array $recurrences = ['one_off', 'monthly', 'quarterly', 'annual', 'ongoing'];

    public function index(): View
    {
        $deadlines = ComplianceDeadline::orderByRaw('next_due_date IS NULL, next_due_date asc')->orderBy('sort_order')->get();

        return view('admin.compliance-deadlines.index', [
            'deadlines'   => $deadlines,
            'sectors'     => SectorConfig::sectors(),
            'categories'  => $this->categories,
            'recurrences' => $this->recurrences,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        ComplianceDeadline::create($validated);

        return back()->with('status', 'Deadline added.');
    }

    public function update(Request $request, ComplianceDeadline $deadline): RedirectResponse
    {
        $validated = $this->validated($request);
        $deadline->update($validated);

        return back()->with('status', 'Deadline updated.');
    }

    public function destroy(ComplianceDeadline $deadline): RedirectResponse
    {
        $deadline->delete();

        return back()->with('status', 'Deadline removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'sector'         => ['nullable', 'string'],
            'category'       => ['required', 'in:' . implode(',', $this->categories)],
            'authority'      => ['nullable', 'string', 'max:150'],
            'recurrence'     => ['required', 'in:' . implode(',', $this->recurrences)],
            'next_due_date'  => ['nullable', 'date'],
            'source_url'     => ['nullable', 'url', 'max:255'],
            'sort_order'     => ['nullable', 'integer'],
        ]);
    }
}
