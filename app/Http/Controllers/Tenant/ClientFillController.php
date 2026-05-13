<?php
// app/Http/Controllers/Tenant/ClientFillController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\ClientFillToken;
use App\Models\Tenant;
use App\Support\SectorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientFillController extends Controller
{
    // ── Generate token and optionally email it ────────────────────────────
    public function generate(Request $request, string $slug)
    {
        $tenant = app('tenant');

        $request->validate([
            'client_type'  => 'required|string',
            'client_name'  => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
        ]);

        $token = ClientFillToken::generate(
            $tenant->id,
            $request->client_name,
            $request->client_email,
            $request->client_type
        );

        $link = url("/{$tenant->slug}/fill/{$token->token}");

        // Send email if address provided
        $emailSent = false;
        if ($request->client_email) {
            try {
                // Set short timeout to prevent blocking
                config(['mail.mailers.smtp.timeout' => 5]);
                Mail::send('emails.client_fill', [
                    'tenantName' => $tenant->name,
                    'clientName' => $request->client_name ?? 'Valued Client',
                    'link'       => $link,
                    'expiresAt'  => $token->expires_at->format('d M Y'),
                ], function ($m) use ($request, $tenant) {
                    $m->to($request->client_email, $request->client_name)
                      ->subject("KYC Form — {$tenant->name}");
                });
                $emailSent = true;
            } catch (\Exception $e) {
                // Email failed silently — link still generated
                \Log::warning("Client fill email failed: " . $e->getMessage());
                $emailSent = false;
            }
        }

        return back()->with([
            'fill_link'    => $link,
            'fill_token'   => $token->token,
            'email_sent'   => $emailSent ?? false,
        ]);
    }

    // ── Public form — no auth required ───────────────────────────────────
    public function show(string $slug, string $token)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $fillToken = ClientFillToken::where('token', $token)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        if ($fillToken->isExpired()) {
            return view('tenant.fill.expired', compact('tenant'));
        }

        if ($fillToken->isUsed()) {
            return view('tenant.fill.used', compact('tenant'));
        }

        $sector = SectorConfig::get($tenant->business_type ?? 'gold');

        return view('tenant.fill.form', compact('tenant', 'fillToken', 'sector'));
    }

    // ── Handle public form submission ─────────────────────────────────────
    public function submit(Request $request, string $slug, string $token)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $fillToken = ClientFillToken::where('token', $token)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        if (!$fillToken->isValid()) {
            return redirect("/{$slug}/fill/{$token}")->with('error', 'This link has expired or already been used.');
        }

        // Create client record as pending
        $client = BullionClient::create([
            'tenant_id'   => $tenant->id,
            'client_type' => $fillToken->client_type,
            'status'      => 'pending',
            'created_by'  => null,
            'company_name'             => $request->company_name,
            'trade_license_no'         => $request->trade_license_no,
            'trade_license_expiry'     => $request->trade_license_expiry ?: null,
            'trn_number'               => $request->trn_number,
            'legal_form'               => $request->legal_form,
            'ejari_number'             => $request->ejari_number,
            'country_of_incorporation' => $request->country_of_incorporation,
            'business_activity'        => $request->business_activity,
            'nature_of_business'       => $request->business_activity,
            'registered_address'       => $request->registered_address,
            'website'                  => $request->website,
            'full_name'                => $request->full_name,
            'nationality'              => $request->nationality,
            'dob'                      => $request->dob ?: null,
            'passport_number'          => $request->passport_number,
            'passport_expiry'          => $request->passport_expiry ?: null,
            'eid_number'               => $request->eid_number,
            'eid_expiry'               => $request->eid_expiry ?: null,
            'occupation'               => $request->occupation,
            'employer_name'            => $request->employer_name,
            'pep_status'               => $request->has('pep_status') ? 1 : 0,
            'email'                    => $request->email,
            'phone'                    => $request->phone,
            'source_of_funds'          => $request->source_of_funds ? json_encode($request->source_of_funds) : null,
            'source_of_wealth'         => $request->source_of_wealth ? json_encode($request->source_of_wealth) : null,
            'purpose_of_relationship'  => $request->purpose_of_relationship,
            'risk_rating'              => 'low',
            'cdd_type'                 => 'standard',
            'decl_pep'                 => $request->has('decl_pep') ? 1 : 0,
            'decl_source_of_funds'     => $request->has('decl_source_of_funds') ? 1 : 0,
            'decl_sanctions'           => $request->has('decl_sanctions') ? 1 : 0,
            'decl_ubo'                 => $request->has('decl_ubo') ? 1 : 0,
            'decl_supply_chain'        => $request->has('decl_supply_chain') ? 1 : 0,
            'decl_cahra'               => $request->has('decl_cahra') ? 1 : 0,
            'decl_property'            => $request->has('decl_property') ? 1 : 0,
        ]);

        // Save signatories
        foreach ($request->input('signatories', []) as $sig) {
            if (!empty($sig['full_name'])) {
                $client->signatories()->create($sig);
            }
        }

        // Save shareholders
        foreach ($request->input('shareholders', []) as $sh) {
            if (!empty($sh['name'])) {
                $client->shareholders()->create([
                    'shareholder_type'     => 'individual',
                    'name'                 => $sh['name'],
                    'nationality'          => $sh['nationality'] ?? null,
                    'ownership_percentage' => $sh['ownership_percentage'] ?? null,
                    'passport_number'      => $sh['passport_number'] ?? null,
                    'dob'                  => $sh['dob'] ?? null,
                    'is_ubo'               => !empty($sh['is_ubo']),
                ]);
            }
        }

        // Save uploaded documents (single)
        foreach ($request->file('documents', []) as $docType => $file) {
            if ($file && $file->isValid()) {
                $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');
                ClientDocument::create([
                    'bullion_client_id' => $client->id,
                    'tenant_id'         => $tenant->id,
                    'document_type'     => $docType,
                    'document_label'    => ucwords(str_replace('_', ' ', $docType)),
                    'file_path'         => $path,
                    'file_name'         => $file->getClientOriginalName(),
                    'file_size'         => $file->getSize(),
                ]);
            }
        }

        // Save uploaded documents (multiple)
        foreach ($request->file('documents_multi', []) as $docType => $files) {
            foreach ($files as $idx => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');
                    ClientDocument::create([
                        'bullion_client_id' => $client->id,
                        'tenant_id'         => $tenant->id,
                        'document_type'     => $docType,
                        'document_label'    => ucwords(str_replace('_', ' ', $docType)) . ' ' . ($idx + 1),
                        'file_path'         => $path,
                        'file_name'         => $file->getClientOriginalName(),
                        'file_size'         => $file->getSize(),
                    ]);
                }
            }
        }

        // Mark token as used
        $fillToken->update(['used_at' => now(), 'bullion_client_id' => $client->id]);

        return view('tenant.fill.thankyou', compact('tenant', 'client'));
    }

    // ── Pending approvals list ────────────────────────────────────────────
    public function pending(Request $request, string $slug)
    {
        $tenant  = app('tenant');
        $pending = BullionClient::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->whereNull('created_by') // self-filled clients have no creator
            ->latest()
            ->get();

        $tokens = ClientFillToken::where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('tenant.fill.pending', compact('tenant', 'pending', 'tokens'));
    }
}
