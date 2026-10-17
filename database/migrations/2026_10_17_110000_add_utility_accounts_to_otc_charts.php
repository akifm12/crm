<?php

use App\Models\OtcChartOfAccount;
use App\Models\Tenant;
use App\Services\Accounting\OtcChartOfAccountSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Add Utilities / Electricity / Internet accounts to existing B-Book charts (idempotent by code). */
    public function up(): void
    {
        $ids = OtcChartOfAccount::query()->distinct()->pluck('tenant_id');

        foreach (Tenant::whereIn('id', $ids)->get() as $tenant) {
            app(OtcChartOfAccountSeeder::class)->seedForTenant($tenant);
        }
    }

    public function down(): void
    {
    }
};
