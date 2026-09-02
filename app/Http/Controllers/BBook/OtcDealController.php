<?php
// app/Http/Controllers/BBook/OtcDealController.php

namespace App\Http\Controllers\BBook;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\InventoryItem;
use App\Models\OtcDeal;
use App\Services\Accounting\OtcDealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OtcDealController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $deals = OtcDeal::where('tenant_id', $tenant->id)
            ->orderByDesc('deal_date')
            ->orderByDesc('id')
            ->paginate(30);

        return view('bbook.deals.index', compact('tenant', 'deals'));
    }

    public function create()
    {
        $tenant = app('tenant');

        return view('bbook.deals.create', [
            'tenant'    => $tenant,
            'items'     => $this->itemsForJs($tenant),
            'clients'   => $this->clientsForJs($tenant),
        ]);
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $request->validate($this->lineRules());

        $deal = app(OtcDealService::class)->createDraft($tenant, array_merge(
            $request->only('bullion_client_id', 'deal_type', 'counterparty_name', 'deal_date', 'notes', 'lines', 'metal_rates'),
            ['created_by' => Auth::guard('b_book')->id()]
        ));

        return redirect()->route('bbook.deals.show', [$tenant->slug, $deal->id])
            ->with('success', 'Deal saved as draft.');
    }

    public function show(string $slug, OtcDeal $deal)
    {
        $tenant = app('tenant');
        abort_if($deal->tenant_id !== $tenant->id, 404);
        $deal->load(['lines.inventoryItem', 'payments', 'journalEntry.lines.account', 'client']);

        return view('bbook.deals.show', compact('tenant', 'deal'));
    }

    public function edit(string $slug, OtcDeal $deal)
    {
        $tenant = app('tenant');
        abort_if($deal->tenant_id !== $tenant->id, 404);
        abort_if($deal->status !== 'draft', 403, 'Only draft deals can be edited.');
        $deal->load('lines.inventoryItem');

        return view('bbook.deals.edit', [
            'tenant'  => $tenant,
            'deal'    => $deal,
            'items'   => $this->itemsForJs($tenant),
            'clients' => $this->clientsForJs($tenant),
        ]);
    }

    public function update(Request $request, string $slug, OtcDeal $deal)
    {
        $tenant = app('tenant');
        abort_if($deal->tenant_id !== $tenant->id, 404);
        $request->validate($this->lineRules());

        try {
            app(OtcDealService::class)->updateDraft($deal, $request->only(
                'bullion_client_id', 'counterparty_name', 'deal_date', 'notes', 'lines', 'metal_rates'
            ));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('bbook.deals.show', [$tenant->slug, $deal->id])
            ->with('success', 'Deal updated.');
    }

    public function post(string $slug, OtcDeal $deal)
    {
        $tenant = app('tenant');
        abort_if($deal->tenant_id !== $tenant->id, 404);

        try {
            app(OtcDealService::class)->post($deal);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Deal posted.');
    }

    public function void(Request $request, string $slug, OtcDeal $deal)
    {
        $tenant = app('tenant');
        abort_if($deal->tenant_id !== $tenant->id, 404);

        try {
            app(OtcDealService::class)->void($deal, $request->input('reason'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Deal voided.');
    }

    public function storePayment(Request $request, string $slug, OtcDeal $deal)
    {
        $tenant = app('tenant');
        abort_if($deal->tenant_id !== $tenant->id, 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'nullable|string',
            'reference'    => 'nullable|string|max:255',
        ]);

        try {
            app(OtcDealService::class)->recordPayment($deal, array_merge(
                $request->only('payment_date', 'amount', 'method', 'reference'),
                ['created_by' => Auth::guard('b_book')->id()]
            ));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded.');
    }

    private function lineRules(): array
    {
        return [
            'bullion_client_id'   => 'nullable|exists:bullion_clients,id',
            'deal_type'           => ['required', Rule::in(['buy', 'sell'])],
            'counterparty_name'   => 'required|string|max:255',
            'deal_date'           => 'required|date',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'metal_rates'                    => 'nullable|array',
            'metal_rates.*.usd_per_oz'       => 'nullable|numeric|min:0',
            'metal_rates.*.usd_aed_rate'     => 'nullable|numeric|min:0',
        ];
    }

    private function itemsForJs($tenant)
    {
        return InventoryItem::where('tenant_id', $tenant->id)->where('is_active', true)
            ->with('balance')
            ->get()
            ->map(fn ($item) => [
                'id'                   => $item->id,
                'name'                 => $item->name,
                'purity'               => $item->purity,
                'metal_type'           => $item->metal_type,
                'nominal_weight_grams' => $item->nominal_weight_grams ? (float) $item->nominal_weight_grams : null,
                'stock_grams'          => $item->balance ? (float) $item->balance->quantity_grams : 0,
            ]);
    }

    private function clientsForJs($tenant)
    {
        return BullionClient::where('tenant_id', $tenant->id)
            ->orderBy('company_name')->orderBy('full_name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->displayName()]);
    }
}
