<?php
// app/Http/Controllers/Tenant/TenantDocumentController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\TenantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantDocumentController extends Controller
{
    // ── Company documents ──────────────────────────────────────────────────

    public function companyIndex()
    {
        $tenant    = app('tenant');
        $documents = TenantDocument::where('tenant_id', $tenant->id)
            ->with('uploader')
            ->orderBy('document_type')
            ->get();

        $docTypes  = TenantDocument::companyDocTypes();
        $byType    = $documents->groupBy('document_type');

        $stats = [
            'total'   => $documents->count(),
            'expired' => $documents->filter(fn($d) => $d->isExpired())->count(),
            'expiring'=> $documents->filter(fn($d) => $d->isExpiringSoon())->count(),
        ];

        return view('tenant.docs.company', compact('tenant', 'documents', 'docTypes', 'byType', 'stats'));
    }

    public function companyUpload(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'document_type'  => 'required|string',
            'document_label' => 'required|string|max:255',
            'file'           => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,docx,xlsx',
            'expiry_date'    => 'nullable|date',
        ]);

        $file = $request->file('file');
        $path = $file->store("tenants/{$tenant->id}/company", 'local');

        TenantDocument::create([
            'tenant_id'      => $tenant->id,
            'document_type'  => $request->document_type,
            'document_label' => $request->document_label,
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
            'expiry_date'    => $request->expiry_date,
            'notes'          => $request->notes,
            'uploaded_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function companyDownload(string $slug, TenantDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function companyDelete(string $slug, TenantDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }

    // ── Client documents (consolidated view) ──────────────────────────────

    public function clientIndex(Request $request)
    {
        $tenant = app('tenant');

        $query = ClientDocument::where('client_documents.tenant_id', $tenant->id)
            ->join('bullion_clients', 'client_documents.bullion_client_id', '=', 'bullion_clients.id')
            ->select('client_documents.*', 'bullion_clients.company_name', 'bullion_clients.full_name', 'bullion_clients.client_type')
            ->with('uploader');

        if ($request->filled('client')) {
            $query->where('client_documents.bullion_client_id', $request->client);
        }
        if ($request->filled('type')) {
            $query->where('client_documents.document_type', $request->type);
        }
        if ($request->filled('status')) {
            if ($request->status === 'expired') {
                $query->where('expiry_date', '<', now());
            } elseif ($request->status === 'expiring') {
                $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
            }
        }

        $documents = $query->orderBy('client_documents.created_at', 'desc')->get();

        $clients = BullionClient::where('tenant_id', $tenant->id)
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'full_name', 'client_type']);

        $stats = [
            'total'    => ClientDocument::where('tenant_id', $tenant->id)->count(),
            'expired'  => ClientDocument::where('tenant_id', $tenant->id)->where('expiry_date', '<', now())->count(),
            'expiring' => ClientDocument::where('tenant_id', $tenant->id)
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
        ];

        return view('tenant.docs.clients', compact('tenant', 'documents', 'clients', 'stats'));
    }

    // Download client document
    public function clientDownload(string $slug, ClientDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    // Delete client document
    public function clientDelete(string $slug, ClientDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
