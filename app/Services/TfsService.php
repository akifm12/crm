<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TfsService
{
    /**
     * Submit a single UAEIEC TFS URL via headless Chrome (Puppeteer).
     * This bypasses the F5 BIG-IP bot-detection JS challenge that blocks plain HTTP clients.
     *
     * Returns ['success' => bool, 'message' => string, 'snapshot_path' => string|null]
     */
    public function submitTfsUrl(string $tfsUrl, string $responseKey = 'no_match'): array
    {
        $snapshotDir = storage_path('app/public/tfs-snapshots');
        if (!is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0755, true);
        }

        $pdfFilename = 'tfs-' . uniqid() . '.pdf';
        $pdfFullPath = $snapshotDir . '/' . $pdfFilename;
        $scriptPath  = base_path('scripts/tfs-submit.cjs');

        $cmd = sprintf(
            'HOME=/tmp NODE_PATH=/usr/lib/node_modules node %s %s %s %s 2>&1',
            escapeshellarg($scriptPath),
            escapeshellarg($tfsUrl),
            escapeshellarg($responseKey),
            escapeshellarg($pdfFullPath)
        );

        Log::debug('TFS: running script', ['cmd' => $cmd]);

        $output = shell_exec($cmd);

        Log::debug('TFS: script output', ['output' => $output]);

        $result = json_decode(trim($output ?? ''), true);

        if (!is_array($result)) {
            Log::error('TFS: script returned non-JSON', ['output' => $output]);
            return [
                'success'       => false,
                'message'       => 'Script error — see Laravel log. Output: ' . substr($output ?? '', 0, 200),
                'snapshot_path' => null,
            ];
        }

        $snapshotPath = (($result['success'] ?? false) && file_exists($pdfFullPath))
            ? 'tfs-snapshots/' . $pdfFilename
            : null;

        return [
            'success'       => $result['success'] ?? false,
            'message'       => $result['message'] ?? 'Unknown result',
            'snapshot_path' => $snapshotPath,
        ];
    }

    /**
     * Capture a PDF snapshot of a raw HTML string (fallback, not used for submission).
     */
    public function snapshotHtml(string $html, string $filename): ?string
    {
        try {
            $path = 'tfs-snapshots/' . $filename;
            $full = storage_path('app/public/' . $path);

            if (!is_dir(dirname($full))) {
                mkdir(dirname($full), 0755, true);
            }

            \Spatie\Browsershot\Browsershot::html($html)
                ->setChromePath('/usr/bin/google-chrome-stable')
                ->setNodeModulePath('/usr/lib/node_modules')
                ->addChromiumArguments(['disable-dev-shm-usage', 'disable-gpu', 'no-zygote'])
                ->format('A4')
                ->noSandbox()
                ->showBackground()
                ->pdf($full);

            return $path;
        } catch (\Throwable $e) {
            Log::error('TFS: snapshot failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
