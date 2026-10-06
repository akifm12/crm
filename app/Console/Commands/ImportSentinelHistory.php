<?php

namespace App\Console\Commands;

use App\Models\ScreeningLog;
use App\Services\SentinelService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * One-off: copy screenings made before the portal screened through Sentinel into
 * Sentinel as cases (source "import", original dates), and link each log to its case.
 * Safe to re-run — already linked logs are skipped and Sentinel deduplicates.
 */
class ImportSentinelHistory extends Command
{
    protected $signature   = 'sentinel:import-history {--dry-run : Show the mapping without writing anything}';
    protected $description = 'Import pre-Sentinel screening history into Sentinel as cases';

    public function handle(): int
    {
        $logs = ScreeningLog::with(['tenant', 'screener'])
            ->whereNull('result->case_id')
            ->whereIn('status', ['clear', 'match'])
            ->orderBy('id')
            ->get();

        $dryRun = $this->option('dry-run');
        $counts = [];
        $linked = $failed = 0;

        foreach ($logs as $log) {
            [$status, $comments, $reviewedAt] = $this->decision($log);
            $counts[$status] = ($counts[$status] ?? 0) + 1;

            if ($dryRun) {
                $this->line(sprintf('%5d  %-10s %-16s %s%s', $log->id, $log->tenant?->slug, $status,
                    $log->query, $comments ? '  [' . $comments . ']' : ''));
                continue;
            }

            // Paced under Sentinel's API rate limit (120/min per IP, shared with live portal traffic)
            usleep(750_000);
            $response = Http::baseUrl(config('sentinel.base_url'))
                ->withHeaders(['X-API-Key' => config('sentinel.api_key')])->acceptJson()->timeout(20)
                ->retry(3, 30_000, fn ($e) => $e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429, throw: false)
                ->post('/cases/import', [
                    'tenantId'        => $log->tenant_id,
                    'tenantName'      => $log->tenant?->name ?? "Tenant {$log->tenant_id}",
                    'query'           => $log->query,
                    'entityType'      => $log->entity_type,
                    'status'          => $status,
                    'hits'            => $log->result['hits'] ?? [],
                    'screenedByName'  => $log->screener?->name ?? ($log->source === 'monitoring' ? 'Ongoing monitoring' : null),
                    'screenedByEmail' => $log->screener?->email,
                    'reference'       => $log->reference,
                    'createdAt'       => $log->created_at->toIso8601String(),
                    'reviewComments'  => $comments,
                    'reviewedAt'      => $reviewedAt,
                ]);

            if (!$response->successful()) {
                $this->error("Log {$log->id}: Sentinel {$response->status()} " . substr(strip_tags($response->body()), 0, 120));
                $failed++;
                continue;
            }
            $result = $log->result ?? [];
            $result['case_id']         = $response->json('id');
            $result['sentinel_status'] = $status;
            $log->update(['result' => $result]);
            $linked++;
        }

        foreach ($counts as $status => $n) $this->info("{$status}: {$n}");
        $this->info($dryRun ? "Would import {$logs->count()} screening(s)."
                            : "Linked {$linked} screening(s) to Sentinel cases, {$failed} failed (re-run to retry).");
        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** Sentinel status for a historical log, carrying over any review done in the portal. */
    private function decision(ScreeningLog $log): array
    {
        if ($log->status === 'clear') return ['MARKED_AS_CLEAR', null, null];

        $reviews = collect($log->reviews ?? []);
        if ($reviews->isEmpty() || !$log->isFullyReviewed()) {
            $note = $reviews->isEmpty() ? null : 'Partly reviewed in the BAMC portal: ' . $this->summary($reviews);
            return ['MARKED_FOR_EDD', $note, null];
        }

        $truePositive = $reviews->contains('verdict', 'true_positive');
        return [
            $truePositive ? 'MARKED_AS_FAIL' : 'MARKED_AS_CLEAR',
            'Reviewed in the BAMC portal: ' . $this->summary($reviews),
            optional($log->reviewed_at)->toIso8601String(),
        ];
    }

    private function summary($reviews): string
    {
        return $reviews->map(fn ($r) => sprintf('hit %s %s by %s%s',
            $r['hit_id'], str_replace('_', ' ', $r['verdict']), $r['reviewed_by_name'] ?? 'unknown',
            !empty($r['notes']) ? " (\"{$r['notes']}\")" : ''))->implode('; ');
    }
}
