<?php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\JournalEntry;
use App\Models\StandardInvoice;
use App\Models\Tenant;
use Illuminate\Console\Command;

class BackfillStandardInvoiceClients extends Command
{
    protected $signature = 'invoices:backfill-client-links
        {tenant? : Tenant slug (omit to run across every tenant)}
        {--dry-run : Preview matches without writing anything}';

    protected $description = 'Link existing standard invoices (free-text client_name only) to their BullionClient record by exact name match, and backfill the client tag on their journal entries so historical postings appear on the client statement';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $tenants = $this->argument('tenant')
            ? Tenant::where('slug', $this->argument('tenant'))->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->error('No matching tenant found.');
            return self::FAILURE;
        }

        $totalLinked = 0;
        $totalUnmatched = 0;

        foreach ($tenants as $tenant) {
            $invoices = StandardInvoice::where('tenant_id', $tenant->id)->whereNull('bullion_client_id')->get();
            if ($invoices->isEmpty()) {
                continue;
            }

            $clients = BullionClient::where('tenant_id', $tenant->id)->get(['id', 'company_name', 'full_name']);

            $linked = 0;
            $unmatched = [];

            foreach ($invoices as $invoice) {
                $match = $this->matchClient($invoice->client_name, $clients);

                if (! $match) {
                    $unmatched[] = "{$invoice->invoice_number}: \"{$invoice->client_name}\"";
                    continue;
                }

                $label = $match->company_name ?: $match->full_name;
                $this->line("  {$invoice->invoice_number}: \"{$invoice->client_name}\" → {$label} (#{$match->id})");
                $linked++;

                if ($dryRun) {
                    continue;
                }

                $invoice->update(['bullion_client_id' => $match->id]);

                // Backfill the client tag on this invoice's journal entries too — the
                // statement is built from JournalEntry.bullion_client_id, not the invoice
                // link alone, so historical postings need the same tag to show up.
                JournalEntry::where('tenant_id', $tenant->id)
                    ->whereIn('source_type', ['standard_invoice', 'standard_invoice_payment'])
                    ->where('source_id', $invoice->id)
                    ->update(['bullion_client_id' => $match->id]);
            }

            if ($linked > 0 || count($unmatched) > 0) {
                $this->info(($dryRun ? '[DRY RUN] ' : '') . "{$tenant->name}: linked {$linked}, unmatched " . count($unmatched));
            }

            foreach ($unmatched as $u) {
                $this->warn("    ✗ {$u}");
            }

            $totalLinked += $linked;
            $totalUnmatched += count($unmatched);
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would link' : 'Linked') . ": {$totalLinked}   Unmatched (needs manual pick on the invoice edit screen): {$totalUnmatched}");

        if ($dryRun) {
            $this->comment('Dry run only — nothing was written. Re-run without --dry-run to commit.');
        }

        return self::SUCCESS;
    }

    private function matchClient(?string $name, $clients): ?BullionClient
    {
        if (! $name) {
            return null;
        }

        $target = $this->normalize($name);

        foreach ($clients as $client) {
            foreach (array_filter([$client->company_name, $client->full_name]) as $candidate) {
                if ($this->normalize($candidate) === $target) {
                    return $client;
                }
            }
        }

        return null;
    }

    private function normalize(string $name): string
    {
        $name = strtolower(str_replace(['.', ','], '', $name));

        return trim(preg_replace('/\s+/', ' ', $name));
    }
}
