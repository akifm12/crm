<?php

namespace App\Console\Commands;

use App\Jobs\ScanUploadedDocument;
use App\Models\ClientDocument;
use App\Models\DocumentScanLog;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SweepDocumentScans extends Command
{
    protected $signature = 'docs:sweep
                            {--tenant= : Tenant slug (omit to sweep all tenants)}
                            {--dry-run : Show what would be scanned without doing it}
                            {--rescan  : Re-scan even documents that were previously scanned}';

    protected $description = 'Scan all previously uploaded client documents and populate missing profile data';

    private array $scannable = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

    public function handle(): int
    {
        if (!config('services.anthropic.key')) {
            $this->error('ANTHROPIC_API_KEY is not set in .env');
            return 1;
        }

        $query = ClientDocument::with('client')->orderBy('bullion_client_id');

        if ($slug = $this->option('tenant')) {
            $tenant = Tenant::where('slug', $slug)->first();
            if (!$tenant) {
                $this->error("Tenant '{$slug}' not found.");
                return 1;
            }
            $query->where('tenant_id', $tenant->id);
        }

        // Skip already-scanned documents unless --rescan
        if (!$this->option('rescan')) {
            $scannedDocIds = DocumentScanLog::whereNotNull('client_document_id')->pluck('client_document_id');
            $query->whereNotIn('id', $scannedDocIds);
        }

        $docs = $query->get();

        // Filter to scannable mime types
        $scannable = $docs->filter(fn($d) => in_array($d->mime_type, $this->scannable));
        $skipped   = $docs->count() - $scannable->count();

        $this->info("Found {$docs->count()} unscanned document(s) — {$scannable->count()} scannable, {$skipped} skipped (non-image/PDF).");

        if ($scannable->isEmpty()) {
            $this->line('Nothing to do.');
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['Client', 'File', 'Type', 'Mime'],
                $scannable->map(fn($d) => [
                    $d->client?->displayName() ?? $d->bullion_client_id,
                    $d->file_name,
                    $d->document_type,
                    $d->mime_type,
                ])->toArray()
            );
            $this->warn('Dry run — nothing was scanned.');
            return 0;
        }

        if (!$this->confirm("Scan {$scannable->count()} document(s)? This will call the Anthropic API for each one.")) {
            return 0;
        }

        $bar     = $this->output->createProgressBar($scannable->count());
        $applied = $noChanges = $failed = 0;

        foreach ($scannable as $doc) {
            if (!Storage::disk('local')->exists($doc->file_path)) {
                $this->newLine();
                $this->warn("  Missing on disk: {$doc->file_path}");
                $failed++;
                $bar->advance();
                continue;
            }

            (new ScanUploadedDocument($doc))->handle();

            $log = DocumentScanLog::where('client_document_id', $doc->id)->latest()->first();
            if ($log) {
                match ($log->status) {
                    'applied'    => $applied++,
                    'no_changes' => $noChanges++,
                    default      => $failed++,
                };
            } else {
                $failed++;
            }

            $bar->advance();
            usleep(300_000); // 0.3 s — gentle rate limiting
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Result', 'Count'], [
            ['Changes applied',            $applied],
            ['Already complete (no changes)', $noChanges],
            ['Failed / missing file',      $failed],
        ]);

        return 0;
    }
}
