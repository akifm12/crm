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
                            {tenant    : Tenant slug}
                            {folder_id : Google Drive folder ID for this tenant}
                            {--dry-run : Preview matches without downloading anything}
                            {--threshold=70 : Minimum fuzzy-match score (0-100) to accept a match}
                            {--skip-existing : Skip clients that already have documents uploaded}';

    protected $description = 'Import client documents from a Google Drive folder into the portal';

    private \Google\Service\Drive $drive;

    public function handle(): int
    {
        // ── Tenant ────────────────────────────────────────────────────────
        $tenant = Tenant::where('slug', $this->argument('tenant'))->first();
        if (!$tenant) {
            $this->error("Tenant '{$this->argument('tenant')}' not found.");
            return 1;
        }

        $isDryRun   = $this->option('dry-run');
        $threshold  = (int) $this->option('threshold');
        $folderId   = $this->argument('folder_id');

        $this->info($isDryRun ? '🔍 DRY RUN — nothing will be saved' : '🚀 LIVE RUN — files will be downloaded and saved');
        $this->info("Tenant: {$tenant->name} ({$tenant->slug})");
        $this->line('');

        // ── Google Drive client ───────────────────────────────────────────
        $credPath = storage_path('app/google-service-account.json');
        if (!file_exists($credPath)) {
            $this->error("Service account key not found at: {$credPath}");
            return 1;
        }

        $client = new \Google\Client();
        $client->setAuthConfig($credPath);
        $client->addScope(\Google\Service\Drive::DRIVE_READONLY);
        $this->drive = new \Google\Service\Drive($client);

        // ── Load all clients for this tenant ──────────────────────────────
        $clients = BullionClient::where('tenant_id', $tenant->id)->get();
        $this->line("Portal clients: {$clients->count()}");

        // ── List numbered subfolders in the Drive folder ──────────────────
        $subfolders = $this->listFolders($folderId);
        $this->line("Drive subfolders found: " . count($subfolders));
        $this->line('');

        $matched   = 0;
        $unmatched = [];
        $skipped   = 0;
        $totalDocs = 0;

        $this->output->progressStart(count($subfolders));

        foreach ($subfolders as $folder) {
            $rawName    = $folder['name'];
            $clientName = $this->stripLeadingNumber($rawName);

            // Skip the tenant's own documents folder (folder 0)
            if (trim($clientName) === '' || $this->isZeroFolder($rawName)) {
                $this->output->progressAdvance();
                continue;
            }

            // Find best matching portal client
            $match = $this->findBestMatch($clientName, $clients, $threshold, $score);

            if (!$match) {
                $unmatched[] = ['drive' => $rawName, 'extracted' => $clientName];
                $this->output->progressAdvance();
                continue;
            }

            // Skip if already has documents and --skip-existing
            if ($this->option('skip-existing')) {
                $existing = ClientDocument::where('bullion_client_id', $match->id)->count();
                if ($existing > 0) {
                    $skipped++;
                    $this->output->progressAdvance();
                    continue;
                }
            }

            $matched++;

            // List files inside this client folder
            $files = $this->listFiles($folder['id']);
            $totalDocs += count($files);

            if ($isDryRun) {
                $this->output->progressAdvance();
                $this->line("\n  <fg=green>✓ MATCH ({$score}%)</> \"{$rawName}\"");
                $this->line("    → {$match->displayName()} (id: {$match->id})");
                foreach ($files as $f) {
                    $docType = $this->detectDocType($f['name']);
                    $this->line("    📄 {$f['name']} → <fg=cyan>{$docType}</>");
                }
                continue;
            }

            // ── Download and store each file ──────────────────────────────
            foreach ($files as $file) {
                try {
                    $docType  = $this->detectDocType($file['name']);
                    $label    = $this->docTypeLabel($docType, $file['name']);
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $safeName = Str::slug(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $ext;

                    $storagePath = "tenants/{$tenant->slug}/clients/{$match->id}/{$safeName}";

                    // Download from Drive
                    $response = $this->drive->files->get($file['id'], ['alt' => 'media']);
                    $content  = $response->getBody()->getContents();

                    Storage::put($storagePath, $content);

                    ClientDocument::firstOrCreate(
                        ['bullion_client_id' => $match->id, 'file_name' => $safeName],
                        [
                            'tenant_id'      => $tenant->id,
                            'document_type'  => $docType,
                            'document_label' => $label,
                            'file_path'      => $storagePath,
                            'mime_type'      => $file['mimeType'] ?? 'application/octet-stream',
                            'file_size'      => strlen($content),
                            'uploaded_by'    => null,
                        ]
                    );
                } catch (\Throwable $e) {
                    $this->newLine();
                    $this->warn("  ⚠ Failed to import {$file['name']}: {$e->getMessage()}");
                }
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();

        // ── Summary ───────────────────────────────────────────────────────
        $this->info("── Results " . ($isDryRun ? '(dry run) ' : '') . "──────────────────");
        $this->line("  Matched:       {$matched}");
        $this->line("  Skipped:       {$skipped}");
        $this->line("  Total docs:    {$totalDocs}");

        if ($unmatched) {
            $this->newLine();
            $this->warn("Unmatched Drive folders (" . count($unmatched) . ") — review and match manually:");
            foreach ($unmatched as $u) {
                $this->line("  • {$u['drive']}  →  extracted: \"{$u['extracted']}\"");
            }
        }

        return 0;
    }

    // ── Google Drive helpers ──────────────────────────────────────────────────

    private function listFolders(string $parentId): array
    {
        $results = [];
        $pageToken = null;

        do {
            $params = [
                'q'          => "'{$parentId}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                'fields'     => 'nextPageToken, files(id, name)',
                'orderBy'    => 'name',
                'pageSize'   => 200,
            ];
            if ($pageToken) $params['pageToken'] = $pageToken;

            $response  = $this->drive->files->listFiles($params);
            $results   = array_merge($results, $response->getFiles());
            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return array_map(fn($f) => ['id' => $f->getId(), 'name' => $f->getName()], $results);
    }

    private function listFiles(string $folderId): array
    {
        $results   = [];
        $pageToken = null;

        do {
            $params = [
                'q'        => "'{$folderId}' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false",
                'fields'   => 'nextPageToken, files(id, name, mimeType)',
                'pageSize' => 100,
            ];
            if ($pageToken) $params['pageToken'] = $pageToken;

            $response  = $this->drive->files->listFiles($params);
            $results   = array_merge($results, $response->getFiles());
            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return array_map(fn($f) => [
            'id'       => $f->getId(),
            'name'     => $f->getName(),
            'mimeType' => $f->getMimeType(),
        ], $results);
    }

    // ── Matching helpers ──────────────────────────────────────────────────────

    private function stripLeadingNumber(string $name): string
    {
        // Remove leading "1. " or "1 - " or "1 " patterns
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
        $driveNorm = strtolower(trim($driveName));

        foreach ($clients as $client) {
            $portalName = strtolower(trim($client->company_name ?? $client->full_name ?? ''));
            if (!$portalName) continue;

            similar_text($driveNorm, $portalName, $pct);
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

        if (str_contains($l, 'trade licen') || str_contains($l, 'trade licen')) return 'trade_license';
        if (str_contains($l, 'memorandum') || str_contains($l, ' moa'))                return 'moa';
        if (str_contains($l, 'certificate of incorp') || str_contains($l, 'incorp'))   return 'certificate_incorp';
        if (str_contains($l, 'source of fund'))                                         return 'source_of_funds';
        if (str_contains($l, 'bank statement'))                                         return 'bank_statement';
        if (str_contains($l, 'emirates id') || preg_match('/\beid\b/', $l))            return 'eid';
        if (str_contains($l, 'signatory') && str_contains($l, 'passport'))             return 'signatory_passport';
        if (str_contains($l, 'ubo') && str_contains($l, 'passport'))                   return 'ubo_passport';
        if (str_contains($l, 'shareholder') && str_contains($l, 'passport'))           return 'shareholder_passport';
        if (str_contains($l, 'passport'))                                               return 'passport';
        if (str_contains($l, 'proof of address'))                                       return 'proof_of_address';
        if (str_contains($l, 'visa'))                                                   return 'other';

        return 'other';
    }

    private function docTypeLabel(string $type, string $filename = ''): string
    {
        $map = [
            'trade_license'       => 'Trade licence',
            'moa'                 => 'Memorandum of Association (MoA)',
            'certificate_incorp'  => 'Certificate of incorporation',
            'signatory_passport'  => 'Authorised signatory passport',
            'signatory_eid'       => 'Authorised signatory Emirates ID',
            'shareholder_passport'=> 'Shareholder passport(s)',
            'ubo_passport'        => 'UBO passport(s)',
            'source_of_funds'     => 'Source of funds evidence',
            'bank_statement'      => 'Bank statement (3 months)',
            'passport'            => 'Passport',
            'eid'                 => 'Emirates ID',
            'proof_of_address'    => 'Proof of address',
            'other'               => 'Other document',
        ];

        return $map[$type] ?? pathinfo($filename, PATHINFO_FILENAME);
    }
}
