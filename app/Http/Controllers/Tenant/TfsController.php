<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TfsSubmission;
use App\Services\SentinelService;
use App\Services\TfsService;
use Illuminate\Http\Request;

class TfsController extends Controller
{
    public function __construct(
        private TfsService $tfs,
        private SentinelService $sentinel,
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
            'title'     => 'required|string|max:300',
            'alert_url' => 'nullable|url',
            'tfs_links' => 'required|string',
        ]);

        $tenant = app('tenant');

        // Parse TFS URLs — one per line, optional label prefix "Label: URL"
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
            'alert_url'  => $request->alert_url,
            'tfs_urls'   => $tfsUrls,
            'status'     => 'draft',
        ]);

        // If alert URL given, extract names and screen immediately
        if ($request->filled('alert_url')) {
            return redirect()->route('tenant.tfs.screen', [$tenant->slug, $sub->id]);
        }

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $sub->id])
            ->with('success', 'TFS submission created. Add alerted names to screen.');
    }

    public function screen(string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        // Extract names from UN alert URL
        $names = $this->tfs->extractNamesFromAlertUrl($submission->alert_url);

        if (empty($names)) {
            return redirect()->route('tenant.tfs.show', [$tenant->slug, $submission->id])
                ->with('error', 'Could not extract names from the alert URL. Please add them manually.');
        }

        // Screen each name
        $screeningResults = [];
        $hasMatch = false;

        foreach ($names as $name) {
            $res     = $this->sentinel->screenIndividual(['query' => $name, 'country' => 'UAE', 'nationality' => '', 'dob' => '']);
            $summary = SentinelService::summarise($res['data'] ?? []);
            if ($summary['status'] === 'match') $hasMatch = true;

            // Also screen as entity
            $resE     = $this->sentinel->screenEntity(['query' => $name, 'country' => 'UAE', 'country_of_issue' => 'UAE', 'license_number' => '']);
            $summaryE = SentinelService::summarise($resE['data'] ?? []);
            if ($summaryE['status'] === 'match') $hasMatch = true;

            $screeningResults[] = [
                'name'    => $name,
                'indiv'   => $summary,
                'entity'  => $summaryE,
                'matched' => $summary['status'] === 'match' || $summaryE['status'] === 'match',
            ];
        }

        $submission->update([
            'alerted_names'        => $names,
            'screening_results'    => $screeningResults,
            'recommended_response' => $hasMatch ? 'confirmed_match' : 'no_match',
            'status'               => 'screened',
        ]);

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $submission->id])
            ->with('success', count($names) . ' name(s) extracted and screened.');
    }

    public function show(string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        $submission->load('creator');

        return view('tenant.tfs.show', compact('tenant', 'submission'));
    }

    public function submitAll(Request $request, string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 403);

        $responseKey = $request->input('response', $submission->recommended_response ?? 'no_match');
        $tfsUrls     = $submission->tfs_urls ?? [];
        $anySuccess  = false;
        $anyFailed   = false;

        foreach ($tfsUrls as &$entry) {
            if ($entry['status'] === 'submitted') continue;

            $result = $this->tfs->submitTfsUrl($entry['url'], $responseKey);
            $entry['status']       = $result['success'] ? 'submitted' : 'failed';
            $entry['submitted_at'] = now()->toIso8601String();
            $entry['message']      = $result['message'];

            if ($result['success']) {
                $anySuccess = true;
                // Capture snapshot
                if (!empty($result['confirmation_html'])) {
                    $filename = 'tfs-' . $submission->id . '-' . uniqid() . '.pdf';
                    $path = $this->tfs->snapshotHtml($result['confirmation_html'], $filename);
                    $entry['snapshot_path'] = $path;
                }
            } else {
                $anyFailed = true;
            }
        }

        $overallStatus = !$anyFailed ? 'submitted' : ($anySuccess ? 'partial' : $submission->status);

        $submission->update([
            'tfs_urls'          => $tfsUrls,
            'selected_response' => $responseKey,
            'status'            => $overallStatus,
            'submitted_at'      => $anySuccess ? now() : $submission->submitted_at,
        ]);

        $msg = $anyFailed
            ? ($anySuccess ? 'Partially submitted — some URLs failed. Check details below.' : 'All submissions failed. Check details below.')
            : 'All TFS URLs submitted successfully.';

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $submission->id])
            ->with($anyFailed && !$anySuccess ? 'error' : 'success', $msg);
    }

    public function addNames(Request $request, string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 403);

        $names = collect(explode("\n", trim($request->input('names', ''))))
            ->map(fn($n) => trim($n))->filter()->values()->toArray();

        if (empty($names)) {
            return back()->with('error', 'No names provided.');
        }

        $screeningResults = [];
        $hasMatch = false;

        foreach ($names as $name) {
            $res     = $this->sentinel->screenIndividual(['query' => $name, 'country' => 'UAE', 'nationality' => '', 'dob' => '']);
            $summary = SentinelService::summarise($res['data'] ?? []);
            $resE     = $this->sentinel->screenEntity(['query' => $name, 'country' => 'UAE', 'country_of_issue' => 'UAE', 'license_number' => '']);
            $summaryE = SentinelService::summarise($resE['data'] ?? []);
            if ($summary['status'] === 'match' || $summaryE['status'] === 'match') $hasMatch = true;
            $screeningResults[] = [
                'name'   => $name, 'indiv' => $summary,
                'entity' => $summaryE, 'matched' => $summary['status'] === 'match' || $summaryE['status'] === 'match',
            ];
        }

        $submission->update([
            'alerted_names'        => $names,
            'screening_results'    => $screeningResults,
            'recommended_response' => $hasMatch ? 'confirmed_match' : 'no_match',
            'status'               => 'screened',
        ]);

        return back()->with('success', count($names) . ' name(s) screened.');
    }

    public function snapshot(string $slug, TfsSubmission $submission, int $urlIndex)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        $entry = $submission->tfs_urls[$urlIndex] ?? null;
        abort_if(!$entry || empty($entry['snapshot_path']), 404);

        $path = storage_path('app/public/' . $entry['snapshot_path']);
        abort_if(!file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }
}
