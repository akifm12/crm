<?php
// database/migrations/2026_08_07_000003_split_metal_accounts_by_type.php

use App\Models\ChartOfAccount;
use App\Models\Tenant;
use App\Services\Accounting\ChartOfAccountSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Rename the existing generic gold-only subtypes to their metal-specific form on
        // every tenant that already seeded a chart of accounts, then re-seed (now
        // idempotent per-code) so each such tenant picks up the 9 new silver/platinum/
        // palladium accounts without disturbing anything that already exists.
        ChartOfAccount::where('code', '1100')->where('subtype', 'inventory')->update(['subtype' => 'inventory_gold']);
        ChartOfAccount::where('code', '4010')->where('subtype', 'sales')->update(['subtype' => 'sales_gold']);
        ChartOfAccount::where('code', '5010')->where('subtype', 'cogs')->update(['subtype' => 'cogs_gold']);

        $seeder = app(ChartOfAccountSeeder::class);

        Tenant::all()->each(function (Tenant $tenant) use ($seeder) {
            if (ChartOfAccount::where('tenant_id', $tenant->id)->exists()) {
                $seeder->seedForTenant($tenant);
            }
        });
    }

    public function down(): void
    {
        // Not reversible in a meaningful way — the new metal-specific accounts may already
        // have real journal entries posted against them by the time anyone would roll back.
    }
};
