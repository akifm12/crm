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

        $query = BullionClient::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'inactive');

        if (!$this->option('all')) {
            $query->whereNull('screening_date');
        }

        $clients = $query->with('shareholders')->get();

        if ($clients->isEmpty()) {
            $this->info('No clients to screen' . ($this->option('all') ? '' : ' (all already screened — use --all to re-screen') . '.');
            return 0;
        }

        $dryRun = $this->option('dry-run');

        $this->info("Tenant: {$tenant->name}");
        $this->info("Clients to screen: {$clients->count()}" . ($dryRun ? ' [dry-run]' : ''));
        $this->newLine();

        $pass = 0;
        $fail = 0;
        $bar  = $this->output->createProgressBar($clients->count());
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
