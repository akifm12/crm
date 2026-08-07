<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Accounting\ChartOfAccountSeeder;
use Illuminate\Console\Command;

class SeedChartOfAccounts extends Command
{
    protected $signature = 'tenant:seed-coa
                            {tenant : Tenant slug}
                            {--template=bullion_dealer : Chart of accounts template to seed}';

    protected $description = 'Seed the default chart of accounts for a tenant (idempotent — no-op if accounts already exist)';

    public function __construct(private ChartOfAccountSeeder $seeder)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $slug = $this->argument('tenant');
        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant) {
            $this->error("No tenant found with slug '{$slug}'.");
            return self::FAILURE;
        }

        $this->seeder->seedForTenant($tenant, $this->option('template'));

        $this->info("Chart of accounts seeded for {$tenant->name} ({$slug}).");
        return self::SUCCESS;
    }
}
