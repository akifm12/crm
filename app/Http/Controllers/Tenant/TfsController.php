<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TfsSubmission;
use App\Services\TfsService;
use Illuminate\Http\Request;

class TfsController extends Controller
{
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

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $sub->id])
            ->with('success', 'TFS submission created. Select your response and submit.');
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

        $responseKey = $request->input('response', 'no_match');
        $tfsUrls     = $submission->tfs_urls ?? [];
        $anySuccess  = false;
        $anyFailed   = false;

        foreach ($tfsUrls as &$entry) {
            if ($entry['status'] === 'submitted') continue;

            $result = $this->tfs->submitTfsUrl($entry['url'], $responseKey);

            $entry['status']        = $result['success'] ? 'submitted' : 'failed';
            $entry['submitted_at']  = now()->toIso8601String();
            $entry['message']       = $result['message'];
            $entry['snapshot_path'] = $result['snapshot_path'] ?? null;

            if ($result['success']) {
                $anySuccess = true;
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
