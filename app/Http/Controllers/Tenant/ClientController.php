<?php
// app/Http/Controllers/Tenant/ClientController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    private function countries(): array
    {
        return \App\Models\Country::orderBy('country_name')->pluck('country_name', 'country_code')->toArray();
    }

    public function index(Request $request)
    {
        $tenant = app('tenant');
        $query  = BullionClient::where('tenant_id', $tenant->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('full_name',  'like', "%{$s}%")
                  ->orWhere('trade_license_no', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('risk'))   $query->where('risk_rating', $request->risk);
        if ($request->filled('type'))   $query->where('client_type', $request->type);

        $clients = $query->latest()->paginate(20)->withQueryString();

        $typeCounts = BullionClient::where('tenant_id', $tenant->id)
            ->selectRaw('client_type, count(*) as total')
            ->groupBy('client_type')
            ->pluck('total', 'client_type')
            ->toArray();
        $typeCounts[''] = array_sum($typeCounts);

        return view('tenant.clients.index', compact('tenant', 'clients', 'typeCounts'));
    }

    public function create()
    {
        $tenant    = app('tenant');
        $countries = $this->countries();
        return view('tenant.clients.create', compact('tenant', 'countries'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $client = BullionClient::create(array_merge(
            $request->except(['signatories', 'shareholders', 'ubos', 'documents', 'doc_labels', 'doc_expiry', 'doc_required', '_token']),
            [
                'tenant_id'   => $tenant->id,
                'created_by'  => auth()->id(),
                'cdd_type'    => $request->input('cdd_type') ?: 'standard',
                'risk_rating' => $request->input('risk_rating') ?: 'low',
                'status'      => $request->input('status') ?: 'pending',
            ]
        ));

        foreach ($request->input('signatories', []) as $sig) {
            if (!empty($sig['full_name'])) $client->signatories()->create($sig);
        }
        foreach ($request->input('shareholders', []) as $sh) {
            if (!empty($sh['name'])) $client->shareholders()->create($sh);
        }
        foreach ($request->input('ubos', []) as $ubo) {
            if (!empty($ubo['full_name'])) $client->ubos()->create($ubo);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $docType => $file) {
                if (!$file || !$file->isValid()) continue;
                $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');
                ClientDocument::create([
                    'bullion_client_id' => $client->id,
                    'tenant_id'         => $tenant->id,
                    'document_type'     => $docType,
                    'document_label'    => $request->input("doc_labels.{$docType}", $docType),
                    'file_path'         => $path,
                    'file_name'         => $file->getClientOriginalName(),
                    'mime_type'         => $file->getMimeType(),
                    'file_size'         => $file->getSize(),
                    'expiry_date'       => $request->input("doc_expiry.{$docType}") ?: null,
                    'is_required'       => (bool) $request->input("doc_required.{$docType}", false),
                    'uploaded_by'       => auth()->id(),
                ]);
            }
        }

        return redirect()
            ->route('tenant.clients.show', [$tenant->slug, $client->id])
            ->with('success', 'Client record created. Please run a screening check.');
    }

    public function show(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos', 'creator']);
        $documents = ClientDocument::where('bullion_client_id', $client->id)->orderBy('document_type')->get();
        return view('tenant.clients.show', compact('tenant', 'client', 'documents'));
    }

    public function edit(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);
        $countries = $this->countries();
        return view('tenant.clients.edit', compact('tenant', 'client', 'countries'));
    }

    public function update(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $data = $request->except(['_token', '_method', 'signatories', 'shareholders', 'ubos']);

        // Protect nullable fields from overwriting with empty string
        foreach (['country_of_incorporation', 'nationality', 'cdd_type', 'risk_rating', 'status'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                unset($data[$field]);
            }
        }

        $data['cdd_type']    = $request->input('cdd_type') ?: $client->cdd_type ?: 'standard';
        $data['risk_rating'] = $request->input('risk_rating') ?: $client->risk_rating ?: 'low';
        $data['status']      = $request->input('status') ?: $client->status ?: 'pending';

        $client->update($data);

        if ($request->has('signatories')) {
            $client->signatories()->delete();
            foreach ($request->input('signatories', []) as $sig) {
                if (!empty($sig['full_name'])) $client->signatories()->create($sig);
            }
        }

        if ($request->has('shareholders')) {
            $client->shareholders()->delete();
            foreach ($request->input('shareholders', []) as $sh) {
                if (!empty($sh['name'])) $client->shareholders()->create($sh);
            }
        }

        if ($request->has('ubos')) {
            $client->ubos()->delete();
            foreach ($request->input('ubos', []) as $ubo) {
                if (!empty($ubo['full_name'])) $client->ubos()->create($ubo);
            }
        }

        return redirect()
            ->route('tenant.clients.show', [$slug, $client->id])
            ->with('success', 'Client record updated successfully.');
    }

    public function uploadDocument(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $request->validate(['file' => 'required|file|max:10240', 'document_type' => 'required|string', 'document_label' => 'required|string', 'expiry_date' => 'nullable|date']);
        $file = $request->file('file');
        $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');
        ClientDocument::create([
            'bullion_client_id' => $client->id,
            'tenant_id'         => $tenant->id,
            'document_type'     => $request->document_type,
            'document_label'    => $request->document_label,
            'file_path'         => $path,
            'file_name'         => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'expiry_date'       => $request->expiry_date,
            'uploaded_by'       => auth()->id(),
        ]);
        return back()->with('success', 'Document uploaded.');
    }

    public function downloadDocument(string $slug, ClientDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);
        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function deleteDocument(string $slug, ClientDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);
        Storage::disk('local')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }

    public function updateRisk(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $request->validate(['risk_rating' => 'required|in:low,medium,high', 'cdd_type' => 'required|in:standard,enhanced', 'next_review_date' => 'nullable|date', 'risk_notes' => 'nullable|string']);
        $client->update($request->only(['risk_rating', 'cdd_type', 'next_review_date', 'risk_notes']));
        return back()->with('success', 'Risk rating updated.');
    }

    public function updateStatus(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $request->validate(['status' => 'required|in:active,pending,inactive,suspended']);
        $client->update(['status' => $request->status]);
        return back()->with('success', 'Status updated.');
    }

    public function updateDeclarations(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->update([
            'decl_pep'            => $request->boolean('decl_pep'),
            'decl_supply_chain'   => $request->boolean('decl_supply_chain'),
            'decl_cahra'          => $request->boolean('decl_cahra'),
            'decl_source_of_funds'=> $request->boolean('decl_source_of_funds'),
            'decl_sanctions'      => $request->boolean('decl_sanctions'),
            'decl_ubo'            => $request->boolean('decl_ubo'),
            'decl_master_signed'  => $request->boolean('decl_master_signed'),
        ]);
        return back()->with('success', 'Declarations updated.');
    }
}