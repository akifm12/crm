<?php

// app/Services/Accounting/OtcDealService.php — B-Book's deal posting logic. Mirrors
// InvoicingService's shape (buy ~ purchase, sell ~ sale) but generic: no VAT, no
// making charges, no formal tax-invoice numbering. Posts to the separate B-Book
// ledger (OtcLedgerService) while moving the *same* shared inventory A-Book uses
// (InventoryService/InventoryItem) — the one deliberate connection point between
// the two books, since physical stock is meant to be one truth.

namespace App\Services\Accounting;

use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use App\Models\OtcDeal;
use App\Models\OtcDealLine;
use App\Models\OtcDealPayment;
use App\Models\OtcChartOfAccount;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OtcDealService
{
    public function __construct(
        private readonly OtcLedgerService $ledger,
        private readonly InventoryService $inventory,
    ) {}

    /**
     * $data: bullion_client_id, deal_type, counterparty_name, deal_date, currency_code,
     *        exchange_rate, metal_rates, notes, created_by,
     *        lines: [[inventory_item_id, metal_type, description, purity,
     *                 gross_weight_grams, pcs, unit_price], ...]
     */
    public function createDraft(Tenant $tenant, array $data): OtcDeal
    {
        return DB::transaction(function () use ($tenant, $data) {
            $computed = $this->computeLines($data['lines'] ?? []);
            $subtotal = round(collect($computed)->sum('line_subtotal'), 2);

            $deal = OtcDeal::create([
                'tenant_id'         => $tenant->id,
                'bullion_client_id' => $data['bullion_client_id'] ?? null,
                'deal_number'       => $this->nextNumber($tenant),
                'deal_type'         => $data['deal_type'],
                'counterparty_name' => $data['counterparty_name'],
                'deal_date'         => $data['deal_date'],
                'status'            => 'draft',
                'currency_code'     => $data['currency_code'] ?? 'AED',
                'exchange_rate'     => $data['exchange_rate'] ?? 1,
                'metal_rates'       => $data['metal_rates'] ?? null,
                'subtotal'          => $subtotal,
                'total'             => $subtotal,
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $data['created_by'] ?? null,
            ]);

            foreach ($computed as $i => $line) {
                OtcDealLine::create(array_merge($line, ['otc_deal_id' => $deal->id, 'line_order' => $i]));
            }

            return $deal->fresh('lines');
        });
    }

    public function updateDraft(OtcDeal $deal, array $data): OtcDeal
    {
        abort_if($deal->status !== 'draft', 422, 'Only draft deals can be edited.');

        return DB::transaction(function () use ($deal, $data) {
            $computed = $this->computeLines($data['lines'] ?? []);
            $subtotal = round(collect($computed)->sum('line_subtotal'), 2);

            $deal->update([
                'bullion_client_id' => $data['bullion_client_id'] ?? null,
                'counterparty_name' => $data['counterparty_name'],
                'deal_date'         => $data['deal_date'],
                'currency_code'     => $data['currency_code'] ?? 'AED',
                'exchange_rate'     => $data['exchange_rate'] ?? 1,
                'metal_rates'       => $data['metal_rates'] ?? null,
                'subtotal'          => $subtotal,
                'total'             => $subtotal,
                'notes'             => $data['notes'] ?? null,
            ]);

            $deal->lines()->delete();
            foreach ($computed as $i => $line) {
                OtcDealLine::create(array_merge($line, ['otc_deal_id' => $deal->id, 'line_order' => $i]));
            }

            return $deal->fresh('lines');
        });
    }

    public function post(OtcDeal $deal): OtcDeal
    {
        abort_if($deal->status !== 'draft', 422, 'Only draft deals can be posted.');

        return DB::transaction(function () use ($deal) {
            $tenant = $deal->tenant;
            $deal->loadMissing('lines.inventoryItem');

            $entry = match ($deal->deal_type) {
                'buy'  => $this->postBuy($tenant, $deal),
                'sell' => $this->postSell($tenant, $deal),
                default => throw new RuntimeException("OTC deal type '{$deal->deal_type}' is not yet supported."),
            };

            $deal->update([
                'status'               => 'posted',
                'otc_journal_entry_id' => $entry->id,
                'posted_at'            => now(),
            ]);

            return $deal->fresh(['lines', 'journalEntry']);
        });
    }

    private function postBuy(Tenant $tenant, OtcDeal $deal)
    {
        $apAccount = $this->requireAccount($tenant, 'ap');
        $inventoryByMetal = [];
        $journalLines = [];

        foreach ($deal->lines as $line) {
            $item = $line->inventoryItem ?: $this->findOrCreateMiscItem($tenant, $line->metal_type);

            $movement = $this->inventory->receiveStock($item, (float) $line->quantity_grams, (float) $line->unit_price, [
                'movement_type' => 'purchase_in',
                'source_type'   => 'otc_deal',
                'source_id'     => $deal->id,
                'moved_at'      => $deal->deal_date,
            ], $line->pcs);

            $metal = $item->metal_type;
            $inventoryByMetal[$metal] = ($inventoryByMetal[$metal] ?? 0) + (float) $movement->total_cost;
        }

        foreach ($inventoryByMetal as $metal => $amount) {
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = [
                'otc_chart_of_account_id' => $inventoryAccount->id,
                'debit' => round($amount, 2), 'credit' => 0,
                'description' => ucfirst($metal).' inventory received (OTC) — '.$deal->deal_number,
            ];
        }

        $journalLines[] = [
            'otc_chart_of_account_id' => $apAccount->id,
            'debit' => 0, 'credit' => (float) $deal->total,
            'description' => 'Payable to '.$deal->counterparty_name.' — '.$deal->deal_number,
        ];

        return $this->ledger->post($tenant, [
            'entry_date'        => $deal->deal_date->toDateString(),
            'reference'         => $deal->deal_number,
            'source_type'       => 'otc_deal',
            'source_id'         => $deal->id,
            'bullion_client_id' => $deal->bullion_client_id,
            'currency_code'     => $deal->currency_code,
            'exchange_rate'     => (float) $deal->exchange_rate,
            'created_by'        => $deal->created_by,
        ], $journalLines);
    }

    private function postSell(Tenant $tenant, OtcDeal $deal)
    {
        $arAccount = $this->requireAccount($tenant, 'ar');
        $revenueByMetal = [];
        $cogsByMetal = [];
        $journalLines = [];

        foreach ($deal->lines as $line) {
            abort_if(! $line->inventoryItem, 422, 'OTC sell lines must reference an existing inventory item.');

            $metal = $line->inventoryItem->metal_type;

            $movement = $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                'movement_type' => 'sale_out',
                'source_type'   => 'otc_deal',
                'source_id'     => $deal->id,
                'moved_at'      => $deal->deal_date,
            ], $line->pcs);

            $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) $movement->total_cost;
            $revenueByMetal[$metal] = ($revenueByMetal[$metal] ?? 0) + (float) $line->line_subtotal;
        }

        $journalLines[] = [
            'otc_chart_of_account_id' => $arAccount->id,
            'debit' => (float) $deal->total, 'credit' => 0,
            'description' => 'Receivable from '.$deal->counterparty_name.' — '.$deal->deal_number,
        ];

        foreach ($revenueByMetal as $metal => $amount) {
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = [
                'otc_chart_of_account_id' => $salesAccount->id,
                'debit' => 0, 'credit' => round($amount, 2),
                'description' => ucfirst($metal).' OTC revenue — '.$deal->deal_number,
            ];
        }

        foreach ($cogsByMetal as $metal => $cogsTotal) {
            if ($cogsTotal <= 0) continue;
            $cogsAccount = $this->requireAccount($tenant, "cogs_{$metal}");
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = [
                'otc_chart_of_account_id' => $cogsAccount->id,
                'debit' => round($cogsTotal, 2), 'credit' => 0,
                'description' => ucfirst($metal).' OTC COGS — '.$deal->deal_number,
            ];
            $journalLines[] = [
                'otc_chart_of_account_id' => $inventoryAccount->id,
                'debit' => 0, 'credit' => round($cogsTotal, 2),
                'description' => ucfirst($metal).' inventory issued (OTC) — '.$deal->deal_number,
            ];
        }

        return $this->ledger->post($tenant, [
            'entry_date'        => $deal->deal_date->toDateString(),
            'reference'         => $deal->deal_number,
            'source_type'       => 'otc_deal',
            'source_id'         => $deal->id,
            'bullion_client_id' => $deal->bullion_client_id,
            'currency_code'     => $deal->currency_code,
            'exchange_rate'     => (float) $deal->exchange_rate,
            'created_by'        => $deal->created_by,
        ], $journalLines);
    }

    public function recordPayment(OtcDeal $deal, array $data): OtcDealPayment
    {
        abort_if($deal->status !== 'posted', 422, 'Payments can only be recorded on posted deals.');

        return DB::transaction(function () use ($deal, $data) {
            $tenant = $deal->tenant;
            $amount = round((float) $data['amount'], 2);
            $direction = $deal->deal_type === 'sell' ? 'in' : 'out';

            $arOrAp = $this->requireAccount($tenant, $direction === 'in' ? 'ar' : 'ap');
            $cash = $this->requireAccount($tenant, $data['account'] ?? 'bank');

            $lines = $direction === 'in'
                ? [
                    ['otc_chart_of_account_id' => $cash->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Receipt — '.$deal->deal_number],
                    ['otc_chart_of_account_id' => $arOrAp->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Receipt — '.$deal->deal_number],
                ]
                : [
                    ['otc_chart_of_account_id' => $arOrAp->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Payment — '.$deal->deal_number],
                    ['otc_chart_of_account_id' => $cash->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Payment — '.$deal->deal_number],
                ];

            $entry = $this->ledger->post($tenant, [
                'entry_date'        => $data['payment_date'],
                'reference'         => $deal->deal_number,
                'source_type'       => 'otc_deal_payment',
                'source_id'         => $deal->id,
                'bullion_client_id' => $deal->bullion_client_id,
                'created_by'        => $data['created_by'] ?? null,
            ], $lines);

            $payment = OtcDealPayment::create([
                'otc_deal_id'          => $deal->id,
                'tenant_id'            => $tenant->id,
                'payment_date'         => $data['payment_date'],
                'amount'               => $amount,
                'direction'            => $direction,
                'method'               => $data['method'] ?? null,
                'reference'            => $data['reference'] ?? null,
                'otc_journal_entry_id' => $entry->id,
                'created_by'           => $data['created_by'] ?? null,
            ]);

            $deal->increment('amount_paid', $amount);

            return $payment;
        });
    }

    public function void(OtcDeal $deal, ?string $reason = null): OtcDeal
    {
        abort_if(! in_array($deal->status, ['draft', 'posted'], true), 422, 'This deal cannot be voided.');

        return DB::transaction(function () use ($deal, $reason) {
            if ($deal->status === 'posted' && $deal->journalEntry) {
                $this->ledger->void($deal->journalEntry, $reason);

                // Reverse the inventory movements this deal made.
                InventoryStockMovement::where('source_type', 'otc_deal')
                    ->where('source_id', $deal->id)
                    ->get()
                    ->each(fn ($m) => $this->inventory->reverseMovement($m));
            }

            $deal->update(['status' => 'void', 'voided_at' => now()]);

            return $deal->fresh();
        });
    }

    private function computeLines(array $lines): array
    {
        return collect($lines)->map(function (array $line) {
            $gross = (float) ($line['gross_weight_grams'] ?? 0);
            $purity = (float) ($line['purity'] ?? 0);
            $qty = $gross > 0 && $purity > 0 ? round($gross * ($purity / 1000), 3) : (float) ($line['quantity_grams'] ?? 0);
            $unitPrice = (float) ($line['unit_price'] ?? 0);

            return [
                'inventory_item_id'  => $line['inventory_item_id'] ?? null,
                'metal_type'         => $line['metal_type'] ?? null,
                'description'        => $line['description'] ?? '',
                'purity'             => $purity ?: null,
                'gross_weight_grams' => $gross ?: null,
                'quantity_grams'     => $qty,
                'pcs'                => isset($line['pcs']) && $line['pcs'] !== '' ? (int) $line['pcs'] : null,
                'unit_price'         => $unitPrice,
                'line_subtotal'      => round($qty * $unitPrice, 2),
            ];
        })->filter(fn ($l) => $l['line_subtotal'] > 0 || strlen($l['description']) > 0)
          ->values()->all();
    }

    private function findOrCreateMiscItem(Tenant $tenant, ?string $metalType): InventoryItem
    {
        abort_if(! $metalType, 422, 'OTC buy lines must either reference an inventory item or specify a metal type.');

        $sku = 'MISC-' . strtoupper($metalType);

        return InventoryItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => $sku],
            [
                'name'                 => 'Misc ' . ucfirst($metalType) . ' Purchase',
                'metal_type'           => $metalType,
                'purity'               => null,
                'nominal_weight_grams' => null,
                'form'                 => 'misc',
                'is_active'            => true,
            ]
        );
    }

    private function nextNumber(Tenant $tenant): string
    {
        $last = OtcDeal::where('tenant_id', $tenant->id)
            ->where('deal_number', 'like', 'OTC-%')
            ->orderByRaw('CAST(SUBSTRING(deal_number, 5) AS UNSIGNED) DESC')
            ->value('deal_number');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'OTC-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function requireAccount(Tenant $tenant, string $subtype): OtcChartOfAccount
    {
        $account = $this->ledger->accountFor($tenant, $subtype);

        abort_if(! $account, 500, "B-Book chart of accounts is missing a required '{$subtype}' account.");

        return $account;
    }
}
