<?php
// app/Http/Controllers/Admin/CrmController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\CrmNote;
use App\Models\CrmTask;
use App\Models\CrmDocument;
use App\Models\CrmSla;
use App\Models\CrmQuotation;
use App\Models\SlaTemplate;
use App\Models\QuotationTemplate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CrmController extends Controller
{
    // ── Client list ────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = CrmClient::with(['assignee', 'tasks']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('license_number', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%");
            });
        }

        if ($request->filled('stage'))  $query->where('stage', $request->stage);
        if ($request->filled('status')) $query->where('status', $request->status);

        $clients  = $query->latest()->paginate(25)->withQueryString();
        $staff    = User::orderBy('name')->get();

        // Pipeline counts for summary bar
        $pipeline = CrmClient::selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        return view('admin.crm.index', compact('clients', 'staff', 'pipeline'));
    }

    // ── Create form ────────────────────────────────────────────────────────
    public function create()
    {
        $staff = User::orderBy('name')->get();
        return view('admin.crm.create', compact('staff'));
    }

    // ── Store new client ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'portal_type'  => 'required|in:bullion,real_estate,other,none',
        ]);

        $client = CrmClient::create(array_merge(
            $request->except(['shareholders', 'contacts', '_token']),
            ['created_by' => auth()->id()]
        ));

        // Shareholders
        foreach ($request->input('shareholders', []) as $sh) {
            if (!empty($sh['shareholder_name'])) {
                $client->shareholders()->create($sh);
            }
        }

        // Contact persons
        foreach ($request->input('contacts', []) as $i => $ct) {
            if (!empty($ct['name'])) {
                $client->contacts()->create(array_merge($ct, [
                    'is_primary' => $i === 0,
                ]));
            }
        }

        // Auto-create tenant portal if not 'none'
        if ($request->portal_type !== 'none') {
            $slug   = CrmClient::generateSlug($client->company_name);
            $tenant = Tenant::create([
                'name'          => $client->company_name,
                'slug'          => $slug,
                'contact_email' => $client->email,
                'is_active'     => true,
                'settings'      => ['type' => $request->portal_type],
            ]);
            $client->update(['tenant_id' => $tenant->id]);
        }

        return redirect()
            ->route('crm.show', $client->id)
            ->with('success', 'Client created. Tenant portal auto-generated.');
    }

    // ── Client profile ─────────────────────────────────────────────────────
    public function show(CrmClient $crm)
    {
        $crm->load(['shareholders', 'contacts', 'documents', 'notes.author', 'tasks.assignee', 'slas', 'quotations', 'tenant', 'assignee']);
        $staff        = User::orderBy('name')->get();
        $slaTemplates = SlaTemplate::where('is_active', true)->orderBy('name')->get();
        $qtTemplates  = QuotationTemplate::where('is_active', true)->orderBy('name')->get();
        return view('admin.crm.show', compact('crm', 'staff', 'slaTemplates', 'qtTemplates'));
    }

    // ── Add note ───────────────────────────────────────────────────────────
    public function addNote(Request $request, CrmClient $crm)
    {
        $request->validate(['body' => 'required', 'type' => 'required']);
        $crm->notes()->create(array_merge(
            $request->only(['type', 'subject', 'body', 'interaction_at']),
            ['created_by' => auth()->id()]
        ));
        return back()->with('success', 'Note added.');
    }

    // ── Add task ───────────────────────────────────────────────────────────
    public function addTask(Request $request, CrmClient $crm)
    {
        $request->validate(['task_description' => 'required']);
        $crm->tasks()->create(array_merge(
            $request->only(['task_description', 'due_date', 'priority', 'assigned_to']),
            ['created_by' => auth()->id()]
        ));
        return back()->with('success', 'Task added.');
    }

    // ── Complete task ──────────────────────────────────────────────────────
    public function completeTask(CrmTask $task)
    {
        $task->update(['status' => 'completed', 'completed_at' => now()]);
        return back()->with('success', 'Task marked complete.');
    }

    // ── Upload document ────────────────────────────────────────────────────
    public function uploadDocument(Request $request, CrmClient $crm)
    {
        $request->validate(['file' => 'required|file|max:10240', 'document_label' => 'required', 'document_type' => 'required']);
        $file = $request->file('file');
        $path = $file->store("crm/{$crm->id}", 'private');
        $crm->documents()->create([
            'document_type'  => $request->document_type,
            'document_label' => $request->document_label,
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
            'expiry_date'    => $request->expiry_date ?: null,
            'notes'          => $request->notes,
            'uploaded_by'    => auth()->id(),
        ]);
        return back()->with('success', 'Document uploaded.');
    }

    // ── Download document ──────────────────────────────────────────────────
    public function downloadDocument(CrmDocument $document)
    {
        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    // ── Delete document ────────────────────────────────────────────────────
    public function deleteDocument(CrmDocument $document)
    {
        Storage::disk('private')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }

    // ── Create SLA from template ───────────────────────────────────────────
    public function createSla(Request $request, CrmClient $crm)
    {
        $request->validate(['sla_template_id' => 'required|exists:sla_templates,id']);
        $template = SlaTemplate::findOrFail($request->sla_template_id);

        CrmSla::create([
            'crm_client_id'   => $crm->id,
            'sla_template_id' => $template->id,
            'sla_reference'   => CrmSla::generateReference(),
            'name'            => $template->name,
            'scope_of_work'   => $template->scope_of_work,
            'client_obligations' => $template->client_obligations,
            'deliverables'    => $template->deliverables,
            'fee'             => $request->fee ?? $template->default_fee,
            'fee_frequency'   => $template->fee_frequency,
            'payment_terms'   => $template->payment_terms,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'status'          => 'draft',
            'created_by'      => auth()->id(),
        ]);

        return back()->with('success', 'SLA created from template.');
    }

    // ── Update SLA status ──────────────────────────────────────────────────
    public function updateSlaStatus(Request $request, CrmSla $sla)
    {
        $sla->update(['status' => $request->status]);
        return back()->with('success', 'SLA status updated.');
    }

    // ── Upload signed SLA ──────────────────────────────────────────────────
    public function uploadSignedSla(Request $request, CrmSla $sla)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $path = $file->store("crm/slas", 'private');
        $sla->update([
            'signed_copy_path' => $path,
            'signed_date'      => $request->signed_date ?? now()->toDateString(),
            'status'           => 'signed',
        ]);
        return back()->with('success', 'Signed SLA uploaded.');
    }

    // ── Create quotation from template ─────────────────────────────────────
    public function createQuotation(Request $request, CrmClient $crm)
    {
        $request->validate(['quotation_template_id' => 'required|exists:quotation_templates,id']);
        $template = QuotationTemplate::findOrFail($request->quotation_template_id);

        $subtotal = collect($template->line_items)->sum(fn($i) => ($i['qty'] ?? 1) * ($i['unit_price'] ?? 0));
        $vat      = round($subtotal * 0.05, 2);

        CrmQuotation::create([
            'crm_client_id'          => $crm->id,
            'quotation_template_id'  => $template->id,
            'quotation_reference'    => CrmQuotation::generateReference(),
            'subject'                => $template->name,
            'line_items'             => $template->line_items,
            'subtotal'               => $subtotal,
            'vat_amount'             => $vat,
            'total_amount'           => $subtotal + $vat,
            'terms'                  => $template->terms,
            'issued_date'            => now()->toDateString(),
            'valid_until'            => now()->addDays($template->validity_days)->toDateString(),
            'status'                 => 'draft',
            'created_by'             => auth()->id(),
        ]);

        return back()->with('success', 'Quotation created.');
    }

    // ── Update pipeline stage ──────────────────────────────────────────────
    public function updateStage(Request $request, CrmClient $crm)
    {
        $crm->update(['stage' => $request->stage]);
        return back()->with('success', 'Stage updated.');
    }
}
