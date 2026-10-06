<?php

namespace App\Console\Commands;

use App\Models\ScreeningLog;
use App\Models\Tenant;
use App\Services\SentinelService;
use Illuminate\Console\Command;

class SyncSentinelDecisions extends Command
{
    protected $signature   = 'sentinel:sync';
    protected $description = "Bring Blue Arrow's review decisions from Sentinel into tenants' screening history";

    public function handle(SentinelService $sentinel): int
    {
        $tenantIds = ScreeningLog::whereNotNull('result->case_id')->distinct()->pluck('tenant_id');

        foreach (Tenant::whereIn('id', $tenantIds)->get() as $tenant) {
            $updated = $sentinel->syncTenant($tenant);
            if ($updated) $this->info("{$tenant->name}: {$updated} screening(s) updated");
        }
        return self::SUCCESS;
    }
}
