<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearManualJournalEntries extends Command
{
    // Single tenant only, by design — no "all tenants" mode, so there is no way to
    // accidentally clear more than the one slug given.
    protected $signature = 'tenant:clear-journal-entries
        {slug : The exact tenant slug to clear — no other tenant is ever touched}
        {--dry-run : Preview counts without deleting anything}
        {--force : Also delete non-manual entries (invoice/otc_deal/bill/etc. postings) — DANGEROUS, orphans the records that posted them}';

    protected $description = 'Delete manually-created journal entries for one tenant (a clean-slate reset for test entries) — by default refuses to touch entries posted by invoices, bills, or OTC deals';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');

        $tenant = Tenant::where('slug', $this->argument('slug'))->first();
        if (! $tenant) {
            $this->error("Tenant not found: {$this->argument('slug')}");
            return self::FAILURE;
        }

        $query = JournalEntry::where('tenant_id', $tenant->id);
        if (! $force) {
            $query->where('source_type', 'manual');
        }

        $targetIds = (clone $query)->pluck('id');
        $targetCount = $targetIds->count();

        // Always report what's being left behind (or, with --force, what's about to
        // be deleted) so this is never a silent surprise.
        $bySource = JournalEntry::where('tenant_id', $tenant->id)
            ->selectRaw('source_type, count(*) as c')
            ->groupBy('source_type')
            ->pluck('c', 'source_type');

        $this->info("Tenant: {$tenant->name} ({$tenant->slug})");
        $this->line('Journal entries by source_type:');
        foreach ($bySource as $type => $count) {
            $flag = (!$force && $type !== 'manual') ? '  <- SKIPPED (use --force to include)' : '';
            $this->line("  {$type}: {$count}{$flag}");
        }

        if ($targetCount === 0) {
            $this->info('Nothing to delete.');
            return self::SUCCESS;
        }

        $this->line('');
        $this->info(($dryRun ? '[DRY RUN] Would delete ' : 'Deleting ') . "{$targetCount} journal entr" . ($targetCount === 1 ? 'y' : 'ies') . ($force ? ' (including non-manual postings)' : ' (manual only)') . '.');

        if ($dryRun) {
            return self::SUCCESS;
        }

        if (! $this->confirm('Proceed?', true)) {
            $this->warn('Cancelled.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($targetIds) {
            JournalEntryLine::whereIn('journal_entry_id', $targetIds)->delete();
            JournalEntry::whereIn('id', $targetIds)->delete();
        });

        $this->info('Done.');

        return self::SUCCESS;
    }
}
