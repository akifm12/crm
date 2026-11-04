<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegulatoryUpdate;
use App\Models\RegulatoryWatchRun;
use Illuminate\Http\Request;

/**
 * Regulatory updates found by the daily check that runs on the administrator's PC.
 * The check only reports what changed on the official sources; nothing here edits a
 * manual. A person reviews each item, then asks for the manuals to be updated.
 */
class RegulatoryUpdateController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $q = RegulatoryUpdate::query()->latest('detected_at');
        $q = match ($status) {
            'all'   => $q,
            'open'  => $q->whereIn('status', ['new', 'reviewed']),
            default => $q->where('status', $status),
        };

        $run = RegulatoryWatchRun::latest('ran_at')->first();

        return view('admin.policy-manuals.updates', [
            'updates' => $q->paginate(30)->withQueryString(),
            'status'  => $status,
            'run'     => $run,
            'stale'   => ! $run || $run->ran_at->lt(now()->subHours(48)),
            'counts'  => [
                'new'  => RegulatoryUpdate::where('status', 'new')->count(),
                'open' => RegulatoryUpdate::whereIn('status', ['new', 'reviewed'])->count(),
            ],
        ]);
    }

    public function update(Request $request, RegulatoryUpdate $update)
    {
        $data = $request->validate([
            'status'        => 'required|in:new,reviewed,applied,dismissed',
            'reviewer_note' => 'nullable|string|max:2000',
        ]);

        $update->update([
            'status'        => $data['status'],
            'reviewer_note' => $data['reviewer_note'] ?? $update->reviewer_note,
            'reviewed_at'   => $data['status'] === 'new' ? null : now(),
        ]);

        return back()->with('success', 'Updated.');
    }

    /** Receives the daily results from the PC task (bearer token). */
    public function ingest(Request $request)
    {
        $expected = (string) config('services.policy_watch.token');
        $given    = (string) $request->bearerToken();
        abort_unless($expected !== '' && hash_equals($expected, $given), 401);

        $data = $request->validate([
            'ran_at'                    => 'required|date',
            'sources'                   => 'required|array|max:50',
            'sources.*.key'             => 'required|string|max:60',
            'sources.*.title'           => 'nullable|string|max:200',
            'sources.*.ok'              => 'required|boolean',
            'sources.*.note'            => 'nullable|string|max:500',
            'items'                     => 'nullable|array|max:200',
            'items.*.source_key'        => 'required|string|max:60',
            'items.*.fingerprint'       => 'required|string|max:64',
            'items.*.title'             => 'required|string|max:300',
            'items.*.url'               => 'nullable|string|max:1000',
            'items.*.change_type'       => 'required|string|max:30',
            'items.*.summary'           => 'nullable|string|max:5000',
            'items.*.details'           => 'nullable|array',
            'items.*.affected_sections' => 'nullable|string|max:500',
        ]);

        RegulatoryWatchRun::create([
            'ran_at'  => $data['ran_at'],
            'ok'      => collect($data['sources'])->every(fn ($s) => $s['ok']),
            'sources' => $data['sources'],
        ]);

        $created = 0;
        foreach ($data['items'] ?? [] as $item) {
            $row = RegulatoryUpdate::firstOrCreate(
                ['fingerprint' => $item['fingerprint']],
                $item + ['detected_at' => now()]
            );
            $created += $row->wasRecentlyCreated ? 1 : 0;
        }

        return response()->json(['ok' => true, 'created' => $created]);
    }
}
