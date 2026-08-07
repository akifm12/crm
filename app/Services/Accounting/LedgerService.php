<?php

// app/Services/Accounting/LedgerService.php

namespace App\Services\Accounting;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Post a balanced journal entry. Balance is validated in entry currency (the source of
     * truth); base-currency (AED) amounts are derived per-line via the entry's exchange_rate.
     *
     * $header: entry_date, reference, source_type, source_id, bullion_client_id, currency_code, exchange_rate, memo, created_by
     * $lines:  array of [chart_of_account_id, debit, credit, description]
     *
     * @throws UnbalancedJournalEntryException
     */
    public function post(Tenant $tenant, array $header, array $lines): JournalEntry
    {
        $lines = array_values(array_filter($lines, fn ($l) => ($l['debit'] ?? 0) > 0 || ($l['credit'] ?? 0) > 0));

        $totalDebit = round(array_sum(array_map(fn ($l) => (float) ($l['debit'] ?? 0), $lines)), 2);
        $totalCredit = round(array_sum(array_map(fn ($l) => (float) ($l['credit'] ?? 0), $lines)), 2);

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw UnbalancedJournalEntryException::forTotals($totalDebit, $totalCredit);
        }

        return DB::transaction(function () use ($tenant, $header, $lines) {
            $exchangeRate = (float) ($header['exchange_rate'] ?? 1);

            $entry = JournalEntry::create([
                'tenant_id' => $tenant->id,
                'entry_date' => $header['entry_date'],
                'reference' => $header['reference'] ?? null,
                'source_type' => $header['source_type'] ?? 'manual',
                'source_id' => $header['source_id'] ?? null,
                'bullion_client_id' => $header['bullion_client_id'] ?? null,
                'currency_code' => $header['currency_code'] ?? 'AED',
                'exchange_rate' => $exchangeRate,
                'status' => 'posted',
                'created_by' => $header['created_by'] ?? null,
                'memo' => $header['memo'] ?? null,
            ]);

            foreach ($lines as $i => $line) {
                $debit = round((float) ($line['debit'] ?? 0), 2);
                $credit = round((float) ($line['credit'] ?? 0), 2);

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $line['chart_of_account_id'],
                    'debit' => $debit,
                    'credit' => $credit,
                    'base_debit' => round($debit * $exchangeRate, 2),
                    'base_credit' => round($credit * $exchangeRate, 2),
                    'description' => $line['description'] ?? null,
                    'line_order' => $i,
                ]);
            }

            return $entry->fresh('lines');
        });
    }

    /**
     * Void a posted entry by writing a mirrored reversing entry — never edits or deletes
     * the original (required for audit trail / FTA record-keeping).
     */
    public function void(JournalEntry $entry, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($entry, $reason) {
            $reversedLines = $entry->lines->map(fn (JournalEntryLine $line) => [
                'chart_of_account_id' => $line->chart_of_account_id,
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
                'description' => $line->description,
            ])->all();

            $reversal = $this->post($entry->tenant, [
                'entry_date' => now()->toDateString(),
                'reference' => 'Void of journal entry #'.$entry->id,
                'source_type' => $entry->source_type,
                'source_id' => $entry->source_id,
                'bullion_client_id' => $entry->bullion_client_id,
                'currency_code' => $entry->currency_code,
                'exchange_rate' => (float) $entry->exchange_rate,
                'memo' => $reason,
                'created_by' => $entry->created_by,
            ], $reversedLines);

            $entry->update([
                'status' => 'void',
                'voided_journal_entry_id' => $reversal->id,
            ]);

            return $reversal;
        });
    }

    /**
     * Look up a tenant's designated account for a given subtype (e.g. 'inventory', 'cogs',
     * 'vat_payable') — the mechanism other services use to post without hardcoding account IDs.
     */
    public function accountFor(Tenant $tenant, string $subtype): ?ChartOfAccount
    {
        return ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('subtype', $subtype)
            ->where('is_active', true)
            ->first();
    }
}
