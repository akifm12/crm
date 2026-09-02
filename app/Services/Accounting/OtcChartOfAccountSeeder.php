<?php
// app/Services/Accounting/OtcChartOfAccountSeeder.php

namespace App\Services\Accounting;

use App\Models\OtcChartOfAccount;
use App\Models\Tenant;
use App\Support\OtcChartOfAccountTemplate;
use Illuminate\Support\Facades\DB;

class OtcChartOfAccountSeeder
{
    /** Idempotent per account code — safe to re-run after the template gains new accounts. */
    public function seedForTenant(Tenant $tenant): void
    {
        $rows = OtcChartOfAccountTemplate::get();

        DB::transaction(function () use ($tenant, $rows) {
            $codeToId = OtcChartOfAccount::where('tenant_id', $tenant->id)
                ->pluck('id', 'code')
                ->all();

            $ordered = collect($rows)->sortBy(fn ($row) => $row['parent_code'] === null ? 0 : 1);

            foreach ($ordered as $row) {
                if (isset($codeToId[$row['code']])) {
                    continue;
                }

                $account = OtcChartOfAccount::create([
                    'tenant_id'      => $tenant->id,
                    'parent_id'      => $row['parent_code'] ? ($codeToId[$row['parent_code']] ?? null) : null,
                    'code'           => $row['code'],
                    'name'           => $row['name'],
                    'type'           => $row['type'],
                    'subtype'        => $row['subtype'],
                    'normal_balance' => $row['normal_balance'],
                    'is_system'      => true,
                    'is_active'      => true,
                ]);

                $codeToId[$row['code']] = $account->id;
            }
        });
    }
}
