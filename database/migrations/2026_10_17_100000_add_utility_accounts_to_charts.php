<?php

use App\Models\ChartOfAccount;
use App\Models\Tenant;
use App\Services\Accounting\ChartOfAccountSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Add Utilities / Electricity / Internet accounts to charts that already exist. */
    public function up(): void
    {
        $ids = ChartOfAccount::query()->distinct()->pluck('tenant_id');

        foreach (Tenant::whereIn('id', $ids)->get() as $tenant) {
            $codes = ChartOfAccount::where('tenant_id', $tenant->id)->pluck('name', 'code');

            $template = match (true) {
                $codes->has('5900') || $codes->contains('Cost of Goods Sold — Gold') => 'bullion_dealer',
                $codes->contains('Property Management Fees')                            => 'real_estate',
                default                                                                 => 'general_business',
            };

            // Existing "Utilities" account gets its lookup key so bills can post to it.
            ChartOfAccount::where('tenant_id', $tenant->id)->where('name', 'Utilities')->whereNull('subtype')->update(['subtype' => 'utilities']);

            (new ChartOfAccountSeeder)->seedForTenant($tenant, $template);
        }
    }

    public function down(): void
    {
    }
};
