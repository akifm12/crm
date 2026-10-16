<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TfsService
{
    /**
     * Run scripts/tfs-submit.cjs (headless Chrome via Puppeteer). A real browser is
     * needed because the UAE EOCN site sits behind an F5 BIG-IP bot-detection JS
     * challenge that blocks plain HTTP clients.
     *
     * The script prints DEBUG lines to stderr (captured via 2>&1) and one JSON
     * object as the last line of stdout; that last line is the result.
     */
    private function runScript(array $args): ?array
    {
        // Chrome/Puppeteer need a writable HOME. A shared /tmp/.config left behind by
        // another OS user (e.g. someone testing as root) used to break runs with EACCES.
        $debugDir = storage_path('app/tfs-debug');
        $homeDir  = storage_path('app/tfs-home');
        foreach ([$debugDir, $homeDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $cmd = sprintf(
            'HOME=%s NODE_PATH=/usr/lib/node_modules TFS_DEBUG_DIR=%s node %s %s 2>&1',
            escapeshellarg($homeDir),
            escapeshellarg($debugDir),
            escapeshellarg(base_path('scripts/tfs-submit.cjs')),
            implode(' ', array_map('escapeshellarg', $args))
        );

        // The unique survey links are credentials; never write them to the log.
        Log::debug('TFS: running script', ['mode' => $args[0] ?? null]);

        $output = shell_exec($cmd);

        $lines  = array_values(array_filter(array_map('trim', explode("\n", $output ?? ''))));
        $result = json_decode(end($lines) ?: '', true);

        if (!is_array($result)) {
            Log::error('TFS: script returned non-JSON', ['output' => substr(preg_replace('#https?://\S+#', '<url>', $output ?? ''), 0, 600)]);
            return null;
        }

        return $result;
    }

    /**
     * Open a TFS link and read it without answering.
     *
     * Returns ['success' => bool, 'state' => form|used|unknown, 'title', 'text', 'text_length', 'message']
     */
    public function preview(string $tfsUrl): array
    {
        $result = $this->runScript(['preview', $tfsUrl]);

        if ($result === null) {
            return ['success' => false, 'state' => 'unknown', 'message' => 'The browser script returned nothing readable — see the Laravel log.'];
        }

        return $result + ['state' => 'unknown'];
    }

    /**
     * Fill the form with a chosen answer and save a PDF of it. Nothing is clicked or
     * sent: this is the step a person inspects before the real submit.
     *
     * Returns ['success', 'message', 'state'?, 'selected_text'?, 'options'?, 'prepared_snapshot_path']
     */
    public function prepare(string $tfsUrl, string $answerText): array
    {
        $snapshotDir = $this->snapshotDir();
        $name = 'tfs-' . uniqid() . '-prepared.pdf';

        $result = $this->runScript(['prepare', $tfsUrl, $answerText, $snapshotDir . '/' . $name]);

        if ($result === null) {
            return ['success' => false, 'message' => 'Script error — see Laravel log.', 'prepared_snapshot_path' => null];
        }

        $ok = (bool) ($result['success'] ?? false);

        return [
            'success'                => $ok,
            'state'                  => $result['state'] ?? null,
            'message'                => $result['message'] ?? 'Unknown result',
            'selected_text'          => $result['selected_text'] ?? null,
            'options'                => $result['options'] ?? null,
            'prepared_snapshot_path' => ($ok && file_exists($snapshotDir . '/' . $name)) ? 'tfs-snapshots/' . $name : null,
        ];
    }

    /**
     * Answer and submit a single TFS link with the exact answer text a person approved,
     * saving "before submit" and "after submit" PDF evidence. The script refuses (and
     * submits nothing) unless that text selects exactly one option and the page still
     * shows it right before the final click.
     *
     * Returns ['success', 'message', 'state'?, 'snapshot_path' (after), 'pre_snapshot_path' (before)]
     */
    public function submitTfsUrl(string $tfsUrl, string $answerText): array
    {
        $snapshotDir = $this->snapshotDir();

        $id          = uniqid();
        $afterName   = "tfs-{$id}.pdf";
        $beforeName  = "tfs-{$id}-before.pdf";
        $afterPath   = $snapshotDir . '/' . $afterName;
        $beforePath  = $snapshotDir . '/' . $beforeName;

        $result = $this->runScript(['submit', $tfsUrl, $answerText, $afterPath, $beforePath]);

        if ($result === null) {
            return [
                'success'           => false,
                'message'           => 'Script error — see Laravel log.',
                'snapshot_path'     => null,
                'pre_snapshot_path' => null,
            ];
        }

        $ok = (bool) ($result['success'] ?? false);

        return [
            'success'           => $ok,
            'state'             => $result['state'] ?? null,
            'message'           => $result['message'] ?? 'Unknown result',
            'snapshot_path'     => ($ok && file_exists($afterPath)) ? 'tfs-snapshots/' . $afterName : null,
            'pre_snapshot_path' => file_exists($beforePath) ? 'tfs-snapshots/' . $beforeName : null,
        ];
    }

    private function snapshotDir(): string
    {
        $dir = storage_path('app/public/tfs-snapshots');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    // ── Answer classification ─────────────────────────────────────────────

    private static function norm(string $s): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    }

    /**
     * What an answer means: no_match | partial_match | confirmed_match, or null when it
     * can't be told. Deliberately cautious: anything unclear is null, and null is
     * treated as if it could raise a match.
     */
    public static function answerKey(string $text): ?string
    {
        $t = self::norm($text);
        $noMatch = (bool) preg_match('/\bno (potential |possible )?match(es)?\b/', $t);

        if ($noMatch && !preg_match('/partial|confirmed|possible match found|yes/', $t)) return 'no_match';
        if (str_contains($t, 'partial') && !$noMatch)                                       return 'partial_match';
        if (str_contains($t, 'confirmed') && !$noMatch)                                      return 'confirmed_match';

        return null;
    }

    /** True only for an answer that unambiguously says no match was found. */
    public static function isSafeNoMatch(string $text): bool
    {
        return self::answerKey($text) === 'no_match';
    }

    /**
     * The form's "no match" option, if exactly one option is unambiguously that.
     * Null when there are none or several, so the person must choose explicitly.
     */
    public static function safeNoMatchOption(array $options): ?string
    {
        $safe = array_values(array_filter($options, fn ($o) => self::isSafeNoMatch((string) $o)));
        return count($safe) === 1 ? $safe[0] : null;
    }

    /** True if $answer is exactly one of the options the form offered. */
    public static function isOffered(string $answer, array $options): bool
    {
        $norm = self::norm($answer);
        return count(array_filter($options, fn ($o) => self::norm((string) $o) === $norm)) === 1;
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
