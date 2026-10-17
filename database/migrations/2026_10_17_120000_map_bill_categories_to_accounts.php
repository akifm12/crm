<?php

use App\Models\ChartOfAccount;
use App\Models\OtcChartOfAccount;
use App\Models\Tenant;
use App\Services\Accounting\ChartOfAccountSeeder;
use App\Services\Accounting\OtcChartOfAccountSeeder;
use App\Support\ChartOfAccountTemplates;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Give existing expense accounts the lookup key bills post by, and add the accounts
     * that were missing (insurance, travel, office supplies, ...). Existing accounts are
     * matched on code AND name, so anything a client renamed or re-purposed is left alone.
     */
    public function up(): void
    {
        $ids = ChartOfAccount::query()->distinct()->pluck('tenant_id');

        foreach (Tenant::whereIn('id', $ids)->get() as $tenant) {
            $names = ChartOfAccount::where('tenant_id', $tenant->id)->pluck('name', 'code');

            $template = match (true) {
                $names->has('5900') || $names->contains('Cost of Goods Sold — Gold') => 'bullion_dealer',
                $names->contains('Property Management Fees')                            => 'real_estate',
                default                                                                 => 'general_business',
            };

            foreach (ChartOfAccountTemplates::get($template) as $row) {
                if ($row['type'] !== 'expense' || ! $row['subtype']) continue;
                ChartOfAccount::where('tenant_id', $tenant->id)->where('code', $row['code'])
                    ->where('name', $row['name'])->whereNull('subtype')->update(['subtype' => $row['subtype']]);
            }

            (new ChartOfAccountSeeder)->seedForTenant($tenant, $template);
        }

        foreach (Tenant::whereIn('id', OtcChartOfAccount::query()->distinct()->pluck('tenant_id'))->get() as $tenant) {
            app(OtcChartOfAccountSeeder::class)->seedForTenant($tenant);
        }
    }

    public function down(): void
    {
    }
};
