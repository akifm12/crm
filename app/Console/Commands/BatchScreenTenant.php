<?php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\ScreeningLog;
use App\Models\Tenant;
use App\Services\SentinelService;
use Illuminate\Console\Command;

class BatchScreenTenant extends Command
{
    protected $signature = 'screening:batch
                            {tenant : Tenant slug}
                            {--limit=50 : Number of clients to screen in this run}
                            {--offset=0 : Skip this many clients (for batching)}
                            {--sleep=2 : Seconds to pause between each client (reduces API timeouts)}
                            {--all : Include already-screened clients (re-screen)}
                            {--dry-run : Show what would be screened without calling the API}';

    protected $description = 'Batch screen all unscreened clients for a tenant';

    public function __construct(private SentinelService $sentinel)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $slug   = $this->argument('tenant');
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            $this->error("Tenant '{$slug}' not found.");
            return 1;
        }

        $limit  = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        $query = BullionClient::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'inactive')
            ->orderBy('id');

        if (!$this->option('all')) {
            $query->whereNull('screening_date');
        }

        $total   = $query->count();
        $clients = $query->with('shareholders')->skip($offset)->take($limit)->get();

        if ($clients->isEmpty()) {
            $this->info('No clients to screen' . ($this->option('all') ? '' : ' (all already screened — use --all to re-screen') . '.');
            return 0;
        }

        $dryRun = $this->option('dry-run');

        $this->info("Tenant: {$tenant->name}");
        $this->info("Total unscreened: {$total} | This batch: {$clients->count()} (offset {$offset})" . ($dryRun ? ' [dry-run]' : ''));
        $remaining = $total - $offset - $clients->count();
        if ($remaining > 0) {
            $this->info("Remaining after this batch: {$remaining} — next run: --offset=" . ($offset + $limit));
        }
        $this->newLine();

        $pass  = 0;
        $fail  = 0;
        $sleep = max(0, (int) $this->option('sleep'));
        $bar   = $this->output->createProgressBar($clients->count());
        $bar->start();

        foreach ($clients as $client) {
            $isCorporate = $client->client_type !== 'individual';
            $allResults  = [];
            $failed      = false;

            if ($dryRun) {
                $this->newLine();
                $this->line("  [dry] {$client->displayName()} (" . ($isCorporate ? 'corporate' : 'individual') . ')');
                $bar->advance();
                $pass++;
                continue;
            }

            // ── Screen main subject ──────────────────────────────────────
            if ($isCorporate) {
                $result = $this->sentinel->screenEntity([
                    'query'            => (string) $client->company_name,
                    'country'          => (string) ($client->country_of_incorporation ?? 'UAE'),
                    'country_of_issue' => (string) ($client->country_of_incorporation ?? 'UAE'),
                    'license_number'   => (string) ($client->trade_license_no ?? ''),
                    'date_of_issue'    => $client->trade_license_issue?->format('Y-m-d') ?? '',
                ]);
            } else {
                $result = $this->sentinel->screenIndividual([
                    'query'       => (string) $client->full_name,
                    'country'     => (string) ($client->nationality ?? 'UAE'),
                    'nationality' => (string) ($client->nationality ?? ''),
                    'dob'         => $client->dob?->format('Y-m-d') ?? '',
                ]);
            }

            if (!$result['success']) {
                $this->newLine();
                $this->warn("  FAILED: {$client->displayName()} — " . ($result['error'] ?? 'unknown error'));
                $fail++;
                if ($sleep > 0) sleep($sleep);
                $bar->advance();
                continue;
            }

            $mainSummary = SentinelService::summarise($result['data']);
            $allResults[] = [
                'name'    => $isCorporate ? $client->company_name : $client->full_name,
                'role'    => $isCorporate ? 'Company' : 'Individual',
                'summary' => $mainSummary,
            ];

            // ── Screen shareholders ──────────────────────────────────────
            $shareholderResults = [];
            if ($isCorporate && $client->shareholders->count()) {
                foreach ($client->shareholders as $sh) {
                    if (empty($sh->name)) continue;

                    $shResult = $this->sentinel->screenIndividual([
                        'query'       => (string) $sh->name,
                        'country'     => (string) ($sh->nationality ?? 'UAE'),
                        'nationality' => (string) ($sh->nationality ?? ''),
                        'dob'         => $sh->dob?->format('Y-m-d') ?? '',
                    ]);

                    if ($shResult['success']) {
                        $shSummary = SentinelService::summarise($shResult['data']);
                        $shareholderResults[] = [
                            'name'    => $sh->name,
                            'role'    => $sh->is_ubo ? 'Shareholder / UBO' : 'Shareholder',
                            'summary' => $shSummary,
                        ];
                        $allResults[] = [
                            'name'    => $sh->name,
                            'role'    => $sh->is_ubo ? 'Shareholder / UBO' : 'Shareholder',
                            'summary' => $shSummary,
                        ];
                    }
                }
            }

            // ── Determine overall status ─────────────────────────────────
            $hasMatch      = collect($allResults)->contains(fn($r) => $r['summary']['status'] === 'match');
            $overallStatus = $hasMatch ? 'match' : 'clear';
            $totalHits     = collect($allResults)->sum(fn($r) => $r['summary']['total_hits'] ?? 0);
            $reference     = 'SCR-' . strtoupper(substr(md5($client->displayName() . now()), 0, 8));

            // ── Save to client record ────────────────────────────────────
            $client->update([
                'screening_status'    => $overallStatus,
                'screening_date'      => now(),
                'screening_reference' => $reference,
                'screening_result'    => array_merge($mainSummary, [
                    'shareholders'   => $shareholderResults,
                    'all_results'    => $allResults,
                    'screened_count' => count($allResults),
                ]),
            ]);

            // ── Log ──────────────────────────────────────────────────────
            ScreeningLog::create([
                'tenant_id'         => $tenant->id,
                'bullion_client_id' => $client->id,
                'screened_by'       => null,
                'query'             => $client->displayName(),
                'entity_type'       => $isCorporate ? 'entity' : 'individual',
                'status'            => $overallStatus,
                'total_hits'        => $totalHits,
                'source'            => 'kyc',
                'reference'         => $reference,
                'result'            => ['all_results' => $allResults],
            ]);

            $pass++;
            if ($sleep > 0) sleep($sleep);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Complete — {$pass} screened, {$fail} failed.");

        if ($fail > 0) {
            $this->warn("Check the warnings above for failed clients.");
        }

        return 0;
    }
}
