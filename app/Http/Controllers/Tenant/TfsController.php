<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TfsSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * One record per TFS alert: the tenant's unique survey link, plus two pieces of evidence
 * they upload themselves — a screenshot of the completed survey and a screenshot of the
 * search of their client list showing no match. The portal never answers the survey.
 */
class TfsController extends Controller
{
    private const EVIDENCE = [
        'survey' => 'survey_evidence_path',
        'search' => 'search_evidence_path',
    ];

    public function index()
    {
        $tenant      = app('tenant');
        $submissions = TfsSubmission::where('tenant_id', $tenant->id)->latest()->paginate(20);

        return view('tenant.tfs.index', compact('tenant', 'submissions'));
    }

    public function create()
    {
        $tenant = app('tenant');
        return view('tenant.tfs.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'survey_url' => ['required', 'url', 'max:2000', 'starts_with:https://,http://'],
            'title'      => 'nullable|string|max:300',
        ]);

        $tenant = app('tenant');
        $sub = TfsSubmission::create([
            'tenant_id'  => $tenant->id,
            'created_by' => auth()->id(),
            'title'      => trim($data['title'] ?? '') ?: 'TFS survey — ' . now()->format('d M Y'),
            'survey_url' => $data['survey_url'],
            'status'     => 'draft',
        ]);

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $sub->id]);
    }

    public function show(string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        return view('tenant.tfs.show', compact('tenant', 'submission'));
    }

    public function upload(Request $request, string $slug, TfsSubmission $submission, string $type)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        abort_unless(isset(self::EVIDENCE[$type]), 404);

        $request->validate(['file' => 'required|file|mimes:png,jpg,jpeg,pdf|max:8192']);

        $column = self::EVIDENCE[$type];
        $file   = $request->file('file');
        $name   = $submission->id . '-' . $type . '-' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
        $file->storeAs('tfs-evidence', $name, 'public');

        if ($submission->$column) {
            Storage::disk('public')->delete($submission->$column);
        }
        $submission->$column = 'tfs-evidence/' . $name;
        $submission->status  = ($submission->survey_evidence_path && $submission->search_evidence_path) ? 'submitted' : 'partial';
        if ($submission->status === 'submitted' && !$submission->submitted_at) {
            $submission->submitted_at = now();
        }
        $submission->save();

        return back()->with('success', 'Saved.');
    }

    public function file(string $slug, TfsSubmission $submission, string $type)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        abort_unless(isset(self::EVIDENCE[$type]), 404);

        $rel = $submission->{self::EVIDENCE[$type]};
        abort_if(!$rel || !Storage::disk('public')->exists($rel), 404);

        return response()->file(Storage::disk('public')->path($rel));
    }
}
