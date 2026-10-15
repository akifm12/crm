<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TfsSubmission;
use App\Services\TfsClientScreener;
use App\Services\TfsService;
use Illuminate\Http\Request;

class TfsController extends Controller
{
    public function __construct(
        private TfsService $tfs,
        private TfsClientScreener $screener,
    ) {}

    public function index()
    {
        $tenant      = app('tenant');
        $submissions = TfsSubmission::where('tenant_id', $tenant->id)
            ->with('creator')
            ->latest()
            ->paginate(20);

        return view('tenant.tfs.index', compact('tenant', 'submissions'));
    }

    public function create()
    {
        $tenant = app('tenant');
        return view('tenant.tfs.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:300',
            'tfs_links' => 'required|string',
        ]);

        $tenant = app('tenant');

        $tfsUrls = collect(explode("\n", trim($request->tfs_links)))
            ->map(fn($line) => trim($line))
            ->filter()
            ->map(function ($line) {
                if (preg_match('/^(.+?)\s*:\s*(https?:\/\/.+)$/i', $line, $m)) {
                    return ['label' => trim($m[1]), 'url' => trim($m[2]), 'status' => 'pending'];
                }
                if (filter_var($line, FILTER_VALIDATE_URL)) {
                    return ['label' => null, 'url' => $line, 'status' => 'pending'];
                }
                return null;
            })
            ->filter()
            ->values()
            ->toArray();

        if (empty($tfsUrls)) {
            return back()->withInput()->with('error', 'No valid TFS URLs found. Paste one URL per line.');
        }

        $sub = TfsSubmission::create([
            'tenant_id'  => $tenant->id,
            'created_by' => auth()->id(),
            'title'      => $request->title,
            'tfs_urls'   => $tfsUrls,
            'status'     => 'draft',
        ]);

        // auto=1 makes the page start checking and submitting straight away
        return redirect()->route('tenant.tfs.show', [$tenant->slug, $sub->id, 'auto' => 1]);
    }

    public function show(string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        $submission->load('creator');

        // Links the automatic run still has to deal with
        $runIndices = collect($submission->tfs_urls ?? [])
            ->filter(fn ($e) => in_array($e['status'] ?? 'pending', ['pending', 'failed']))
            ->keys()->values()->all();

        return view('tenant.tfs.show', compact('tenant', 'submission', 'runIndices'));
    }

    /**
     * One step of the automatic run for a single link, called one at a time by the page
     * so no request has to outlast the web server's timeout.
     *
     *   phase=preview  open the link, read the alert, check it against the tenant's clients
     *   phase=submit   answer "No match" — only ever for a link whose check came back clear
     */
    public function process(Request $request, string $slug, TfsSubmission $submission, int $index)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        set_time_limit(150);

        $urls = $submission->tfs_urls ?? [];
        abort_unless(isset($urls[$index]), 404);

        $entry = $urls[$index];
        $phase = $request->input('phase', 'preview');

        if (($entry['status'] ?? '') === 'submitted') {
            return $this->entryJson($submission, $entry, 'Already submitted.');
        }

        if ($phase === 'preview') {
            $entry = $this->previewEntry($tenant->id, $entry);
        } elseif ($phase === 'submit') {
            // Server-side guard: an automatic answer is only ever sent for a clear check
            if (($entry['status'] ?? '') !== 'clear') {
                return response()->json(['status' => $entry['status'] ?? 'pending', 'message' => 'Not cleared for automatic submission.'], 422);
            }
            $entry = $this->submitEntry($entry, 'no_match');
        } else {
            abort(422);
        }

        $urls[$index] = $entry;
        $this->save($submission, $urls, $phase === 'submit' ? 'no_match' : null);

        return $this->entryJson($submission, $entry);
    }

    private function previewEntry(int $tenantId, array $entry): array
    {
        $entry['checked_at'] = now()->toIso8601String();
        unset($entry['candidates'], $entry['page_excerpt']);

        $res = $this->tfs->preview($entry['url']);

        if (!($res['success'] ?? false)) {
            $entry['status']  = 'failed';
            $entry['message'] = $res['message'] ?? 'Could not open the link.';
            return $entry;
        }

        if (($res['state'] ?? '') === 'used') {
            $entry['status']  = 'used';
            $entry['message'] = 'TFS shows this link as already completed. Nothing was submitted.';
            return $entry;
        }

        $text = (string) ($res['text'] ?? '');

        // Trust a "no match" only if the alert was actually read
        if (($res['state'] ?? '') !== 'form' || strlen($text) < 200) {
            $entry['status']       = 'review';
            $entry['message']      = 'The alert page could not be read reliably, so it was not checked or submitted. Review it manually.';
            $entry['candidates']   = [];
            $entry['page_excerpt'] = mb_substr($text, 0, 1500);
            return $entry;
        }

        $candidates = $this->screener->check($tenantId, $text);

        $entry['candidates']   = $candidates;
        $entry['page_excerpt'] = mb_substr($text, 0, 1500);
        $entry['text_length']  = strlen($text);

        if ($candidates) {
            $entry['status']  = 'review';
            $entry['message'] = count($candidates) . ' possible match' . (count($candidates) === 1 ? '' : 'es') . ' in your client base. Not submitted — review below.';
        } else {
            $entry['status']  = 'clear';
            $entry['message'] = 'No client, signatory, shareholder or UBO matches the alert.';
        }

        return $entry;
    }

    private function submitEntry(array $entry, string $responseKey): array
    {
        $result = $this->tfs->submitTfsUrl($entry['url'], $responseKey);

        if (($result['state'] ?? null) === 'used') {
            $entry['status'] = 'used';
        } else {
            $entry['status'] = $result['success'] ? 'submitted' : 'failed';
        }
        $entry['submitted_at']      = now()->toIso8601String();
        $entry['message']           = $result['message'];
        $entry['snapshot_path']     = $result['snapshot_path'] ?? null;
        $entry['pre_snapshot_path'] = $result['pre_snapshot_path'] ?? null;

        return $entry;
    }

    /** Manual submit, for links you reviewed yourself (including partial / confirmed match answers). */
    public function submitAll(Request $request, string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 403);
        set_time_limit(300);

        $responseKey = $request->input('response', 'no_match');
        $urls        = $submission->tfs_urls ?? [];
        $anySuccess  = false;
        $anyFailed   = false;

        foreach ($urls as $i => $entry) {
            if (in_array($entry['status'] ?? 'pending', ['submitted', 'used'], true)) continue;

            $entry = $this->submitEntry($entry, $responseKey);
            $urls[$i] = $entry;

            if ($entry['status'] === 'submitted') {
                $anySuccess = true;
            } else {
                $anyFailed = true;
            }
        }

        $this->save($submission, $urls, $responseKey, $anySuccess);

        $msg = $anyFailed
            ? ($anySuccess ? 'Partially submitted — some links failed. Check details below.' : 'Nothing was submitted. Check details below.')
            : 'All TFS links submitted successfully.';

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $submission->id])
            ->with($anyFailed && !$anySuccess ? 'error' : 'success', $msg);
    }

    /** Persist the links and derive the overall status from them. */
    private function save(TfsSubmission $submission, array $urls, ?string $response, bool $touchSubmittedAt = false): void
    {
        $statuses  = collect($urls)->pluck('status');
        $open      = $statuses->reject(fn ($s) => in_array($s, ['submitted', 'used'], true))->count();
        $submitted = $statuses->filter(fn ($s) => $s === 'submitted')->count();

        $overall = match (true) {
            $open === 0 && $submitted > 0     => 'submitted',
            $submitted > 0                    => 'partial',
            $statuses->contains('review')     => 'screened',
            default                           => $submission->status === 'screened' ? 'draft' : $submission->status,
        };

        $submission->update([
            'tfs_urls'          => $urls,
            'status'            => $overall,
            'selected_response' => $response ?? $submission->selected_response,
            'submitted_at'      => ($submitted > 0 && ($touchSubmittedAt || $overall === 'submitted' || !$submission->submitted_at)) ? now() : $submission->submitted_at,
        ]);
    }

    private function entryJson(TfsSubmission $submission, array $entry, ?string $override = null)
    {
        return response()->json([
            'status'      => $entry['status'] ?? 'pending',
            'message'     => $override ?? ($entry['message'] ?? ''),
            'candidates'  => $entry['candidates'] ?? [],
            'submission'  => $submission->fresh()->status,
        ]);
    }

    /** Evidence PDF: ?type=before (the alert with the chosen answer) or the default completion page. */
    public function snapshot(Request $request, string $slug, TfsSubmission $submission, int $urlIndex)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        $entry = $submission->tfs_urls[$urlIndex] ?? null;
        $key   = $request->query('type') === 'before' ? 'pre_snapshot_path' : 'snapshot_path';
        abort_if(!$entry || empty($entry[$key]), 404);

        $path = storage_path('app/public/' . $entry[$key]);
        abort_if(!file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }
}
