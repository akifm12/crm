<?php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\ScreeningLog;
use Illuminate\Console\Command;

class BackfillScreeningLogs extends Command
{
    protected $signature = 'screening:backfill {--dry-run : Show what would be inserted without writing}';
    protected $description = 'Backfill screening_logs from existing client screening_result data';

    public function handle(): int
    {
        $clients = BullionClient::whereNotNull('screening_date')
            ->whereNotNull('screening_status')
            ->get();

        if ($clients->isEmpty()) {
            $this->info('No screened clients found.');
            return 0;
        }

        $dryRun  = $this->option('dry-run');
        $skipped = 0;
        $created = 0;

        foreach ($clients as $client) {
            // Skip if a KYC log already exists for this client
            $exists = ScreeningLog::where('bullion_client_id', $client->id)
                ->where('source', 'kyc')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $result    = $client->screening_result ?? [];
            $totalHits = $result['total_hits'] ?? 0;

            if (!$dryRun) {
                ScreeningLog::create([
                    'tenant_id'         => $client->tenant_id,
                    'bullion_client_id' => $client->id,
                    'screened_by'       => null,
                    'query'             => $client->displayName(),
                    'entity_type'       => $client->client_type === 'individual' ? 'individual' : 'entity',
                    'status'            => $client->screening_status,
                    'total_hits'        => $totalHits,
                    'source'            => 'kyc',
                    'reference'         => $client->screening_reference,
                    'result'            => $result,
                    'created_at'        => $client->screening_date,
                    'updated_at'        => $client->screening_date,
                ]);
            }

            $this->line(($dryRun ? '[dry] ' : '') . "  {$client->displayName()} — {$client->screening_status} ({$totalHits} hits)");
            $created++;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Done: {$created} inserted, {$skipped} already had a log.");
        return 0;
    }
}
