<?php

// app/Services/Accounting/InvoicingService.php

namespace App\Services\Accounting;

use App\Models\BullionClient;
use App\Models\ChartOfAccount;
use App\Models\InventoryStockMovement;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoicingService
{
    public function __construct(
        private InvoiceNumberService $invoiceNumbers,
        private InventoryService $inventory,
        private LedgerService $ledger,
        private ClientTransactionSyncService $clientTransactionSync,
    ) {}

    /**
     * Persist an invoice + lines as a draft, recomputing all totals server-side (never
     * trusting client-submitted totals). The invoice number is minted here, at creation —
     * the safer default for UAE FTA gapless-numbering expectations; a subsequently voided
     * invoice keeps its number rather than recycling it.
     *
     * $data: bullion_client_id, invoice_type, invoice_date, currency_code, exchange_rate,
     *        pricing_type, gold_rate_per_oz, gold_rate_per_gram, party_reference,
     *        premium_amount, discount_amount, notes, created_by, lines: [[line_type,
     *        inventory_item_id, description, purity, quantity_grams, gross_weight_grams,
     *        pcs, unit_price, metal_vat_treatment, metal_vat_rate, making_charge_rate,
     *        making_charge_amount, making_vat_treatment, making_vat_rate], ...]
     */
    public function createDraft(Tenant $tenant, array $data): Invoice
    {
        return DB::transaction(function () use ($tenant, $data) {
            $invoiceNumber = $this->invoiceNumbers->next($tenant, $data['invoice_type']);
            $computedLines = $this->computeLines($data['lines']);

            if ($data['invoice_type'] === 'exchange') {
                // An exchange's metal_in/metal_out legs are a swap, not an amount owed —
                // whatever the client actually owes in cash is just the cash_topup line(s)
                // plus making charges (+ VAT on those), exactly what postExchange() debits
                // to AR. Summing every line's line_subtotal here (like sale/purchase do)
                // would double-count both legs' full notional value into "total owed".
                $subtotal = 0.0;
                $vatTotal = 0.0;
                foreach ($computedLines as $line) {
                    if ($line['line_type'] === 'cash_topup') {
                        $subtotal += (float) $line['line_subtotal'];
                        $vatTotal += (float) $line['metal_vat_amount'];
                    }
                    $subtotal += (float) $line['making_charge_amount'];
                    $vatTotal += (float) $line['making_vat_amount'];
                }
                $subtotal = round($subtotal, 2);
                $vatTotal = round($vatTotal, 2);
            } else {
                // subtotal/vat_total combine the metal and making-charge portions of every
                // line — each can carry its own VAT treatment, but they're summed together
                // for the invoice header the same way the reference tax invoice presents them.
                $subtotal = round(
                    collect($computedLines)->sum('line_subtotal') + collect($computedLines)->sum('making_charge_amount'),
                    2
                );
                $vatTotal = round(
                    collect($computedLines)->sum('metal_vat_amount') + collect($computedLines)->sum('making_vat_amount'),
                    2
                );
            }
            $premium = round((float) ($data['premium_amount'] ?? 0), 2);
            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $total = round($subtotal + $vatTotal + $premium - $discount, 2);

            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'bullion_client_id' => $data['bullion_client_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_type' => $data['invoice_type'],
                'status' => 'draft',
                'invoice_date' => $data['invoice_date'],
                'currency_code' => $data['currency_code'] ?? 'AED',
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'pricing_type' => $data['pricing_type'] ?? 'fixed',
                'metal_rates' => $data['metal_rates'] ?? null,
                'party_reference' => $data['party_reference'] ?? null,
                'premium_amount' => $premium,
                'discount_amount' => $discount,
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            foreach ($computedLines as $i => $line) {
                InvoiceLine::create(array_merge($line, [
                    'invoice_id' => $invoice->id,
                    'line_order' => $i,
                ]));
            }

            return $invoice->fresh('lines');
        });
    }

    public function updateDraft(Invoice $invoice, array $data): Invoice
    {
        abort_if($invoice->status !== 'draft', 422, 'Only draft invoices can be edited.');

        return DB::transaction(function () use ($invoice, $data) {
            $computedLines = $this->computeLines($data['lines']);
            $invoiceType   = $invoice->invoice_type;

            if ($invoiceType === 'exchange') {
                $subtotal = 0.0;
                $vatTotal = 0.0;
                foreach ($computedLines as $line) {
                    if ($line['line_type'] === 'cash_topup') {
                        $subtotal += (float) $line['line_subtotal'];
                        $vatTotal += (float) $line['metal_vat_amount'];
                    }
                    $subtotal += (float) $line['making_charge_amount'];
                    $vatTotal += (float) $line['making_vat_amount'];
                }
                $subtotal = round($subtotal, 2);
                $vatTotal = round($vatTotal, 2);
            } else {
                $subtotal = round(
                    collect($computedLines)->sum('line_subtotal') + collect($computedLines)->sum('making_charge_amount'),
                    2
                );
                $vatTotal = round(
                    collect($computedLines)->sum('metal_vat_amount') + collect($computedLines)->sum('making_vat_amount'),
                    2
                );
            }
            $premium  = round((float) ($data['premium_amount'] ?? 0), 2);
            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $total    = round($subtotal + $vatTotal + $premium - $discount, 2);

            $invoice->update([
                'invoice_date'     => $data['invoice_date'],
                'currency_code'    => $data['currency_code'] ?? 'AED',
                'exchange_rate'    => $data['exchange_rate'] ?? 1,
                'metal_rates'      => $data['metal_rates'] ?? null,
                'party_reference'  => $data['party_reference'] ?? null,
                'premium_amount'   => $premium,
                'discount_amount'  => $discount,
                'subtotal'         => $subtotal,
                'vat_total'        => $vatTotal,
                'total'            => $total,
                'notes'            => $data['notes'] ?? null,
            ]);

            $invoice->lines()->delete();
            foreach ($computedLines as $i => $line) {
                InvoiceLine::create(array_merge($line, [
                    'invoice_id' => $invoice->id,
                    'line_order' => $i,
                ]));
            }

            return $invoice->fresh('lines');
        });
    }

    private function computeLines(array $lines): array
    {
        return collect($lines)->map(function (array $line) {
            $qty = isset($line['quantity_grams']) && $line['quantity_grams'] !== '' ? (float) $line['quantity_grams'] : null;
            $grossWeight = isset($line['gross_weight_grams']) && $line['gross_weight_grams'] !== '' ? (float) $line['gross_weight_grams'] : null;
            $purity = isset($line['purity']) && $line['purity'] !== '' ? (float) $line['purity'] : null;
            $price = isset($line['unit_price']) && $line['unit_price'] !== '' ? (float) $line['unit_price'] : null;

            $subtotal = ($qty !== null && $price !== null)
                ? round($qty * $price, 2)
                : round((float) ($line['line_subtotal'] ?? 0), 2);

            $metalVatRate = (float) ($line['metal_vat_rate'] ?? 0);
            $metalVatAmount = round($subtotal * $metalVatRate / 100, 2);

            $makingRate = isset($line['making_charge_rate']) && $line['making_charge_rate'] !== '' ? (float) $line['making_charge_rate'] : null;
            $makingAmount = ($qty !== null && $makingRate !== null)
                ? round($qty * $makingRate, 2)
                : round((float) ($line['making_charge_amount'] ?? 0), 2);

            $makingVatRate = (float) ($line['making_vat_rate'] ?? 0);
            $makingVatAmount = round($makingAmount * $makingVatRate / 100, 2);

            return [
                'line_type' => $line['line_type'],
                'inventory_item_id' => $line['inventory_item_id'] ?? null,
                'metal_type' => $line['metal_type'] ?? null,
                'description' => $line['description'],
                'purity' => $purity,
                'quantity_grams' => $qty,
                'gross_weight_grams' => $grossWeight,
                'pcs' => isset($line['pcs']) && $line['pcs'] !== '' ? (int) $line['pcs'] : null,
                'unit_price' => $price,
                'line_subtotal' => $subtotal,
                'metal_vat_treatment' => $line['metal_vat_treatment'] ?? 'standard',
                'metal_vat_rate' => $metalVatRate,
                'metal_vat_amount' => $metalVatAmount,
                'making_charge_rate' => $makingRate,
                'making_charge_amount' => $makingAmount,
                'making_vat_treatment' => $line['making_vat_treatment'] ?? 'standard',
                'making_vat_rate' => $makingVatRate,
                'making_vat_amount' => $makingVatAmount,
                'line_total' => round($subtotal + $metalVatAmount + $makingAmount + $makingVatAmount, 2),
            ];
        })->all();
    }

    /**
     * Post a draft invoice: moves inventory and syncs the ClientTransaction AML record
     * unconditionally. For a `fixed`-pricing invoice this also posts the financial journal
     * entry immediately, same as always. For an `unfixed` invoice (sale only — the metal
     * changes hands now, the price is settled later) the journal entry is deliberately
     * skipped and the invoice lands in `pending_fix` instead of `posted`; call fixPrice()
     * once the rate is known to post the financial side. Transaction-wrapped throughout.
     */
    public function post(Invoice $invoice): Invoice
    {
        abort_if($invoice->status !== 'draft', 422, 'Only draft invoices can be posted.');

        if ($invoice->pricing_type === 'unfixed' && $invoice->invoice_type !== 'sale') {
            abort(422, 'Unfixed pricing is only supported on sale invoices.');
        }

        return DB::transaction(function () use ($invoice) {
            $tenant = $invoice->tenant;
            $invoice->loadMissing('lines.inventoryItem');

            if ($invoice->pricing_type === 'unfixed') {
                $this->moveInventoryForSale($invoice);

                $invoice->update(['status' => 'pending_fix']);
                $this->clientTransactionSync->createFromInvoice($invoice);

                return $invoice->fresh(['lines', 'clientTransaction']);
            }

            $journalEntry = match ($invoice->invoice_type) {
                'purchase' => $this->postPurchase($tenant, $invoice),
                'sale' => $this->postSale($tenant, $invoice),
                'exchange' => $this->postExchange($tenant, $invoice),
                default => throw new RuntimeException("Unknown invoice type: {$invoice->invoice_type}"),
            };

            $invoice->update([
                'status' => 'posted',
                'journal_entry_id' => $journalEntry->id,
                'posted_at' => now(),
            ]);

            $this->clientTransactionSync->createFromInvoice($invoice);

            return $invoice->fresh(['lines', 'journalEntry', 'clientTransaction']);
        });
    }

    /**
     * Move inventory for a sale's metal_out lines without touching the ledger — the piece
     * shared between an unfixed post() and a normal fixed sale would otherwise duplicate.
     */
    private function moveInventoryForSale(Invoice $invoice): void
    {
        foreach ($invoice->lines as $line) {
            if ($line->line_type !== 'metal_out') {
                continue;
            }

            abort_if(! $line->inventoryItem, 422, 'Metal-out lines must reference an inventory item.');

            $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                'movement_type' => 'sale_out',
                'source_type' => 'invoice',
                'source_id' => $invoice->id,
                'moved_at' => $invoice->invoice_date,
            ], $line->pcs);
        }
    }

    private function postPurchase(Tenant $tenant, Invoice $invoice)
    {
        $expenseAccount = $this->requireAccount($tenant, 'other_expense');
        $vatRecoverableAccount = $this->requireAccount($tenant, 'vat_recoverable');
        $apAccount = $this->requireAccount($tenant, 'ap');

        $inventoryByMetal = []; // metal => amount, since each metal has its own inventory account
        $journalLines = [];

        foreach ($invoice->lines as $line) {
            if ($line->line_type === 'metal_in') {
                if (! $line->inventoryItem) {
                    $metalType = $line->metal_type;
                    abort_if(! $metalType, 422, 'Metal-in lines must either reference an inventory item or specify a metal type.');
                    $line->setRelation('inventoryItem', $this->findOrCreateMiscItem($invoice->tenant, $metalType));
                }

                $movement = $this->inventory->receiveStock($line->inventoryItem, (float) $line->quantity_grams, (float) $line->unit_price, [
                    'movement_type' => 'purchase_in',
                    'source_type' => 'invoice',
                    'source_id' => $invoice->id,
                    'moved_at' => $invoice->invoice_date,
                ], $line->pcs);

                $metal = $line->inventoryItem->metal_type;
                $inventoryByMetal[$metal] = ($inventoryByMetal[$metal] ?? 0) + (float) $movement->total_cost;
            } elseif ($line->line_type === 'other') {
                $journalLines[] = [
                    'chart_of_account_id' => $expenseAccount->id,
                    'debit' => (float) $line->line_subtotal,
                    'credit' => 0,
                    'description' => $line->description,
                ];
            } else {
                throw new RuntimeException("Line type '{$line->line_type}' is not valid on a purchase invoice.");
            }
        }

        foreach ($inventoryByMetal as $metal => $amount) {
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = [
                'chart_of_account_id' => $inventoryAccount->id,
                'debit' => round($amount, 2),
                'credit' => 0,
                'description' => ucfirst($metal).' inventory received — '.$invoice->invoice_number,
            ];
        }

        if ((float) $invoice->vat_total > 0) {
            $journalLines[] = [
                'chart_of_account_id' => $vatRecoverableAccount->id,
                'debit' => (float) $invoice->vat_total,
                'credit' => 0,
                'description' => 'VAT recoverable — '.$invoice->invoice_number,
            ];
        }

        $journalLines[] = [
            'chart_of_account_id' => $apAccount->id,
            'debit' => 0,
            'credit' => (float) $invoice->total,
            'description' => 'Payable to supplier — '.$invoice->invoice_number,
        ];

        return $this->ledger->post($tenant, [
            'entry_date' => $invoice->invoice_date->toDateString(),
            'reference' => $invoice->invoice_number,
            'source_type' => 'invoice',
            'source_id' => $invoice->id,
            'bullion_client_id' => $invoice->bullion_client_id,
            'currency_code' => $invoice->currency_code,
            'exchange_rate' => (float) $invoice->exchange_rate,
            'created_by' => $invoice->created_by,
        ], $journalLines);
    }

    /**
     * $moveInventory is false when finalizing a previously-unfixed sale: the metal already
     * left inventory back at the original post() call, so this only needs the COGS figure
     * from that existing movement, not a second issueStock() call (which would double-issue).
     */
    private function postSale(Tenant $tenant, Invoice $invoice, bool $moveInventory = true)
    {
        $arAccount = $this->requireAccount($tenant, 'ar');
        $vatPayableAccount = $this->requireAccount($tenant, 'vat_payable');

        $primaryMetal = $this->primaryMetalType($invoice);
        $revenueByMetal = []; // metal => metal + making charge amounts combined
        $cogsByMetal = [];    // metal => cost of goods sold
        $journalLines = [];

        foreach ($invoice->lines as $line) {
            if ($line->line_type === 'metal_out') {
                abort_if(! $line->inventoryItem, 422, 'Metal-out lines must reference an inventory item.');

                $metal = $line->inventoryItem->metal_type;

                if ($moveInventory) {
                    $movement = $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                        'movement_type' => 'sale_out',
                        'source_type' => 'invoice',
                        'source_id' => $invoice->id,
                        'moved_at' => $invoice->invoice_date,
                    ], $line->pcs);
                    $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) $movement->total_cost;
                } else {
                    $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) InventoryStockMovement::where('source_type', 'invoice')
                        ->where('source_id', $invoice->id)
                        ->where('inventory_item_id', $line->inventory_item_id)
                        ->sum('total_cost');
                }

                $revenueByMetal[$metal] = ($revenueByMetal[$metal] ?? 0) + (float) $line->line_subtotal + (float) $line->making_charge_amount;
            } elseif ($line->line_type === 'other') {
                $revenueByMetal[$primaryMetal] = ($revenueByMetal[$primaryMetal] ?? 0) + (float) $line->line_subtotal + (float) $line->making_charge_amount;
            } else {
                throw new RuntimeException("Line type '{$line->line_type}' is not valid on a sale invoice.");
            }
        }

        // Premium/discount adjust the header total but aren't metal-specific — fold them into
        // the primary metal's revenue bucket so the sum of per-metal credits still ties to total.
        if (! isset($revenueByMetal[$primaryMetal])) {
            $revenueByMetal[$primaryMetal] = 0.0;
        }
        $revenueByMetal[$primaryMetal] += (float) $invoice->premium_amount - (float) $invoice->discount_amount;

        $journalLines[] = [
            'chart_of_account_id' => $arAccount->id,
            'debit' => (float) $invoice->total,
            'credit' => 0,
            'description' => 'Receivable from customer — '.$invoice->invoice_number,
        ];

        foreach ($revenueByMetal as $metal => $amount) {
            $amount = round($amount, 2);
            if ($amount == 0.0) {
                continue;
            }
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = [
                'chart_of_account_id' => $salesAccount->id,
                'debit' => $amount < 0 ? abs($amount) : 0,
                'credit' => $amount < 0 ? 0 : $amount,
                'description' => ucfirst($metal).' sales revenue — '.$invoice->invoice_number,
            ];
        }

        if ((float) $invoice->vat_total > 0) {
            $journalLines[] = [
                'chart_of_account_id' => $vatPayableAccount->id,
                'debit' => 0,
                'credit' => (float) $invoice->vat_total,
                'description' => 'VAT payable — '.$invoice->invoice_number,
            ];
        }

        foreach ($cogsByMetal as $metal => $cogsTotal) {
            if ($cogsTotal <= 0) {
                continue;
            }
            $cogsAccount = $this->requireAccount($tenant, "cogs_{$metal}");
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");

            $journalLines[] = [
                'chart_of_account_id' => $cogsAccount->id,
                'debit' => round($cogsTotal, 2),
                'credit' => 0,
                'description' => ucfirst($metal).' COGS — '.$invoice->invoice_number,
            ];
            $journalLines[] = [
                'chart_of_account_id' => $inventoryAccount->id,
                'debit' => 0,
                'credit' => round($cogsTotal, 2),
                'description' => ucfirst($metal).' inventory drawdown — '.$invoice->invoice_number,
            ];
        }

        return $this->ledger->post($tenant, [
            'entry_date' => $invoice->invoice_date->toDateString(),
            'reference' => $invoice->invoice_number,
            'source_type' => 'invoice',
            'source_id' => $invoice->id,
            'bullion_client_id' => $invoice->bullion_client_id,
            'currency_code' => $invoice->currency_code,
            'exchange_rate' => (float) $invoice->exchange_rate,
            'created_by' => $invoice->created_by,
        ], $journalLines);
    }

    /**
     * Finalize a previously-unfixed sale: apply the now-known pricing to each line, recompute
     * header totals, and post the financial journal entry that was deliberately skipped at
     * post() time. Inventory already moved back then — this only touches money.
     *
     * $lineUpdates: [line_id => [unit_price, metal_vat_treatment, metal_vat_rate,
     *                making_charge_rate, making_vat_treatment, making_vat_rate], ...]
     */
    public function fixPrice(Invoice $invoice, array $lineUpdates, ?int $fixedBy = null): Invoice
    {
        abort_if($invoice->status !== 'pending_fix', 422, 'Only invoices awaiting a price fix can be fixed.');

        return DB::transaction(function () use ($invoice, $lineUpdates, $fixedBy) {
            $tenant = $invoice->tenant;
            $invoice->loadMissing('lines.inventoryItem');

            foreach ($invoice->lines as $line) {
                $update = $lineUpdates[$line->id] ?? null;
                if (! $update) {
                    continue;
                }

                $computed = $this->computeLines([array_merge($line->only([
                    'line_type', 'inventory_item_id', 'description', 'purity',
                    'quantity_grams', 'gross_weight_grams', 'pcs',
                ]), $update)])[0];

                $line->update($computed);
            }

            $invoice->refresh()->load('lines');

            $subtotal = round($invoice->lines->sum('line_subtotal') + $invoice->lines->sum('making_charge_amount'), 2);
            $vatTotal = round($invoice->lines->sum('metal_vat_amount') + $invoice->lines->sum('making_vat_amount'), 2);
            $total = round($subtotal + $vatTotal + (float) $invoice->premium_amount - (float) $invoice->discount_amount, 2);

            $invoice->update(['subtotal' => $subtotal, 'vat_total' => $vatTotal, 'total' => $total]);

            $journalEntry = $this->postSale($tenant, $invoice, moveInventory: false);

            // Any deposits collected while this invoice was pending_fix were booked to
            // Customer Deposits at the time (no AR existed yet). Now that AR exists, move
            // them across in one reclass entry — amount_paid already reflects these, so
            // outstandingBalance() needs no further adjustment.
            $depositsCollected = round((float) $invoice->payments()->sum('amount'), 2);
            if ($depositsCollected > 0) {
                $depositAccount = $this->requireAccount($tenant, 'customer_deposits');
                $arAccount = $this->requireAccount($tenant, 'ar');

                $this->ledger->post($tenant, [
                    'entry_date' => now()->toDateString(),
                    'reference' => $invoice->invoice_number,
                    'source_type' => 'deposit_reclass',
                    'source_id' => $invoice->id,
                    'bullion_client_id' => $invoice->bullion_client_id,
                    'currency_code' => $invoice->currency_code,
                    'exchange_rate' => (float) $invoice->exchange_rate,
                    'created_by' => $fixedBy,
                ], [
                    ['chart_of_account_id' => $depositAccount->id, 'debit' => $depositsCollected, 'credit' => 0, 'description' => 'Deposits applied — '.$invoice->invoice_number],
                    ['chart_of_account_id' => $arAccount->id, 'debit' => 0, 'credit' => $depositsCollected, 'description' => 'Deposits applied — '.$invoice->invoice_number],
                ]);
            }

            $invoice->update([
                'status' => 'posted',
                'journal_entry_id' => $journalEntry->id,
                'posted_at' => now(),
                'fixed_at' => now(),
                'fixed_by' => $fixedBy,
            ]);

            return $invoice->fresh(['lines', 'journalEntry']);
        });
    }

    /**
     * Dual-leg metal swap: metal_in lines (client's metal coming into our inventory, at
     * appraised value) + metal_out lines (our metal going to the client, at our cost via
     * weighted-average) + an optional cash_topup line (cash received from the client to
     * balance the exchange — the reverse direction, cash paid BY the dealer TO the client,
     * is not supported in v1). Any residual difference between the two legs plus VAT is
     * posted to the Metal Exchange Gain/Loss account so the entry always balances by
     * construction. This mapping is the most novel part of the whole module — walk it
     * through with the client/their accountant using a real transaction before relying on
     * it for filing.
     */
    private function postExchange(Tenant $tenant, Invoice $invoice)
    {
        $vatPayableAccount = $this->requireAccount($tenant, 'vat_payable');
        $arAccount = $this->requireAccount($tenant, 'ar');
        $gainLossAccount = $this->requireAccount($tenant, 'exchange_gain_loss');

        $inValueByMetal = [];  // metal => client's metal received, at appraised value
        $outValueByMetal = []; // metal => our metal given to client, at selling value
        $makingByMetal = [];   // metal => making/fabrication charges on the metal given out
        $makingVatTotal = 0.0; // VAT on those making charges — client owes this in cash too
        $cogsByMetal = [];     // metal => cost basis of metal given out
        $cashTopup = 0.0;      // cash received from client to balance the exchange

        foreach ($invoice->lines as $line) {
            if ($line->line_type === 'metal_in') {
                abort_if(! $line->inventoryItem, 422, 'Metal-in lines must reference an inventory item.');

                $this->inventory->receiveStock($line->inventoryItem, (float) $line->quantity_grams, (float) $line->unit_price, [
                    'movement_type' => 'exchange_in',
                    'source_type' => 'invoice',
                    'source_id' => $invoice->id,
                    'moved_at' => $invoice->invoice_date,
                ], $line->pcs);

                $metal = $line->inventoryItem->metal_type;
                $inValueByMetal[$metal] = ($inValueByMetal[$metal] ?? 0) + (float) $line->line_subtotal;
            } elseif ($line->line_type === 'metal_out') {
                abort_if(! $line->inventoryItem, 422, 'Metal-out lines must reference an inventory item.');

                $movement = $this->inventory->issueStock($line->inventoryItem, (float) $line->quantity_grams, [
                    'movement_type' => 'exchange_out',
                    'source_type' => 'invoice',
                    'source_id' => $invoice->id,
                    'moved_at' => $invoice->invoice_date,
                ], $line->pcs);

                $metal = $line->inventoryItem->metal_type;
                $outValueByMetal[$metal] = ($outValueByMetal[$metal] ?? 0) + (float) $line->line_subtotal;
                $makingByMetal[$metal] = ($makingByMetal[$metal] ?? 0) + (float) $line->making_charge_amount;
                $makingVatTotal += (float) $line->making_vat_amount;
                $cogsByMetal[$metal] = ($cogsByMetal[$metal] ?? 0) + (float) $movement->total_cost;
            } elseif ($line->line_type === 'cash_topup') {
                $cashTopup += (float) $line->line_subtotal;
            } else {
                throw new RuntimeException("Line type '{$line->line_type}' is not valid on an exchange invoice.");
            }
        }

        $vatTotal = round((float) $invoice->vat_total, 2);

        $journalLines = [];

        foreach ($inValueByMetal as $metal => $inValue) {
            if ($inValue <= 0) {
                continue;
            }
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = ['chart_of_account_id' => $inventoryAccount->id, 'debit' => round($inValue, 2), 'credit' => 0, 'description' => ucfirst($metal).' metal received — '.$invoice->invoice_number];
        }
        if ($cashTopup > 0) {
            $journalLines[] = ['chart_of_account_id' => $arAccount->id, 'debit' => round($cashTopup, 2), 'credit' => 0, 'description' => 'Cash top-up — '.$invoice->invoice_number];
        }
        $makingTotal = round(array_sum($makingByMetal), 2);
        if ($makingTotal > 0 || $makingVatTotal > 0) {
            // Making charges are a cash obligation the client owes us, separate from the
            // metal-for-metal swap itself — they don't net against the inventory/sales legs.
            $journalLines[] = ['chart_of_account_id' => $arAccount->id, 'debit' => round($makingTotal + $makingVatTotal, 2), 'credit' => 0, 'description' => 'Making charges receivable — '.$invoice->invoice_number];
        }
        foreach ($cogsByMetal as $metal => $cogsTotal) {
            if ($cogsTotal <= 0) {
                continue;
            }
            $inventoryAccount = $this->requireAccount($tenant, "inventory_{$metal}");
            $journalLines[] = ['chart_of_account_id' => $inventoryAccount->id, 'debit' => 0, 'credit' => round($cogsTotal, 2), 'description' => ucfirst($metal).' metal given out (cost) — '.$invoice->invoice_number];
        }
        foreach ($outValueByMetal as $metal => $outValue) {
            if ($outValue <= 0) {
                continue;
            }
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = ['chart_of_account_id' => $salesAccount->id, 'debit' => 0, 'credit' => round($outValue, 2), 'description' => ucfirst($metal).' metal given out (value) — '.$invoice->invoice_number];
        }
        foreach ($makingByMetal as $metal => $makingAmount) {
            if ($makingAmount <= 0) {
                continue;
            }
            $salesAccount = $this->requireAccount($tenant, "sales_{$metal}");
            $journalLines[] = ['chart_of_account_id' => $salesAccount->id, 'debit' => 0, 'credit' => round($makingAmount, 2), 'description' => ucfirst($metal).' making charges — '.$invoice->invoice_number];
        }
        foreach ($cogsByMetal as $metal => $cogsTotal) {
            if ($cogsTotal <= 0) {
                continue;
            }
            $cogsAccount = $this->requireAccount($tenant, "cogs_{$metal}");
            $journalLines[] = ['chart_of_account_id' => $cogsAccount->id, 'debit' => round($cogsTotal, 2), 'credit' => 0, 'description' => ucfirst($metal).' COGS — '.$invoice->invoice_number];
        }
        if ($vatTotal > 0) {
            $journalLines[] = ['chart_of_account_id' => $vatPayableAccount->id, 'debit' => 0, 'credit' => $vatTotal, 'description' => 'VAT payable — '.$invoice->invoice_number];
        }

        $knownDebit = round(collect($journalLines)->sum('debit'), 2);
        $knownCredit = round(collect($journalLines)->sum('credit'), 2);
        $diff = round($knownDebit - $knownCredit, 2);

        if (abs($diff) > 0.001) {
            $journalLines[] = $diff > 0
                ? ['chart_of_account_id' => $gainLossAccount->id, 'debit' => 0, 'credit' => abs($diff), 'description' => 'Exchange valuation plug — '.$invoice->invoice_number]
                : ['chart_of_account_id' => $gainLossAccount->id, 'debit' => abs($diff), 'credit' => 0, 'description' => 'Exchange valuation plug — '.$invoice->invoice_number];
        }

        return $this->ledger->post($tenant, [
            'entry_date' => $invoice->invoice_date->toDateString(),
            'reference' => $invoice->invoice_number,
            'source_type' => 'invoice',
            'source_id' => $invoice->id,
            'bullion_client_id' => $invoice->bullion_client_id,
            'currency_code' => $invoice->currency_code,
            'exchange_rate' => (float) $invoice->exchange_rate,
            'created_by' => $invoice->created_by,
        ], $journalLines);
    }

    /**
     * Record a payment against a specific invoice. Thin wrapper around
     * recordClientPayment() that infers direction from the invoice type and derives the
     * client from it — the invoice-detail-page "Record payment" form uses this.
     */
    public function recordPayment(Invoice $invoice, array $data): InvoicePayment
    {
        abort_if(! in_array($invoice->status, ['posted', 'pending_fix'], true), 422, 'Payments can only be recorded on posted or pending-fix invoices.');

        $direction = $data['direction'] ?? ($invoice->invoice_type === 'purchase' ? 'out' : 'in');

        return $this->recordClientPayment($invoice->tenant, $invoice->client, array_merge($data, [
            'invoice_id' => $invoice->id,
            'direction' => $direction,
        ]));
    }

    /**
     * Record a payment for a client, optionally tied to an invoice. Three cases:
     *  - invoice is posted (real AR/AP exists): applies against it exactly as before,
     *    overpayment still spills to a deposits/advances account.
     *  - invoice is pending_fix, or there's no invoice at all: nothing to apply against yet
     *    (no AR/AP posted), so the full amount books to Customer/Supplier Deposits. If
     *    linked to an invoice, amount_paid still updates immediately so
     *    outstandingBalance() is correct even before the ledger catches up in fixPrice().
     */
    public function recordClientPayment(Tenant $tenant, BullionClient $client, array $data): InvoicePayment
    {
        return DB::transaction(function () use ($tenant, $client, $data) {
            $invoice = null;
            if (! empty($data['invoice_id'])) {
                $invoice = Invoice::findOrFail($data['invoice_id']);
                abort_if($invoice->tenant_id !== $tenant->id || $invoice->bullion_client_id !== $client->id, 404);
            }

            $amount = round((float) $data['amount'], 2);
            $direction = $data['direction'] ?? ($invoice ? ($invoice->invoice_type === 'purchase' ? 'out' : 'in') : null);
            abort_if(! in_array($direction, ['in', 'out'], true), 422, 'Payment direction (receipt from client / payment to client) is required.');

            $cashAccount = $this->requireAccount($tenant, ($data['method'] ?? null) === 'bank_transfer' ? 'bank' : 'cash');

            if ($invoice && $invoice->status === 'posted') {
                $outstanding = $invoice->outstandingBalance();
                $isPurchase = $invoice->invoice_type === 'purchase';
                $balanceAccount = $this->requireAccount($tenant, $isPurchase ? 'ap' : 'ar');

                $applied = min($amount, max($outstanding, 0));
                $overpaid = round($amount - $applied, 2);
                $journalLines = [];

                if ($isPurchase) {
                    // Paying down what we owe: Dr AP, Cr Cash/Bank.
                    $journalLines[] = ['chart_of_account_id' => $balanceAccount->id, 'debit' => $applied, 'credit' => 0, 'description' => 'Payment — '.$invoice->invoice_number];
                    $journalLines[] = ['chart_of_account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Payment — '.$invoice->invoice_number];
                    if ($overpaid > 0) {
                        $depositAccount = $this->requireAccount($tenant, 'supplier_deposits');
                        $journalLines[] = ['chart_of_account_id' => $depositAccount->id, 'debit' => $overpaid, 'credit' => 0, 'description' => 'Advance to supplier — '.$invoice->invoice_number];
                    }
                } else {
                    // Receiving what's owed to us: Dr Cash/Bank, Cr AR.
                    $journalLines[] = ['chart_of_account_id' => $cashAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Payment — '.$invoice->invoice_number];
                    $journalLines[] = ['chart_of_account_id' => $balanceAccount->id, 'debit' => 0, 'credit' => $applied, 'description' => 'Payment — '.$invoice->invoice_number];
                    if ($overpaid > 0) {
                        $depositAccount = $this->requireAccount($tenant, 'customer_deposits');
                        $journalLines[] = ['chart_of_account_id' => $depositAccount->id, 'debit' => 0, 'credit' => $overpaid, 'description' => 'Advance from customer — '.$invoice->invoice_number];
                    }
                }

                $reference = $invoice->invoice_number;
                $currencyCode = $invoice->currency_code;
                $exchangeRate = (float) $invoice->exchange_rate;
            } else {
                // No AR/AP exists yet (pending_fix or no invoice) — the whole amount is
                // held as a deposit/advance until it's linked to a priced invoice.
                $depositAccount = $this->requireAccount($tenant, $direction === 'out' ? 'supplier_deposits' : 'customer_deposits');
                $label = ($data['purpose'] ?? 'Deposit').' — '.($invoice->invoice_number ?? $client->displayName());

                $journalLines = $direction === 'out'
                    ? [
                        ['chart_of_account_id' => $depositAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => $label],
                        ['chart_of_account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => $label],
                    ]
                    : [
                        ['chart_of_account_id' => $cashAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => $label],
                        ['chart_of_account_id' => $depositAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => $label],
                    ];

                $reference = $invoice->invoice_number ?? ($data['reference'] ?? null);
                $currencyCode = $invoice->currency_code ?? 'AED';
                $exchangeRate = (float) ($invoice->exchange_rate ?? 1);
            }

            $journalEntry = $this->ledger->post($tenant, [
                'entry_date' => $data['payment_date'],
                'reference' => $reference,
                'source_type' => 'invoice_payment',
                'source_id' => $invoice->id ?? null,
                'bullion_client_id' => $client->id,
                'currency_code' => $currencyCode,
                'exchange_rate' => $exchangeRate,
                'created_by' => $data['created_by'] ?? null,
            ], $journalLines);

            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id ?? null,
                'bullion_client_id' => $client->id,
                'tenant_id' => $tenant->id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'direction' => $direction,
                'purpose' => $data['purpose'] ?? null,
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'journal_entry_id' => $journalEntry->id,
                'created_by' => $data['created_by'] ?? null,
            ]);

            if ($invoice) {
                $invoice->update(['amount_paid' => round((float) $invoice->amount_paid + $amount, 2)]);
            }

            return $payment;
        });
    }

    /**
     * Link a previously-unlinked deposit to an invoice, immediately reducing its
     * outstanding balance. If the invoice already has real AR/AP (status posted), also
     * reclasses the deposit in the ledger right away; if it's still pending_fix, the
     * reclass happens later, in bulk, inside fixPrice().
     */
    public function applyDepositToInvoice(InvoicePayment $deposit, Invoice $invoice): void
    {
        abort_if($deposit->invoice_id !== null, 422, 'This payment is already linked to an invoice.');
        abort_if($deposit->bullion_client_id !== $invoice->bullion_client_id, 422, 'This payment belongs to a different client.');
        abort_if(! in_array($invoice->status, ['posted', 'pending_fix'], true), 422, 'Can only apply a deposit to a posted or pending-fix invoice.');

        DB::transaction(function () use ($deposit, $invoice) {
            $deposit->update(['invoice_id' => $invoice->id]);
            $invoice->update(['amount_paid' => round((float) $invoice->amount_paid + (float) $deposit->amount, 2)]);

            if ($invoice->status !== 'posted') {
                return; // reclassified in bulk by fixPrice() once this invoice is priced
            }

            $tenant = $invoice->tenant;
            $depositAccount = $this->requireAccount($tenant, $deposit->direction === 'out' ? 'supplier_deposits' : 'customer_deposits');
            $balanceAccount = $this->requireAccount($tenant, $invoice->invoice_type === 'purchase' ? 'ap' : 'ar');
            $amount = round((float) $deposit->amount, 2);

            $journalLines = $deposit->direction === 'out'
                ? [
                    ['chart_of_account_id' => $balanceAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Deposit applied — '.$invoice->invoice_number],
                    ['chart_of_account_id' => $depositAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Deposit applied — '.$invoice->invoice_number],
                ]
                : [
                    ['chart_of_account_id' => $depositAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Deposit applied — '.$invoice->invoice_number],
                    ['chart_of_account_id' => $balanceAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Deposit applied — '.$invoice->invoice_number],
                ];

            $this->ledger->post($tenant, [
                'entry_date' => now()->toDateString(),
                'reference' => $invoice->invoice_number,
                'source_type' => 'deposit_reclass',
                'source_id' => $invoice->id,
                'bullion_client_id' => $invoice->bullion_client_id,
                'currency_code' => $invoice->currency_code,
                'exchange_rate' => (float) $invoice->exchange_rate,
                'created_by' => $deposit->created_by,
            ], $journalLines);
        });
    }

    /**
     * Void a posted (or pending-fix) invoice: reverses each inventory movement it produced,
     * voids the journal entry if one was posted (via a reversing entry — never edits/deletes;
     * a pending-fix invoice has none yet, since fixPrice() hasn't run), and flags the linked
     * ClientTransaction rather than deleting it (AML records are never deleted).
     */
    public function void(Invoice $invoice, ?string $reason = null): Invoice
    {
        abort_if(! in_array($invoice->status, ['posted', 'pending_fix'], true), 422, 'Only posted or pending-fix invoices can be voided.');

        return DB::transaction(function () use ($invoice, $reason) {
            $movements = InventoryStockMovement::where('source_type', 'invoice')
                ->where('source_id', $invoice->id)
                ->get();

            foreach ($movements as $movement) {
                $this->inventory->reverseMovement($movement);
            }

            if ($invoice->journal_entry_id) {
                $this->ledger->void($invoice->journalEntry, $reason ?? ('Invoice '.$invoice->invoice_number.' voided'));
            }

            if ($invoice->clientTransaction) {
                $invoice->clientTransaction->update([
                    'notes' => trim(($invoice->clientTransaction->notes ?? '').' [VOIDED: '.($reason ?? 'no reason given').']'),
                ]);
            }

            $invoice->update(['status' => 'void', 'voided_at' => now()]);

            return $invoice->fresh();
        });
    }

    private function requireAccount(Tenant $tenant, string $subtype): ChartOfAccount
    {
        $account = $this->ledger->accountFor($tenant, $subtype);

        abort_if(! $account, 500, "Chart of accounts is missing a required '{$subtype}' account for tenant {$tenant->slug}.");

        return $account;
    }

    private function findOrCreateMiscItem(Tenant $tenant, string $metalType): InventoryItem
    {
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

    /**
     * Metal-agnostic amounts (an "other" fee line, an exchange's cash top-up) still need to
     * land in one metal's revenue/COGS bucket now that those accounts are per-metal. Attribute
     * them to the first metal actually present on the invoice — the realistic case is a
     * delivery fee riding along on an otherwise single-metal sale — falling back to gold only
     * if the invoice somehow has no metal lines at all.
     */
    private function primaryMetalType(Invoice $invoice): string
    {
        foreach ($invoice->lines as $line) {
            if (in_array($line->line_type, ['metal_in', 'metal_out'], true) && $line->inventoryItem) {
                return $line->inventoryItem->metal_type;
            }
        }

        return 'gold';
    }
}
