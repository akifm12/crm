<?php

// app/Services/Accounting/OtcLedgerService.php — mirrors LedgerService, targeting
// the separate otc_* ledger tables. See migration for why B-Book's ledger is
// isolated from A-Book's rather than sharing chart_of_accounts/journal_entries.

namespace App\Services\Accounting;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\OtcChartOfAccount;
use App\Models\OtcJournalEntry;
use App\Models\OtcJournalEntryLine;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class OtcLedgerService
{
    /**
     * $header: entry_date, reference, source_type, source_id, bullion_client_id, currency_code, exchange_rate, memo, created_by
     * $lines:  array of [otc_chart_of_account_id, debit, credit, description]
     *
     * @throws UnbalancedJournalEntryException
     */
    public function post(Tenant $tenant, array $header, array $lines): OtcJournalEntry
    {
        $lines = array_values(array_filter($lines, fn ($l) => ($l['debit'] ?? 0) > 0 || ($l['credit'] ?? 0) > 0));

        $totalDebit = round(array_sum(array_map(fn ($l) => (float) ($l['debit'] ?? 0), $lines)), 2);
        $totalCredit = round(array_sum(array_map(fn ($l) => (float) ($l['credit'] ?? 0), $lines)), 2);

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw UnbalancedJournalEntryException::forTotals($totalDebit, $totalCredit);
        }

        return DB::transaction(function () use ($tenant, $header, $lines) {
            $exchangeRate = (float) ($header['exchange_rate'] ?? 1);

            $entry = OtcJournalEntry::create([
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

                OtcJournalEntryLine::create([
                    'otc_journal_entry_id' => $entry->id,
                    'otc_chart_of_account_id' => $line['otc_chart_of_account_id'],
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

    public function void(OtcJournalEntry $entry, ?string $reason = null): OtcJournalEntry
    {
        return DB::transaction(function () use ($entry, $reason) {
            $reversedLines = $entry->lines->map(fn (OtcJournalEntryLine $line) => [
                'otc_chart_of_account_id' => $line->otc_chart_of_account_id,
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
                'description' => $line->description,
            ])->all();

            $reversal = $this->post($entry->tenant, [
                'entry_date' => now()->toDateString(),
                'reference' => 'Void of B-Book journal entry #'.$entry->id,
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

    public function accountFor(Tenant $tenant, string $subtype): ?OtcChartOfAccount
    {
        return OtcChartOfAccount::where('tenant_id', $tenant->id)
            ->where('subtype', $subtype)
            ->where('is_active', true)
            ->first();
    }
}
