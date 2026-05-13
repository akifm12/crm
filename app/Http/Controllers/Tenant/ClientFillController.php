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

        $isIndividual = $fillToken->client_type === 'individual';

        // Create client record as pending — for tenant to review
        $client = BullionClient::create([
            'tenant_id'   => $tenant->id,
            'client_type' => $fillToken->client_type,
            'status'      => 'pending',
            'created_by'  => null,

            // Corporate fields
            'company_name'        => $request->company_name,
            'trade_license_no'    => $request->trade_license_no,
            'trade_license_expiry'=> $request->trade_license_expiry,
            'legal_form'          => $request->legal_form,
            'country_of_incorporation' => $request->country_of_incorporation,
            'business_activity'   => $request->business_activity,
            'nature_of_business'  => $request->nature_of_business,
            'registered_address'  => $request->registered_address,
            'email'               => $request->email,
            'phone'               => $request->phone,
            'website'             => $request->website,

            // Individual fields
            'full_name'       => $request->full_name,
            'nationality'     => $request->nationality,
            'dob'             => $request->dob,
            'passport_number' => $request->passport_number,
            'passport_expiry' => $request->passport_expiry,
            'eid_number'      => $request->eid_number,
            'eid_expiry'      => $request->eid_expiry,
            'occupation'      => $request->occupation,
            'employer_name'   => $request->employer_name,

            // AML
            'source_of_funds'          => $request->source_of_funds ? json_encode($request->source_of_funds) : null,
            'purpose_of_relationship'  => $request->purpose_of_relationship,
            'risk_rating'              => 'low',
            'cdd_type'                 => 'standard',
        ]);

        // Mark token as used
        $fillToken->update([
            'used_at'          => now(),
            'bullion_client_id'=> $client->id,
        ]);

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