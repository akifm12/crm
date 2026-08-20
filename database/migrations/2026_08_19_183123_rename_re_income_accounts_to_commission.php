<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find tenants with the real_estate_accounting module enabled
        $tenantIds = DB::table('tenants')
            ->whereRaw("JSON_EXTRACT(settings, '$.enabled_modules.real_estate_accounting') = true")
            ->pluck('id');

        if ($tenantIds->isEmpty()) {
            return;
        }

        $renames = [
            'Rental Income'         => 'Commission Income — Rental',
            'Service Charge Income' => 'Commission Income — Sales',
            'Other Property Income' => 'Other Commission Income',
        ];

        foreach ($renames as $oldName => $newName) {
            DB::table('chart_of_accounts')
                ->whereIn('tenant_id', $tenantIds)
                ->where('name', $oldName)
                ->update(['name' => $newName]);
        }
    }

    public function down(): void
    {
        $tenantIds = DB::table('tenants')
            ->whereRaw("JSON_EXTRACT(settings, '$.enabled_modules.real_estate_accounting') = true")
            ->pluck('id');

        if ($tenantIds->isEmpty()) {
            return;
        }

        $restores = [
            'Commission Income — Rental' => 'Rental Income',
            'Commission Income — Sales'  => 'Service Charge Income',
            'Other Commission Income'          => 'Other Property Income',
        ];

        foreach ($restores as $currentName => $originalName) {
            DB::table('chart_of_accounts')
                ->whereIn('tenant_id', $tenantIds)
                ->where('name', $currentName)
                ->update(['name' => $originalName]);
        }
    }
};
