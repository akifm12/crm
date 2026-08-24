<?php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class ImportClientDocuments extends Command
{
    protected $signature = 'clients:import-documents
        {tenant : Tenant slug}
        {path : Absolute path to the folder containing per-client subfolders}
        {--folder= : Name of the single client subfolder to import (skips the interactive picker)}
        {--dry-run : Preview matches and files without writing anything}';

    protected $description = 'Import trade licence / MOA / passport / EID / ejari / visa files for one client folder at a time into the matching client profile';

    // Junk files/dirs Google Drive / macOS / Windows leave behind — always skipped.
    private const IGNORE_NAMES = ['.ds_store', 'thumbs.db', 'desktop.ini', '.gitkeep'];

    // Keyword => [document_type, label, singular]. Checked in order; first match wins.
    // "singular" types (one expected per client) are overwritten by type alone.
    // Non-singular types (multiple people) are deduped by type + filename.
    private const RULES = [
        ['keywords' => ['TRADELICEN', 'TRADELICENCE', 'TRADELICENSE'], 'type' => 'trade_license', 'label' => 'Trade Licence', 'singular' => true],
        ['keywords' => ['EJARI'],                                       'type' => 'ejari',         'label' => 'Ejari',         'singular' => true],
        ['keywords' => ['MOA', 'MEMORANDUMOFASSOCIATION'],               'type' => 'moa',           'label' => 'MOA',           'singular' => true],
        ['keywords' => ['VISA'],                                         'type' => 'other',         'label' => 'Visa',          'singular' => false],
        ['keywords' => ['EMIRATESID', 'EID'],                            'type' => 'eid',           'label' => 'Emirates ID',   'singular' => false],
        ['keywords' => ['PASSPORT'],                                     'type' => 'passport',      'label' => 'Passport',      'singular' => false],
    ];

    // Only generic corporate-entity suffixes are stripped for matching — industry
    // words like "trading"/"gold"/"jewellery" are kept, since in this sector almost
    // every company shares them and stripping would cause false-positive matches.
    private const LEGAL_SUFFIXES = ['l.l.c', 'llc', 'fzco', 'fze', 'dmcc', 'llp'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $root   = rtrim($this->argument('path'), '/\\');

        if (! is_dir($root)) {
            $this->error("Path not found: {$root}");
            return self::FAILURE;
        }

        $tenant = Tenant::where('slug', $this->argument('tenant'))->first();
        if (! $tenant) {
            $this->error("Tenant not found: {$this->argument('tenant')}");
            return self::FAILURE;
        }

        $clients = BullionClient::where('tenant_id', $tenant->id)->get(['id', 'company_name', 'full_name']);
        if ($clients->isEmpty()) {
            $this->error("Tenant '{$tenant->name}' has no clients on file.");
            return self::FAILURE;
        }

        $this->info("Tenant: {$tenant->name} ({$clients->count()} clients on file)");
        $this->line("Source: {$root}\n");

        $folders = collect(scandir($root))
            ->reject(fn ($f) => in_array($f, ['.', '..']))
            ->filter(fn ($f) => is_dir($root . DIRECTORY_SEPARATOR . $f))
            ->sort()
            ->values();

        if ($folders->isEmpty()) {
            $this->error('No subfolders found under that path.');
            return self::FAILURE;
        }

        // ── Pick exactly one client folder ──────────────────────────────────
        $folderName = $this->option('folder');
        if ($folderName) {
            if (! $folders->contains($folderName)) {
                $this->error("Folder not found under source path: \"{$folderName}\"");
                return self::FAILURE;
            }
        } else {
            $folderName = $this->choice(
                'Which client folder do you want to import?',
                $folders->all(),
            );
        }

        $client = $this->matchClient($folderName, $clients);
        if (! $client) {
            $this->error("No client record matches \"{$folderName}\" in this tenant.");
            $this->line('Check the spelling, or create the client first, then re-run.');
            return self::FAILURE;
        }

        $label = $client->company_name ?: $client->full_name;
        $this->info("✓ \"{$folderName}\" → {$label} (client #{$client->id})");
        $this->newLine();

        // ── Build the list of files this run would touch ───────────────────
        $folderPath = $root . DIRECTORY_SEPARATOR . $folderName;
        $finder = Finder::create()->files()->in($folderPath)->ignoreDotFiles(true)->ignoreVCS(true);

        $plan = [];
        $skippedFiles = 0;

        foreach ($finder as $file) {
            $filename = $file->getFilename();

            if (in_array(strtolower($filename), self::IGNORE_NAMES)) {
                continue;
            }

            $rule = $this->classify($filename);
            if (! $rule) {
                $skippedFiles++;
                continue;
            }

            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'])) {
                $skippedFiles++;
                continue;
            }

            // Dedupe: singular types overwrite by (client, type); multi types by (client, type, filename).
            $existingQuery = ClientDocument::where('bullion_client_id', $client->id)
                ->where('document_type', $rule['type']);
            if (! $rule['singular']) {
                $existingQuery->where('file_name', $filename);
            }

            $plan[] = ['file' => $file, 'rule' => $rule, 'existing' => $existingQuery->first(), 'filename' => $filename];
        }

        if (empty($plan)) {
            $this->warn('No matching document files (trade licence / ejari / MOA / passport / EID / visa) found in this folder.');
            $this->line("Files skipped (not a target type): {$skippedFiles}");
            return self::SUCCESS;
        }

        $this->line('Files to import:');
        foreach ($plan as $p) {
            $this->line("    [{$p['rule']['label']}] {$p['filename']}" . ($p['existing'] ? ' (will overwrite existing)' : ' (new)'));
        }
        $this->line("Other files in folder skipped (not a target type): {$skippedFiles}");
        $this->newLine();

        if ($dryRun) {
            $this->comment('Dry run only — nothing was written.');
            return self::SUCCESS;
        }

        if (! $this->confirm("Import these " . count($plan) . " file(s) for {$label}?", true)) {
            $this->comment('Cancelled — nothing was written.');
            return self::SUCCESS;
        }

        $imported = 0; $overwritten = 0;

        foreach ($plan as $p) {
            $file = $p['file'];
            $rule = $p['rule'];
            $existing = $p['existing'];
            $filename = $p['filename'];

            $mime = mime_content_type($file->getRealPath()) ?: 'application/octet-stream';
            $storedPath = Storage::disk('local')->putFile(
                "tenants/{$tenant->id}/clients/{$client->id}",
                $file->getRealPath()
            );

            if ($existing) {
                Storage::disk('local')->delete($existing->file_path);
                $existing->update([
                    'document_label' => $rule['label'],
                    'file_path'      => $storedPath,
                    'file_name'      => $filename,
                    'mime_type'      => $mime,
                    'file_size'      => $file->getSize(),
                ]);
                $overwritten++;
            } else {
                ClientDocument::create([
                    'bullion_client_id' => $client->id,
                    'tenant_id'         => $tenant->id,
                    'document_type'     => $rule['type'],
                    'document_label'    => $rule['label'],
                    'file_path'         => $storedPath,
                    'file_name'         => $filename,
                    'mime_type'         => $mime,
                    'file_size'         => $file->getSize(),
                    'is_required'       => false,
                ]);
                $imported++;
            }
        }

        $this->newLine();
        $this->info("Done — {$imported} new, {$overwritten} overwritten for {$label}.");

        return self::SUCCESS;
    }

    private function classify(string $filename): ?array
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $filename));

        foreach (self::RULES as $rule) {
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($normalized, $kw)) {
                    return $rule;
                }
            }
        }

        return null;
    }

    private function matchClient(string $folderName, $clients): ?BullionClient
    {
        $target = $this->normalizeName($folderName);

        foreach ($clients as $client) {
            $candidates = array_filter([$client->company_name, $client->full_name]);
            foreach ($candidates as $candidate) {
                if ($this->normalizeName($candidate) === $target) {
                    return $client;
                }
            }
        }

        // Fallback: fuzzy — target contains/contained-by candidate after normalization.
        foreach ($clients as $client) {
            $candidates = array_filter([$client->company_name, $client->full_name]);
            foreach ($candidates as $candidate) {
                $norm = $this->normalizeName($candidate);
                if ($norm !== '' && (str_contains($target, $norm) || str_contains($norm, $target))) {
                    return $client;
                }
            }
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        // Strip a trailing " - Something" annotation (e.g. "- Company Closed", "- No Transaction").
        $name = preg_replace('/\s*-\s*.+$/', '', $name);
        $name = strtolower($name);
        $name = str_replace(['.', ','], '', $name);

        foreach (self::LEGAL_SUFFIXES as $suffix) {
            $name = preg_replace('/\b' . preg_quote($suffix, '/') . '\b/', '', $name);
        }

        return trim(preg_replace('/\s+/', ' ', $name));
    }
}
