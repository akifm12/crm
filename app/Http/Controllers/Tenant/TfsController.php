<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TfsSubmission;
use App\Services\TfsService;
use Illuminate\Http\Request;

/**
 * A per-tenant record of each TFS alert and how it was answered.
 *
 * The tenant searches their own client list for the designated names. This page keeps
 * the record of that (an attestation, who and when, the answer, evidence PDFs) and can
 * help submit the survey. Answering a TFS survey "match" opens an inquiry at the
 * Ministry, so nothing here ever submits on its own. Each link moves through stages
 * that a person triggers:
 *
 *   1. read      read-only: open the link, confirm it is open, list the exact answer options
 *   2. prepare   the person attests they searched their client list and picks the answer;
 *                the form is filled in a throwaway browser and a PDF is saved. Nothing is sent.
 *   3. finalize  the person looks at that PDF and explicitly confirms; only then is the
 *                answer submitted, with the exact text they approved.
 *
 * Any answer that isn't an unambiguous "no match" also needs a typed confirmation at both
 * the prepare and finalize steps. A submission made outside the portal can be recorded too.
 */
class TfsController extends Controller
{
    /** Typed to send anything other than a clear "no match". */
    private const MATCH_PHRASE = 'SEND MATCH';

    /** A prepared answer older than this has to be prepared again. */
    private const PREPARED_TTL_MINUTES = 60;

    public function __construct(private TfsService $tfs) {}

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
            'title'        => 'required|string|max:300',
            'tfs_links'    => 'required|string',
            'alert_url'    => 'nullable|url|max:500',
            'search_notes' => 'nullable|string|max:5000',
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
            'tenant_id'         => $tenant->id,
            'created_by'        => auth()->id(),
            'title'             => $request->title,
            'alert_url'         => $request->alert_url,
            'screening_results' => ['search_notes' => trim((string) $request->search_notes)],
            'tfs_urls'          => $tfsUrls,
            'status'            => 'draft',
        ]);

        // auto=1 only starts the read-only link check on the next page; nothing is submitted
        return redirect()->route('tenant.tfs.show', [$tenant->slug, $sub->id, 'auto' => 1]);
    }

    public function show(string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        $submission->load('creator');

        // Links that still need the read-only check
        $runIndices = collect($submission->tfs_urls ?? [])
            ->filter(fn ($e) => in_array($e['status'] ?? 'pending', ['pending', 'failed']))
            ->keys()->values()->all();

        return view('tenant.tfs.show', [
            'tenant'      => $tenant,
            'submission'  => $submission,
            'runIndices'  => $runIndices,
            'matchPhrase' => self::MATCH_PHRASE,
        ]);
    }

    // ── Stage 1: read the link (read-only) ────────────────────────────────

    /** Called by the page one link at a time. Reads; never answers. */
    public function process(Request $request, string $slug, TfsSubmission $submission, int $index)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        set_time_limit(120);

        $urls = $submission->tfs_urls ?? [];
        abort_unless(isset($urls[$index]), 404);

        $entry = $urls[$index];

        if (in_array($entry['status'] ?? '', ['submitted', 'prepared'], true)) {
            return $this->entryJson($submission, $entry);
        }

        $urls[$index] = $this->readEntry($entry);
        $this->save($submission, $urls);

        return $this->entryJson($submission, $urls[$index]);
    }

    private function readEntry(array $entry): array
    {
        $entry['checked_at'] = now()->toIso8601String();
        unset($entry['options'], $entry['page_excerpt'], $entry['attested_by'], $entry['attested_at'],
              $entry['prepared_answer'], $entry['prepared_at'], $entry['prepared_by'], $entry['prepared_snapshot_path']);

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

        $options = array_values(array_filter((array) ($res['options'] ?? []), 'is_string'));

        $entry['options']      = $options;
        $entry['page_excerpt'] = mb_substr((string) ($res['text'] ?? ''), 0, 1500);

        $readable = ($res['state'] ?? '') === 'form' && ($res['select_count'] ?? 0) === 1 && count($options) >= 2;
        if (!$readable) {
            $entry['status']  = 'failed';
            $entry['message'] = 'The survey page did not look as expected (no single answer list found), so it cannot be answered from here. Open the link in your browser.';
            return $entry;
        }

        $entry['status']  = 'ready';
        $entry['message'] = 'Link is open and the answer options were read. Nothing has been sent.';

        return $entry;
    }

    // ── Stage 2: attest, choose the answer, fill the form (nothing is sent) ─

    public function prepare(Request $request, string $slug, TfsSubmission $submission, int $index)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        set_time_limit(120);

        $request->validate(['answer' => 'required|string|max:300']);

        $urls  = $submission->tfs_urls ?? [];
        abort_unless(isset($urls[$index]), 404);
        $entry = $urls[$index];

        if (!in_array($entry['status'] ?? '', ['ready', 'prepared'], true)) {
            return $this->back($tenant, $submission, 'error', 'This link has to be read first (Step 1).');
        }

        $options = (array) ($entry['options'] ?? []);
        $answer  = trim($request->input('answer'));

        if (!TfsService::isOffered($answer, $options)) {
            return $this->back($tenant, $submission, 'error', 'That answer is not one of the options on the TFS form.');
        }

        if (!$request->boolean('attest')) {
            return $this->back($tenant, $submission, 'error', 'Tick the box to confirm you have searched your client database against the designated names.');
        }

        if (!TfsService::isSafeNoMatch($answer) && trim((string) $request->input('confirm_text')) !== self::MATCH_PHRASE) {
            return $this->back($tenant, $submission, 'error', 'This answer is not a clear "no match". Type ' . self::MATCH_PHRASE . ' to prepare it.');
        }

        $result = $this->tfs->prepare($entry['url'], $answer);

        if (!($result['success'] ?? false)) {
            $entry['message'] = $result['message'];
            $urls[$index] = $entry;
            $this->save($submission, $urls);
            return $this->back($tenant, $submission, 'error', $result['message']);
        }

        $entry['status']                 = 'prepared';
        $entry['attested_by']            = auth()->id();
        $entry['attested_at']            = now()->toIso8601String();
        $entry['prepared_answer']        = $result['selected_text'] ?? $answer;
        $entry['prepared_at']            = now()->toIso8601String();
        $entry['prepared_by']            = auth()->id();
        $entry['prepared_snapshot_path'] = $result['prepared_snapshot_path'] ?? null;
        $entry['message']                = 'Form filled with "' . $entry['prepared_answer'] . '". NOT submitted. Check it, then confirm below.';

        $urls[$index] = $entry;
        $this->save($submission, $urls);

        return $this->back($tenant, $submission, 'success', 'Form filled for your review. Nothing has been sent to the Ministry.');
    }

    // ── Stage 3: finalize (the only step that sends anything) ─────────────

    public function finalize(Request $request, string $slug, TfsSubmission $submission, int $index)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        set_time_limit(150);

        $urls  = $submission->tfs_urls ?? [];
        abort_unless(isset($urls[$index]), 404);
        $entry = $urls[$index];

        if (($entry['status'] ?? '') !== 'prepared' || empty($entry['prepared_answer'])) {
            return $this->back($tenant, $submission, 'error', 'Nothing is prepared for this link. Fill the form first (Step 2).');
        }

        $preparedAt = \Carbon\Carbon::parse($entry['prepared_at'] ?? now()->subYear());
        if ($preparedAt->lt(now()->subMinutes(self::PREPARED_TTL_MINUTES))) {
            return $this->back($tenant, $submission, 'error', 'This was prepared more than ' . self::PREPARED_TTL_MINUTES . ' minutes ago. Fill the form again so you are confirming what is on it now.');
        }

        if (!$request->boolean('confirm')) {
            return $this->back($tenant, $submission, 'error', 'Tick the confirmation box to submit.');
        }

        $answer = $entry['prepared_answer'];

        if (!TfsService::isSafeNoMatch($answer) && trim((string) $request->input('confirm_text')) !== self::MATCH_PHRASE) {
            return $this->back($tenant, $submission, 'error', 'This answer is not a clear "no match". Type ' . self::MATCH_PHRASE . ' to submit it.');
        }

        $result = $this->tfs->submitTfsUrl($entry['url'], $answer);

        $entry['submitted_at']      = now()->toIso8601String();
        $entry['submitted_by']      = auth()->id();
        $entry['message']           = $result['message'];
        $entry['snapshot_path']     = $result['snapshot_path'] ?? null;
        $entry['pre_snapshot_path'] = $result['pre_snapshot_path'] ?? null;

        if (($result['state'] ?? null) === 'used') {
            $entry['status'] = 'used';
            unset($entry['submitted_at'], $entry['submitted_by']);
        } elseif ($result['success']) {
            $entry['status'] = 'submitted';
        } else {
            // Not sent. Back to a state where it can be read and prepared again.
            $entry['status'] = 'failed';
            unset($entry['submitted_at'], $entry['submitted_by']);
        }

        $urls[$index] = $entry;
        $this->save($submission, $urls, $result['success'] ? TfsService::answerKey($answer) : null);

        return $this->back(
            $tenant,
            $submission,
            $result['success'] ? 'success' : 'error',
            $result['success'] ? 'Submitted "' . $answer . '". Evidence PDFs are saved below.' : 'NOT submitted: ' . $result['message']
        );
    }

    // ── Record a submission made outside the portal ───────────────────────

    public function record(Request $request, string $slug, TfsSubmission $submission, int $index)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        $request->validate([
            'answer'       => 'required|string|max:300',
            'submitted_on' => 'required|date|before_or_equal:today',
            'evidence'     => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',
            'note'         => 'nullable|string|max:1000',
        ]);

        $urls = $submission->tfs_urls ?? [];
        abort_unless(isset($urls[$index]), 404);
        $entry = $urls[$index];

        if (($entry['status'] ?? '') === 'submitted') {
            return $this->back($tenant, $submission, 'error', 'This link is already recorded as submitted.');
        }

        $path = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $name = 'tfs-manual-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('tfs-snapshots', $name, 'public');
            $path = 'tfs-snapshots/' . $name;
        }

        $entry['status']          = 'submitted';
        $entry['manual']          = true;
        $entry['prepared_answer'] = trim($request->answer);
        $entry['submitted_at']    = \Carbon\Carbon::parse($request->submitted_on)->setTimeFromTimeString(now()->format('H:i:s'))->toIso8601String();
        $entry['submitted_by']    = auth()->id();
        $entry['snapshot_path']   = $path;
        $entry['message']         = 'Recorded as submitted outside the portal.' . ($request->note ? ' ' . trim($request->note) : '');

        $urls[$index] = $entry;
        $this->save($submission, $urls, TfsService::answerKey($entry['prepared_answer']));

        return $this->back($tenant, $submission, 'success', 'Recorded.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function back($tenant, TfsSubmission $submission, string $flash, string $message)
    {
        return redirect()->route('tenant.tfs.show', [$tenant->slug, $submission->id])->with($flash, $message);
    }

    /** Persist the links and derive the overall status from them. */
    private function save(TfsSubmission $submission, array $urls, ?string $responseKey = null): void
    {
        $statuses  = collect($urls)->pluck('status');
        $open      = $statuses->reject(fn ($s) => in_array($s, ['submitted', 'used'], true))->count();
        $submitted = $statuses->filter(fn ($s) => $s === 'submitted')->count();

        $overall = match (true) {
            $open === 0 && $submitted > 0                                              => 'submitted',
            $submitted > 0                                                             => 'partial',
            $statuses->contains(fn ($s) => in_array($s, ['ready', 'prepared'], true))  => 'screened',
            default                                                                    => 'draft',
        };

        $submission->update([
            'tfs_urls'          => $urls,
            'status'            => $overall,
            'selected_response' => $responseKey ?? $submission->selected_response,
            'submitted_at'      => $submitted > 0 ? ($submission->submitted_at ?? now()) : $submission->submitted_at,
        ]);
    }

    private function entryJson(TfsSubmission $submission, array $entry)
    {
        return response()->json([
            'status'     => $entry['status'] ?? 'pending',
            'message'    => $entry['message'] ?? '',
            'submission' => $submission->fresh()->status,
        ]);
    }

    /** Evidence PDF/image: ?type=prepared (filled form), ?type=before (right before Submit), default = confirmation. */
    public function snapshot(Request $request, string $slug, TfsSubmission $submission, int $urlIndex)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        $entry = $submission->tfs_urls[$urlIndex] ?? null;
        $key   = match ($request->query('type')) {
            'prepared' => 'prepared_snapshot_path',
            'before'   => 'pre_snapshot_path',
            default    => 'snapshot_path',
        };
        abort_if(!$entry || empty($entry[$key]), 404);

        $path = storage_path('app/public/' . $entry[$key]);
        abort_if(!file_exists($path), 404);

        return response()->file($path);
    }
}
