<?php

// app/Services/Accounting/OtcDealService.php — B-Book's deal posting logic. Full
// parity with InvoicingService (buy/sell/exchange, unfixed pricing + Fix Price,
// premium/discount, making charges) minus VAT — OTC deals are generic, not tax
// invoices. Posts to the separate B-Book ledger (OtcLedgerService) while moving
// the *same* shared inventory A-Book uses — the one deliberate connection point.

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
     * $data: bullion_client_id, deal_type, pricing_type, counterparty_name, deal_date,
     *        party_reference, currency_code, exchange_rate, metal_rates,
     *        premium_amount, discount_amount, notes, created_by,
     *        lines: [[line_type, inventory_item_id, metal_type, description, purity,
     *                 gross_weight_grams, pcs, unit_price, making_charge_rate], ...]
     */
    public function createDraft(Tenant $tenant, array $data): OtcDeal
    {
        return DB::transaction(function () use ($tenant, $data) {
            $computed = $this->computeLines($data['lines'] ?? [], $data['deal_type']);
            $subtotal = round(collect($computed)->sum('line_subtotal') + collect($computed)->sum('making_charge_amount'), 2);
            $premium  = (float) ($data['premium_amount'] ?? 0);
            $discount = (float) ($data['discount_amount'] ?? 0);
            $total    = round($subtotal + $premium - $discount, 2);

            $deal = OtcDeal::create([
                'tenant_id'         => $tenant->id,
                'bullion_client_id' => $data['bullion_client_id'] ?? null,
                'deal_number'       => $this->nextNumber($tenant),
                'deal_type'         => $data['deal_type'],
                'pricing_type'      => $data['pricing_type'] ?? 'fixed',
                'counterparty_name' => $data['counterparty_name'],
                'deal_date'         => $data['deal_date'],
                'party_reference'   => $data['party_reference'] ?? null,
                'status'            => 'draft',
                'currency_code'     => $data['currency_code'] ?? 'AED',
                'exchange_rate'     => $data['exchange_rate'] ?? 1,
                'metal_rates'       => $data['metal_rates'] ?? null,
                'subtotal'          => $subtotal,
                'premium_amount'    => $premium,
                'discount_amount'   => $discount,
                'total'             => $total,
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
            $computed = $this->computeLines($data['lines'] ?? [], $deal->deal_type);
            $subtotal = round(collect($computed)->sum('line_subtotal') + collect($computed)->sum('making_charge_amount'), 2);
            $premium  = (float) ($data['premium_amount'] ?? 0);
            $discount = (float) ($data['discount_amount'] ?? 0);
            $total    = round($subtotal + $premium - $discount, 2);

            $deal->update([
                'bullion_client_id' => $data['bullion_client_id'] ?? null,
                'pricing_type'      => $data['pricing_type'] ?? $deal->pricing_type,
                'counterparty_name' => $data['counterparty_name'],
                'deal_date'         => $data['deal_date'],
                'party_reference'   => $data['party_reference'] ?? null,
                'currency_code'     => $data['currency_code'] ?? 'AED',
                'exchange_rate'     => $data['exchange_rate'] ?? 1,
                'metal_rates'       => $data['metal_rates'] ?? null,
                'subtotal'          => $subtotal,
                'premium_amount'    => $premium,
                'discount_amount'   => $discount,
                'total'             => $total,
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

        if ($deal->pricing_type === 'unfixed' && $deal->deal_type !== 'sell') {
            abort(422, 'Unfixed pricing is only supported on sell deals.');
        }

        return DB::transaction(function () use ($deal) {
            $tenant = $deal->tenant;
            $deal->loadMissing('lines.inventoryItem');

            if ($deal->pricing_type === 'unfixed') {
                $this->moveInventoryForSell($deal);
                $deal->update(['status' => 'pending_fix']);

                return $deal->fresh('lines');
            }

            $entry = match ($deal->deal_type) {
                'buy'      => $this->postBuy($tenant, $deal),
                'sell'     => $this->postSell($tenant, $deal),
                'exchange' => $this->postExchange($tenant, $deal),
                default    => throw new RuntimeException("OTC deal type '{$deal->deal_type}' is not supported."),
            };

            $deal->update([
                'status'               => 'posted',
                'otc_journal_entry_id' => $entry->id,
                'posted_at'            => now(),
            ]);

            return $deal->fresh(['lines', 'journalEntry']);
        });
    }

    /**
     * Move inventory for an unfixed sell's metal_out lines without touching the
     * ledger — the piece shared between an unfixed post() and the later fixPrice().
     */
    private function moveInventoryForSell(OtcDeal $deal): void
    {
        foreach ($deal->lines as $line) {
            if ($line->line_type !== 'metal_out') continue;

            abort_if(! $line->inventoryItem, 422, 'Metal-out lines must reference an inventory item.');

            $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                'movement_type' => 'sale_out',
                'source_type'   => 'otc_deal',
                'source_id'     => $deal->id,
                'moved_at'      => $deal->deal_date,
            ], $line->pcs);
        }
    }

    /**
     * Fix the price on a deal that was posted "unfixed" — re-prices the given lines
     * (unit_price / making_charge_rate), recomputes totals, and posts the deferred
     * journal entry now that price is known. Mirrors InvoicingService::fixPrice().
     *
     * $lineUpdates: [line_id => ['unit_price' => .., 'making_charge_rate' => ..], ...]
     */
    public function fixPrice(OtcDeal $deal, array $lineUpdates, ?int $fixedBy = null): OtcDeal
    {
        abort_if($deal->status !== 'pending_fix', 422, 'Only deals awaiting a price fix can be fixed.');

        return DB::transaction(function () use ($deal, $lineUpdates, $fixedBy) {
            $tenant = $deal->tenant;
            $deal->loadMissing('lines.inventoryItem');

            foreach ($deal->lines as $line) {
                $update = $lineUpdates[$line->id] ?? null;
                if (! $update) continue;

                $computed = $this->computeLines([array_merge($line->only([
                    'line_type', 'inventory_item_id', 'metal_type', 'description',
                    'purity', 'gross_weight_grams', 'quantity_grams', 'pcs',
                ]), $update)], $deal->deal_type)[0];

                $line->update($computed);
            }

            $deal->refresh()->load('lines');

            $subtotal = round($deal->lines->sum('line_subtotal') + $deal->lines->sum('making_charge_amount'), 2);
            $total = round($subtotal + (float) $deal->premium_amount - (float) $deal->discount_amount, 2);
            $deal->update(['subtotal' => $subtotal, 'total' => $total]);

            $journalEntry = $this->postSell($tenant, $deal, moveInventory: false);

            // Deposits collected while pending_fix were booked to Customer Deposits (no AR
            // existed yet). Now that AR exists, reclass them across in one entry.
            $depositsCollected = round((float) $deal->payments()->sum('amount'), 2);
            if ($depositsCollected > 0) {
                $depositAccount = $this->requireAccount($tenant, 'customer_deposits');
                $arAccount = $this->requireAccount($tenant, 'ar');

                $this->ledger->post($tenant, [
                    'entry_date'        => now()->toDateString(),
                    'reference'         => $deal->deal_number,
                    'source_type'       => 'otc_deposit_reclass',
                    'source_id'         => $deal->id,
                    'bullion_client_id' => $deal->bullion_client_id,
                    'created_by'        => $fixedBy,
                ], [
                    ['otc_chart_of_account_id' => $depositAccount->id, 'debit' => $depositsCollected, 'credit' => 0, 'description' => 'Deposits applied — '.$deal->deal_number],
                    ['otc_chart_of_account_id' => $arAccount->id, 'debit' => 0, 'credit' => $depositsCollected, 'description' => 'Deposits applied — '.$deal->deal_number],
                ]);
            }

            $deal->update([
                'status'               => 'posted',
                'otc_journal_entry_id' => $journalEntry->id,
                'posted_at'            => now(),
                'fixed_at'             => now(),
                'fixed_by'             => $fixedBy,
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

    private function postSell(Tenant $tenant, OtcDeal $deal, bool $moveInventory = true)
    {
        $arAccount = $this->requireAccount($tenant, 'ar');
        $primaryMetal = $this->primaryMetalType($deal);
        $revenueByMetal = [];
        $cogsByMetal = [];
        $journalLines = [];

        foreach ($deal->lines as $line) {
            abort_if(! $line->inventoryItem, 422, 'Sell lines must reference an existing inventory item.');

            $metal = $line->inventoryItem->metal_type;

            if ($moveInventory) {
                $movement = $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                    'movement_type' => 'sale_out',
                    'source_type'   => 'otc_deal',
                    'source_id'     => $deal->id,
                    'moved_at'      => $deal->deal_date,
                ], $line->pcs);
                $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) $movement->total_cost;
            } else {
                $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) InventoryStockMovement::where('source_type', 'otc_deal')
                    ->where('source_id', $deal->id)
                    ->where('inventory_item_id', $line->inventory_item_id)
                    ->sum('total_cost');
            }

            $revenueByMetal[$metal] = ($revenueByMetal[$metal] ?? 0) + (float) $line->line_subtotal + (float) $line->making_charge_amount;
        }

        if (! isset($revenueByMetal[$primaryMetal])) {
            $revenueByMetal[$primaryMetal] = 0.0;
        }
        $revenueByMetal[$primaryMetal] += (float) $deal->premium_amount - (float) $deal->discount_amount;

        $journalLines[] = [
            'otc_chart_of_account_id' => $arAccount->id,
            'debit' => (float) $deal->total, 'credit' => 0,
            'description' => 'Receivable from '.$deal->counterparty_name.' — '.$deal->deal_number,
        ];

        foreach ($revenueByMetal as $metal => $amount) {
            $amount = round($amount, 2);
            if ($amount == 0.0) continue;
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = [
                'otc_chart_of_account_id' => $salesAccount->id,
                'debit' => $amount < 0 ? abs($amount) : 0, 'credit' => $amount < 0 ? 0 : $amount,
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

    /**
     * Dual-leg metal swap: metal_in lines (counterparty's metal coming into our
     * inventory, at appraised value) + metal_out lines (our metal going to the
     * counterparty, at our cost via weighted-average) + an optional cash_topup
     * line. Any residual difference is posted to Metal Exchange Gain/Loss so the
     * entry always balances by construction. Mirrors InvoicingService::postExchange()
     * minus VAT.
     */
    private function postExchange(Tenant $tenant, OtcDeal $deal)
    {
        $arAccount = $this->requireAccount($tenant, 'ar');
        $gainLossAccount = $this->requireAccount($tenant, 'exchange_gain_loss');

        $inValueByMetal = [];
        $outValueByMetal = [];
        $makingByMetal = [];
        $cogsByMetal = [];
        $cashTopup = 0.0;

        foreach ($deal->lines as $line) {
            if ($line->line_type === 'metal_in') {
                abort_if(! $line->inventoryItem, 422, 'Metal-in lines must reference an inventory item.');

                $this->inventory->receiveStock($line->inventoryItem, (float) $line->quantity_grams, (float) $line->unit_price, [
                    'movement_type' => 'exchange_in',
                    'source_type'   => 'otc_deal',
                    'source_id'     => $deal->id,
                    'moved_at'      => $deal->deal_date,
                ], $line->pcs);

                $metal = $line->inventoryItem->metal_type;
                $inValueByMetal[$metal] = ($inValueByMetal[$metal] ?? 0) + (float) $line->line_subtotal;
            } elseif ($line->line_type === 'metal_out') {
                abort_if(! $line->inventoryItem, 422, 'Metal-out lines must reference an inventory item.');

                $movement = $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                    'movement_type' => 'exchange_out',
                    'source_type'   => 'otc_deal',
                    'source_id'     => $deal->id,
                    'moved_at'      => $deal->deal_date,
                ], $line->pcs);

                $metal = $line->inventoryItem->metal_type;
                $outValueByMetal[$metal] = ($outValueByMetal[$metal] ?? 0) + (float) $line->line_subtotal;
                $makingByMetal[$metal] = ($makingByMetal[$metal] ?? 0) + (float) $line->making_charge_amount;
                $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) $movement->total_cost;
            } elseif ($line->line_type === 'cash_topup') {
                $cashTopup += (float) $line->line_subtotal;
            } else {
                throw new RuntimeException("Line type '{$line->line_type}' is not valid on an exchange deal.");
            }
        }

        $journalLines = [];

        foreach ($inValueByMetal as $metal => $inValue) {
            if ($inValue <= 0) continue;
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = ['otc_chart_of_account_id' => $inventoryAccount->id, 'debit' => round($inValue, 2), 'credit' => 0, 'description' => ucfirst($metal).' metal received (OTC) — '.$deal->deal_number];
        }
        if ($cashTopup > 0) {
            $journalLines[] = ['otc_chart_of_account_id' => $arAccount->id, 'debit' => round($cashTopup, 2), 'credit' => 0, 'description' => 'Cash top-up — '.$deal->deal_number];
        }
        $makingTotal = round(array_sum($makingByMetal), 2);
        if ($makingTotal > 0) {
            $journalLines[] = ['otc_chart_of_account_id' => $arAccount->id, 'debit' => $makingTotal, 'credit' => 0, 'description' => 'Making charges receivable — '.$deal->deal_number];
        }
        foreach ($cogsByMetal as $metal => $cogsTotal) {
            if ($cogsTotal <= 0) continue;
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = ['otc_chart_of_account_id' => $inventoryAccount->id, 'debit' => 0, 'credit' => round($cogsTotal, 2), 'description' => ucfirst($metal).' metal given out — cost (OTC) — '.$deal->deal_number];
        }
        foreach ($outValueByMetal as $metal => $outValue) {
            if ($outValue <= 0) continue;
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = ['otc_chart_of_account_id' => $salesAccount->id, 'debit' => 0, 'credit' => round($outValue, 2), 'description' => ucfirst($metal).' metal given out — value (OTC) — '.$deal->deal_number];
        }
        foreach ($makingByMetal as $metal => $makingAmount) {
            if ($makingAmount <= 0) continue;
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = ['otc_chart_of_account_id' => $salesAccount->id, 'debit' => 0, 'credit' => round($makingAmount, 2), 'description' => ucfirst($metal).' making charges (OTC) — '.$deal->deal_number];
        }
        foreach ($cogsByMetal as $metal => $cogsTotal) {
            if ($cogsTotal <= 0) continue;
            $cogsAccount = $this->requireAccount($tenant, "cogs_{$metal}");
            $journalLines[] = ['otc_chart_of_account_id' => $cogsAccount->id, 'debit' => round($cogsTotal, 2), 'credit' => 0, 'description' => ucfirst($metal).' OTC COGS — '.$deal->deal_number];
        }

        $knownDebit = round(collect($journalLines)->sum('debit'), 2);
        $knownCredit = round(collect($journalLines)->sum('credit'), 2);
        $diff = round($knownDebit - $knownCredit, 2);

        if (abs($diff) > 0.001) {
            $journalLines[] = $diff > 0
                ? ['otc_chart_of_account_id' => $gainLossAccount->id, 'debit' => 0, 'credit' => abs($diff), 'description' => 'Exchange valuation plug — '.$deal->deal_number]
                : ['otc_chart_of_account_id' => $gainLossAccount->id, 'debit' => abs($diff), 'credit' => 0, 'description' => 'Exchange valuation plug — '.$deal->deal_number];
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
        abort_if(! in_array($deal->status, ['posted', 'pending_fix'], true), 422, 'Payments can only be recorded on posted or pending-fix deals.');

        return DB::transaction(function () use ($deal, $data) {
            $tenant = $deal->tenant;
            $amount = round((float) $data['amount'], 2);
            $direction = $deal->deal_type === 'buy' ? 'out' : 'in';

            $cash = $this->requireAccount($tenant, $data['account'] ?? 'bank');

            if ($deal->status === 'pending_fix') {
                // No AR/AP posted yet — the full amount books to Customer Deposits,
                // reclassified against AR later when fixPrice() runs.
                $depositAccount = $this->requireAccount($tenant, 'customer_deposits');
                $lines = [
                    ['otc_chart_of_account_id' => $cash->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Deposit — '.$deal->deal_number],
                    ['otc_chart_of_account_id' => $depositAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Deposit — '.$deal->deal_number],
                ];
            } else {
                $arOrAp = $this->requireAccount($tenant, $direction === 'in' ? 'ar' : 'ap');
                $lines = $direction === 'in'
                    ? [
                        ['otc_chart_of_account_id' => $cash->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Receipt — '.$deal->deal_number],
                        ['otc_chart_of_account_id' => $arOrAp->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Receipt — '.$deal->deal_number],
                    ]
                    : [
                        ['otc_chart_of_account_id' => $arOrAp->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Payment — '.$deal->deal_number],
                        ['otc_chart_of_account_id' => $cash->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Payment — '.$deal->deal_number],
                    ];
            }

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
        abort_if(! in_array($deal->status, ['draft', 'posted', 'pending_fix'], true), 422, 'This deal cannot be voided.');

        return DB::transaction(function () use ($deal, $reason) {
            if (in_array($deal->status, ['posted', 'pending_fix'], true)) {
                if ($deal->journalEntry) {
                    $this->ledger->void($deal->journalEntry, $reason);
                }

                InventoryStockMovement::where('source_type', 'otc_deal')
                    ->where('source_id', $deal->id)
                    ->get()
                    ->each(fn ($m) => $this->inventory->reverseMovement($m));
            }

            $deal->update(['status' => 'void', 'voided_at' => now()]);

            return $deal->fresh();
        });
    }

    private function computeLines(array $lines, string $dealType = 'sell'): array
    {
        $defaultLineType = match ($dealType) {
            'buy'  => 'metal_in',
            'sell' => 'metal_out',
            default => 'other', // exchange — caller must specify line_type per line
        };

        return collect($lines)->map(function (array $line) use ($defaultLineType) {
            $lineType = $line['line_type'] ?? $defaultLineType;
            $gross = (float) ($line['gross_weight_grams'] ?? 0);
            $purity = (float) ($line['purity'] ?? 0);
            // cash_topup carries no metal weight — unit_price *is* the amount, so
            // force qty=1 rather than trust the caller to pass quantity_grams=1.
            $qty = $lineType === 'cash_topup'
                ? 1.0
                : ($gross > 0 && $purity > 0 ? round($gross * ($purity / 1000), 3) : (float) ($line['quantity_grams'] ?? 0));
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $lineSubtotal = round($qty * $unitPrice, 2);

            $makingRate = $lineType !== 'cash_topup' && isset($line['making_charge_rate']) && $line['making_charge_rate'] !== ''
                ? (float) $line['making_charge_rate'] : null;
            $makingAmount = $makingRate ? round($qty * $makingRate, 2) : 0.0;

            return [
                'line_type'             => $lineType,
                'inventory_item_id'     => $line['inventory_item_id'] ?? null,
                'metal_type'            => $line['metal_type'] ?? null,
                'description'           => $line['description'] ?? '',
                'purity'                => $lineType === 'cash_topup' ? null : ($purity ?: null),
                'gross_weight_grams'    => $lineType === 'cash_topup' ? null : ($gross ?: null),
                'quantity_grams'        => $qty,
                'pcs'                   => isset($line['pcs']) && $line['pcs'] !== '' ? (int) $line['pcs'] : null,
                'unit_price'            => $unitPrice,
                'making_charge_rate'    => $makingRate,
                'making_charge_amount'  => $makingAmount,
                'line_subtotal'         => $lineSubtotal,
                'line_total'            => round($lineSubtotal + $makingAmount, 2),
            ];
        })->filter(fn ($l) => $l['line_subtotal'] > 0 || $l['making_charge_amount'] > 0 || strlen($l['description']) > 0)
          ->values()->all();
    }

    private function primaryMetalType(OtcDeal $deal): string
    {
        foreach ($deal->lines as $line) {
            if (in_array($line->line_type, ['metal_in', 'metal_out'], true) && $line->inventoryItem) {
                return $line->inventoryItem->metal_type;
            }
        }

        return 'gold';
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
