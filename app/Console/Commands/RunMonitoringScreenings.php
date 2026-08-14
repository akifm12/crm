<?php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\ScreeningLog;
use App\Services\SentinelService;
use Illuminate\Console\Command;

class RunMonitoringScreenings extends Command
{
    protected $signature   = 'monitoring:run {--tenant=* : Limit to specific tenant IDs}';
    protected $description = 'Re-screen clients that are due for ongoing monitoring';

    public function handle(SentinelService $sentinel): int
    {
        $query = BullionClient::with(['shareholders', 'tenant'])
            ->where('monitoring_enabled', true)
            ->where('monitoring_next_due_at', '<=', now());

        if ($tenantIds = $this->option('tenant')) {
            $query->whereIn('tenant_id', $tenantIds);
        }

        $clients = $query->get();

        if ($clients->isEmpty()) {
            $this->info('No clients due for monitoring.');
            return self::SUCCESS;
        }

        $this->info("Running monitoring for {$clients->count()} client(s)…");

        foreach ($clients as $client) {
            $this->screenClient($client, $sentinel);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    private function screenClient(BullionClient $client, SentinelService $sentinel): void
    {
        $isCorporate = $client->client_type !== 'individual';
        $allResults  = [];

        // Screen main subject
        if ($isCorporate) {
            $res = $sentinel->screenEntity([
                'query'            => (string) $client->company_name,
                'country'          => (string) ($client->country_of_incorporation ?? 'UAE'),
                'country_of_issue' => (string) ($client->country_of_incorporation ?? 'UAE'),
                'license_number'   => (string) ($client->trade_license_no ?? ''),
                'date_of_issue'    => $client->trade_license_issue?->format('Y-m-d') ?? '',
            ]);
        } else {
            $res = $sentinel->screenIndividual([
                'query'       => (string) $client->full_name,
                'country'     => (string) ($client->nationality ?? 'UAE'),
                'nationality' => (string) ($client->nationality ?? ''),
                'dob'         => $client->dob?->format('Y-m-d') ?? '',
            ]);
        }

        if (!$res['success']) {
            $this->warn("  [{$client->id}] {$client->displayName()} — screening failed: " . ($res['error'] ?? 'unknown'));
            return;
        }

        $mainSummary  = SentinelService::summarise($res['data']);
        $allResults[] = ['name' => $isCorporate ? $client->company_name : $client->full_name,
                         'role' => $isCorporate ? 'Company' : 'Individual', 'summary' => $mainSummary];

        // Screen shareholders
        if ($isCorporate) {
            foreach ($client->shareholders as $sh) {
                if (empty($sh->name)) continue;
                $shRes = $sentinel->screenIndividual([
                    'query'       => (string) $sh->name,
                    'country'     => (string) ($sh->nationality ?? 'UAE'),
                    'nationality' => (string) ($sh->nationality ?? ''),
                    'dob'         => $sh->dob?->format('Y-m-d') ?? '',
                ]);
                if ($shRes['success']) {
                    $shSummary    = SentinelService::summarise($shRes['data']);
                    $allResults[] = ['name' => $sh->name, 'role' => 'Shareholder', 'summary' => $shSummary];
                }
            }
        }

        $hasMatch     = collect($allResults)->contains(fn($r) => $r['summary']['status'] === 'match');
        $totalHits    = collect($allResults)->sum(fn($r) => $r['summary']['total_hits'] ?? 0);
        $overallStatus= $hasMatch ? 'match' : 'clear';
        $reference    = 'MON-' . strtoupper(substr(md5($client->id . now()), 0, 8));

        // Update client screening status
        $client->update([
            'screening_status'    => $overallStatus,
            'screening_date'      => now(),
            'screening_reference' => $reference,
            'screening_result'    => array_merge($mainSummary, [
                'all_results'    => $allResults,
                'screened_count' => count($allResults),
            ]),
            'monitoring_last_screened_at' => now(),
            'monitoring_next_due_at'      => $client->monitoringNextDue(),
        ]);

        // Log each subject
        foreach ($allResults as $r) {
            ScreeningLog::create([
                'tenant_id'         => $client->tenant_id,
                'bullion_client_id' => $client->id,
                'screened_by'       => null,
                'query'             => $client->displayName(),
                'entity_type'       => ($r['role'] === 'Company') ? 'entity' : 'individual',
                'status'            => $r['summary']['status'] ?? 'clear',
                'total_hits'        => $r['summary']['total_hits'] ?? 0,
                'source'            => 'monitoring',
                'reference'         => $reference,
                'result'            => array_merge($r['summary'], ['subject_name' => $r['name'], 'subject_role' => $r['role']]),
            ]);
        }

        $icon = $hasMatch ? '⚠' : '✓';
        $this->line("  {$icon} [{$client->id}] {$client->displayName()} — {$overallStatus}" . ($totalHits ? " ({$totalHits} hit(s))" : ''));
    }
}
