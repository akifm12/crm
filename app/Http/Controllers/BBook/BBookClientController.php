<?php
// app/Http/Controllers/BBook/BBookClientController.php — the consolidated "A+B" client
// view: every A-Book client (bullion_clients, reusing the existing, unmodified
// ReportingService::clientStatement()) PLUS every OTC-only counterparty who was
// never formally onboarded (no KYC needed for OTC dealings by design).

namespace App\Http\Controllers\BBook;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\OtcDeal;
use App\Services\Accounting\ReportingService;

class BBookClientController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $clients = BullionClient::where('tenant_id', $tenant->id)
            ->orderBy('company_name')->orderBy('full_name')
            ->get();

        $clientDealCounts = OtcDeal::where('tenant_id', $tenant->id)
            ->whereNotNull('bullion_client_id')
            ->selectRaw('bullion_client_id, count(*) as c')
            ->groupBy('bullion_client_id')
            ->pluck('c', 'bullion_client_id');

        // OTC-only counterparties: never linked to a real client record.
        $unlinkedCounterparties = OtcDeal::where('tenant_id', $tenant->id)
            ->whereNull('bullion_client_id')
            ->selectRaw('counterparty_name, count(*) as deal_count, sum(total) as volume')
            ->groupBy('counterparty_name')
            ->orderBy('counterparty_name')
            ->get();

        return view('bbook.clients.index', compact('tenant', 'clients', 'clientDealCounts', 'unlinkedCounterparties'));
    }

    public function show(string $slug, BullionClient $client, ReportingService $reporting)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        // A-Book side — the exact same, unmodified statement A-Book itself uses.
        $aBookStatement = $tenant->hasModule('bullion_accounting')
            ? $reporting->clientStatement($tenant, $client)
            : null;

        // B-Book side — this client's OTC dealings.
        $otcDeals = OtcDeal::where('tenant_id', $tenant->id)
            ->where('bullion_client_id', $client->id)
            ->orderByDesc('deal_date')
            ->get();

        $otcOutstanding = $otcDeals->where('status', 'posted')->sum(fn ($d) => $d->outstandingBalance());

        return view('bbook.clients.show', compact('tenant', 'client', 'aBookStatement', 'otcDeals', 'otcOutstanding'));
    }

    public function showCounterparty(string $slug, string $name)
    {
        $tenant = app('tenant');
        $name = urldecode($name);

        $otcDeals = OtcDeal::where('tenant_id', $tenant->id)
            ->whereNull('bullion_client_id')
            ->where('counterparty_name', $name)
            ->orderByDesc('deal_date')
            ->get();

        abort_if($otcDeals->isEmpty(), 404);

        $otcOutstanding = $otcDeals->where('status', 'posted')->sum(fn ($d) => $d->outstandingBalance());

        return view('bbook.clients.counterparty', compact('tenant', 'name', 'otcDeals', 'otcOutstanding'));
    }
}
