<?php
// app/Console/Commands/ImportDriveDocs.php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportDriveDocs extends Command
{
    protected $signature = 'drive:import
                            {tenant      : Tenant slug}
                            {source_path : Absolute path to the tenant\'s folder on this server}
                            {--folder=   : Only process this one subfolder name (for testing)}
                            {--dry-run   : Preview without saving anything}
                            {--overwrite : Replace files that already exist}
                            {--threshold=70 : Fuzzy match minimum score (0-100)}';

    protected $description = 'Import client documents from a local folder into the portal';

    public function handle(): int
    {
        // ── Tenant ────────────────────────────────────────────────────────
        $tenant = Tenant::where('slug', $this->argument('tenant'))->first();
        if (!$tenant) {
            $this->error("Tenant '{$this->argument('tenant')}' not found.");
            return 1;
        }

        $sourcePath = rtrim($this->argument('source_path'), '/\\');
        if (!is_dir($sourcePath)) {
            $this->error("Source path not found: {$sourcePath}");
            return 1;
        }

        $isDryRun  = $this->option('dry-run');
        $overwrite = $this->option('overwrite');
        $threshold = (int) $this->option('threshold');
        $onlyFolder = $this->option('folder');

        $this->line('');
        $this->info($isDryRun ? '🔍  DRY RUN — nothing will be saved' : '🚀  LIVE — files will be saved');
        $this->info("Tenant  : {$tenant->name}");
        $this->info("Source  : {$sourcePath}");
        if ($onlyFolder) $this->info("Filter  : {$onlyFolder}");
        $this->line('');

        // ── Load clients ──────────────────────────────────────────────────
        $clients = BullionClient::where('tenant_id', $tenant->id)->get();

        // ── Scan subfolders ───────────────────────────────────────────────
        $subfolders = $this->scanSubfolders($sourcePath, $onlyFolder);

        if (empty($subfolders)) {
            $this->warn('No client subfolders found.');
            return 0;
        }

        $stats = ['matched' => 0, 'unmatched' => 0, 'new' => 0, 'skipped' => 0, 'overwritten' => 0, 'errors' => 0];
        $unmatched = [];

        foreach ($subfolders as $folderName) {
            $folderPath = $sourcePath . DIRECTORY_SEPARATOR . $folderName;
            $clientName = $this->stripLeadingNumber($folderName);

            // Skip folder 0 (tenant's own documents)
            if ($this->isZeroFolder($folderName)) {
                $this->line("<fg=gray>  SKIP  0-folder: {$folderName}</>");
                continue;
            }

            // Match to portal client
            $client = $this->findBestMatch($clientName, $clients, $threshold, $score);

            if (!$client) {
                $stats['unmatched']++;
                $unmatched[] = $folderName;
                $this->line("<fg=yellow>  NO MATCH  {$folderName}</>");
                continue;
            }

            $stats['matched']++;
            $tradeLicense = trim($client->trade_license_no ?? '');
            $storagePath  = $tradeLicense
                ? "shared/entities/{$tradeLicense}"
                : "tenants/{$tenant->slug}/clients/{$client->id}";

            $this->line('');
            $this->line("<fg=green>  ✓ MATCH ({$score}%)  {$folderName}</>");
            $this->line("    → {$client->displayName()} | storage: {$storagePath}");

            // List files in folder
            $files = $this->scanFiles($folderPath);

            if (empty($files)) {
                $this->line("    <fg=gray>(no files)</>");
                continue;
            }

            foreach ($files as $fileName) {
                $filePath = $folderPath . DIRECTORY_SEPARATOR . $fileName;
                $docType  = $this->detectDocType($fileName);
                $ext      = pathinfo($fileName, PATHINFO_EXTENSION);
                $safeName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . ($ext ? '.' . strtolower($ext) : '');
                $destPath = $storagePath . '/' . $safeName;

                // Check for existing record
                $existing = ClientDocument::where('bullion_client_id', $client->id)
                    ->where('tenant_id', $tenant->id)
                    ->where('file_name', $safeName)
                    ->first();

                if ($existing && !$overwrite) {
                    $stats['skipped']++;
                    $this->line("    <fg=gray>  SKIP  {$fileName} (already exists)</>");
                    continue;
                }

                $tag = $existing ? 'OVERWRITE' : 'NEW';
                $this->line("    <fg=cyan>  {$tag}  {$fileName}  →  {$docType}</>");

                if ($isDryRun) continue;

                // ── Save file ─────────────────────────────────────────────
                try {
                    $content = file_get_contents($filePath);
                    Storage::put($destPath, $content);

                    $attrs = [
                        'tenant_id'      => $tenant->id,
                        'document_type'  => $docType,
                        'document_label' => $this->docTypeLabel($docType, $fileName),
                        'file_path'      => $destPath,
                        'file_name'      => $safeName,
                        'mime_type'      => mime_content_type($filePath) ?: 'application/octet-stream',
                        'file_size'      => filesize($filePath),
                        'uploaded_by'    => null,
                    ];

                    if ($existing) {
                        $existing->update($attrs);
                        $stats['overwritten']++;
                    } else {
                        ClientDocument::create(array_merge(['bullion_client_id' => $client->id], $attrs));
                        $stats['new']++;
                    }
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $this->warn("    ⚠  Failed: {$e->getMessage()}");
                }
            }
        }

        // ── Summary ───────────────────────────────────────────────────────
        $this->line('');
        $this->info('── Summary ' . ($isDryRun ? '(dry run) ' : '') . str_repeat('─', 30));
        $this->line("  Matched    : {$stats['matched']}");
        $this->line("  New docs   : {$stats['new']}");
        $this->line("  Skipped    : {$stats['skipped']}");
        $this->line("  Overwritten: {$stats['overwritten']}");
        $this->line("  Errors     : {$stats['errors']}");

        if ($unmatched) {
            $this->line('');
            $this->warn('Unmatched folders (' . count($unmatched) . '):');
            foreach ($unmatched as $u) $this->line("  • {$u}");
        }

        return 0;
    }

    // ── Filesystem helpers ────────────────────────────────────────────────────

    private function scanSubfolders(string $path, ?string $only): array
    {
        $entries = array_values(array_filter(
            scandir($path),
            fn($e) => $e !== '.' && $e !== '..' && is_dir($path . DIRECTORY_SEPARATOR . $e)
        ));

        if ($only) {
            $entries = array_filter($entries, fn($e) => stripos($e, $only) !== false);
        }

        return array_values($entries);
    }

    private function scanFiles(string $path): array
    {
        return array_values(array_filter(
            scandir($path),
            fn($e) => $e !== '.' && $e !== '..' && is_file($path . DIRECTORY_SEPARATOR . $e)
        ));
    }

    private function stripLeadingNumber(string $name): string
    {
        return trim(preg_replace('/^\d+[\.\-\s]+/', '', $name));
    }

    private function isZeroFolder(string $name): bool
    {
        return (bool) preg_match('/^0[\.\-\s]/i', trim($name));
    }

    private function findBestMatch(string $driveName, $clients, int $threshold, &$bestScore = 0): ?BullionClient
    {
        $best      = null;
        $bestScore = 0;
        $needle    = strtolower(trim($driveName));

        foreach ($clients as $client) {
            $haystack = strtolower(trim($client->company_name ?? $client->full_name ?? ''));
            if (!$haystack) continue;

            similar_text($needle, $haystack, $pct);
            $pct = (int) round($pct);

            if ($pct > $bestScore) {
                $bestScore = $pct;
                $best      = $client;
            }
        }

        return $bestScore >= $threshold ? $best : null;
    }

    // ── Document type detection ───────────────────────────────────────────────

    private function detectDocType(string $filename): string
    {
        $l = strtolower($filename);

        if (str_contains($l, 'trade licen'))                                          return 'trade_license';
        if (str_contains($l, 'memorandum') || preg_match('/\bmoa\b/', $l))           return 'moa';
        if (str_contains($l, 'certificate of incorp') || str_contains($l, 'incorp')) return 'certificate_incorp';
        if (str_contains($l, 'source of fund'))                                       return 'source_of_funds';
        if (str_contains($l, 'bank statement'))                                       return 'bank_statement';
        if (str_contains($l, 'emirates id') || preg_match('/\beid\b/', $l))          return 'eid';
        if (str_contains($l, 'signatory') && str_contains($l, 'passport'))           return 'signatory_passport';
        if (str_contains($l, 'ubo') && str_contains($l, 'passport'))                 return 'ubo_passport';
        if (str_contains($l, 'shareholder') && str_contains($l, 'passport'))         return 'shareholder_passport';
        if (str_contains($l, 'passport'))                                             return 'passport';
        if (str_contains($l, 'proof of address'))                                     return 'proof_of_address';

        return 'other';
    }

    private function docTypeLabel(string $type, string $filename = ''): string
    {
        return [
            'trade_license'        => 'Trade licence',
            'moa'                  => 'Memorandum of Association (MoA)',
            'certificate_incorp'   => 'Certificate of incorporation',
            'signatory_passport'   => 'Authorised signatory passport',
            'signatory_eid'        => 'Authorised signatory Emirates ID',
            'shareholder_passport' => 'Shareholder passport(s)',
            'ubo_passport'         => 'UBO passport(s)',
            'source_of_funds'      => 'Source of funds evidence',
            'bank_statement'       => 'Bank statement (3 months)',
            'passport'             => 'Passport',
            'eid'                  => 'Emirates ID',
            'proof_of_address'     => 'Proof of address',
            'other'                => 'Other document',
        ][$type] ?? pathinfo($filename, PATHINFO_FILENAME);
    }
}
