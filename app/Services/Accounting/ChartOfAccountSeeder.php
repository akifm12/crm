<?php
// app/Services/Accounting/ChartOfAccountSeeder.php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\Tenant;
use App\Support\ChartOfAccountTemplates;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder
{
    /**
     * Seed the tenant's chart of accounts from a template. Idempotent per account code —
     * safe to call again after the template gains new accounts (e.g. a new metal added
     * later); only codes the tenant doesn't already have get inserted, existing accounts
     * are left untouched.
     */
    public static function templateForModule(string $module): string
    {
        return match ($module) {
            'general_accounting'    => 'general_business',
            'real_estate_accounting'=> 'real_estate',
            default                 => 'bullion_dealer',
        };
    }

    public function seedForTenant(Tenant $tenant, string $template = 'bullion_dealer'): void
    {
        $rows = ChartOfAccountTemplates::get($template);

        if (empty($rows)) {
            return;
        }

        DB::transaction(function () use ($tenant, $rows) {
            $codeToId = ChartOfAccount::where('tenant_id', $tenant->id)
                ->pluck('id', 'code')
                ->all();

            // Parents (parent_code === null) must be inserted before children reference them.
            $ordered = collect($rows)->sortBy(fn ($row) => $row['parent_code'] === null ? 0 : 1);

            foreach ($ordered as $row) {
                if (isset($codeToId[$row['code']])) {
                    continue;
                }

                $account = ChartOfAccount::create([
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
