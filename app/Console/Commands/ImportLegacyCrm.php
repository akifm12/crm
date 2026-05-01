<?php
// app/Console/Commands/ImportLegacyCrm.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CrmClient;
use App\Models\CrmShareholder;

class ImportLegacyCrm extends Command
{
    protected $signature   = 'crm:import-legacy {--fresh : Drop existing CRM clients before importing}';
    protected $description = 'Import companies and shareholders from the legacy bamc database';

    public function handle(): void
    {
        $this->info('Connecting to legacy database...');

        try {
            $count = DB::connection('legacy')->table('companies')->count();
            $this->info("Found {$count} companies in legacy database.");
        } catch (\Exception $e) {
            $this->error('Could not connect to legacy database: ' . $e->getMessage());
            return;
        }

        if ($this->option('fresh')) {
            if ($this->confirm('This will delete all existing CRM clients. Are you sure?')) {
                CrmShareholder::query()->delete();
                CrmClient::query()->delete();
                $this->warn('Existing CRM clients cleared.');
            } else {
                $this->info('Aborted.');
                return;
            }
        }

        $companies   = DB::connection('legacy')->table('companies')->get();
        $shareholders= DB::connection('legacy')->table('shareholders')->get()->groupBy('company_id');

        $imported = 0;
        $skipped  = 0;

        $this->output->progressStart($companies->count());

        foreach ($companies as $company) {

            // Skip if already imported (by company name)
            if (CrmClient::where('company_name', $company->company_name)->exists()) {
                $skipped++;
                $this->output->progressAdvance();
                continue;
            }

            // Map stage from company_status
            $stage = match(strtolower($company->company_status ?? 'active')) {
                'active'   => 'active',
                'inactive' => 'inactive',
                'lead'     => 'lead',
                default    => 'active',
            };

            $client = CrmClient::create([
                'company_name'      => $company->company_name,
                'license_number'    => $company->license_number ?? null,
                'license_issue'     => $this->safeDate($company->license_issue),
                'license_expiry'    => $this->safeDate($company->license_expiry),
                'license_authority' => $company->license_authority ?? null,
                'legal_status'      => $company->legal_status ?? null,
                'country_inc'       => $company->country_inc ?? 'UAE',
                'regulator'         => $company->regulator ?? null,
                'ejari'             => $company->ejari ?? null,
                'trn'               => $company->trn ?? null,
                'address'           => $company->address ?? null,
                'contact_person'    => $company->contact_person ?? null,
                'telephone'         => $company->telephone ?? null,
                'email'             => $company->email ?? null,
                'website'           => $company->website ?? null,
                'stage'             => $stage,
                'status'            => $stage === 'active' ? 'active' : 'inactive',
                'portal_type'       => 'none',
            ]);

            // Import shareholders for this company
            $companyShares = $shareholders->get($company->id, collect());
            foreach ($companyShares as $sh) {
                CrmShareholder::create([
                    'crm_client_id'      => $client->id,
                    'shareholder_name'   => $sh->shareholder_name,
                    'birthdate'          => $this->safeDate($sh->birthdate),
                    'nationality'        => $sh->nationality ?? null,
                    'passport'           => $sh->passport ?? null,
                    'passport_expiry'    => $this->safeDate($sh->passport_expiry),
                    'ownership_percentage' => null,
                    'is_ubo'             => false,
                ]);
            }

            $imported++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info('');
        $this->info("✓ Import complete.");
        $this->info("  Imported : {$imported} companies");
        $this->info("  Skipped  : {$skipped} (already existed)");
        $this->info("  Shareholders imported with each company.");
    }

    private function safeDate(?string $date): ?string
    {
        if (!$date || $date === '0000-00-00') return null;
        try {
            return \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
