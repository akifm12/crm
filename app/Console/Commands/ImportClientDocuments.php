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
        {--dry-run : Preview matches and files without writing anything}';

    protected $description = 'Bulk-import trade licence / MOA / passport / EID / ejari / visa files from client-named folders into the matching client profile';

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

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Importing into tenant: {$tenant->name} ({$clients->count()} clients on file)");
        $this->line("Source: {$root}\n");

        $folders = collect(scandir($root))
            ->reject(fn ($f) => in_array($f, ['.', '..']))
            ->filter(fn ($f) => is_dir($root . DIRECTORY_SEPARATOR . $f))
            ->values();

        $matched = 0; $unmatched = 0; $imported = 0; $overwritten = 0; $skippedFiles = 0;
        $unmatchedList = [];

        foreach ($folders as $folderName) {
            $client = $this->matchClient($folderName, $clients);

            if (! $client) {
                $unmatched++;
                $unmatchedList[] = $folderName;
                $this->warn("✗ No match: \"{$folderName}\"");
                continue;
            }

            $matched++;
            $label = $client->company_name ?: $client->full_name;
            $this->info("✓ \"{$folderName}\" → {$label} (client #{$client->id})");

            $folderPath = $root . DIRECTORY_SEPARATOR . $folderName;
            $finder = Finder::create()->files()->in($folderPath)->ignoreDotFiles(true)->ignoreVCS(true);

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
                $existing = $existingQuery->first();

                $this->line("    [{$rule['label']}] {$filename}" . ($existing ? ' (overwriting existing)' : ''));

                if ($dryRun) {
                    $existing ? $overwritten++ : $imported++;
                    continue;
                }

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
        }

        $this->newLine();
        $this->info('── Summary ──────────────────────────');
        $this->line("Folders matched:    {$matched}");
        $this->line("Folders unmatched:  {$unmatched}");
        $this->line(($dryRun ? 'Would import:  ' : 'Imported:      ') . $imported . ' new');
        $this->line(($dryRun ? 'Would overwrite: ' : 'Overwritten:   ') . $overwritten . ' existing');
        $this->line("Files skipped (not a target type): {$skippedFiles}");

        if ($unmatched > 0) {
            $this->newLine();
            $this->warn('Unmatched folders (no client record found — review manually):');
            foreach ($unmatchedList as $u) {
                $this->line("  - {$u}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('Dry run only — nothing was written. Re-run without --dry-run to commit.');
        }

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
