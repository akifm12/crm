<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Accounting\OtcChartOfAccountSeeder;
use Illuminate\Console\Command;

class SeedOtcChartOfAccounts extends Command
{
    protected $signature = 'tenant:seed-otc-coa {tenant : Tenant slug}';

    protected $description = 'Seed the default B-Book (OTC) chart of accounts for a tenant (idempotent — no-op if accounts already exist)';

    public function __construct(private OtcChartOfAccountSeeder $seeder)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $slug   = $this->argument('tenant');
        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant) {
            $this->error("No tenant found with slug '{$slug}'.");
            return self::FAILURE;
        }

        $this->seeder->seedForTenant($tenant);

        $this->info("B-Book chart of accounts seeded for {$tenant->name} ({$slug}).");
        return self::SUCCESS;
    }
}
