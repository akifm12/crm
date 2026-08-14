<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Log;

class TfsService
{
    /**
     * Submit a single UAEIEC TFS URL.
     * Returns ['success' => bool, 'message' => string, 'confirmation_html' => string|null]
     */
    public function submitTfsUrl(string $tfsUrl, string $responseKey = 'no_match'): array
    {
        try {
            $jar    = new CookieJar();
            $client = new Client([
                'cookies'         => $jar,
                'allow_redirects' => true,
                'http_errors'     => false,   // handle status codes manually
                'verify'          => false,
                'timeout'         => 30,
                'headers'         => [
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ],
            ]);

            // ── Step 1: GET the form ─────────────────────────────────────────
            $r1   = $client->get($tfsUrl);
            $html1 = (string) $r1->getBody();

            Log::debug('TFS GET', ['status' => $r1->getStatusCode(), 'url' => $tfsUrl]);

            if (stripos($html1, 'already submitted') !== false
                || stripos($html1, 'already been submitted') !== false) {
                return ['success' => false, 'message' => 'Already submitted', 'confirmation_html' => null];
            }

            [$fields1, $action1, $selectName, $answerValue] = $this->parseForm($html1, $tfsUrl, $responseKey);

            if (!isset($fields1['__RequestVerificationToken'])) {
                Log::error('TFS: no CSRF token found', ['html_excerpt' => substr($html1, 0, 500)]);
                return ['success' => false, 'message' => 'Could not parse form — no CSRF token found', 'confirmation_html' => null];
            }

            if (!$answerValue) {
                Log::error('TFS: could not find answer option', ['responseKey' => $responseKey]);
                return ['success' => false, 'message' => 'Could not find matching answer option in form', 'confirmation_html' => null];
            }

            // ── Step 2: POST "Continue" ──────────────────────────────────────
            $post1 = $fields1;
            if ($selectName) $post1[$selectName] = $answerValue;
            $post1['SubmitButton'] = 'Continue';

            $r2    = $client->post($action1, ['form_params' => $post1]);
            $html2 = (string) $r2->getBody();

            Log::debug('TFS Continue', ['status' => $r2->getStatusCode(), 'action' => $action1]);

            if ($r2->getStatusCode() >= 500) {
                Log::error('TFS: 500 on Continue step', ['html' => substr($html2, 0, 800)]);
                return ['success' => false, 'message' => 'Server error on Continue step (500) — see Laravel log', 'confirmation_html' => null];
            }

            // ── Step 3: POST "Submit" ────────────────────────────────────────
            [$fields2, $action2, $selectName2, ] = $this->parseForm($html2, $action1, $responseKey);

            $post2 = $fields2;
            // carry the answer through to the confirmation step
            $sName = $selectName2 ?: $selectName;
            if ($sName) $post2[$sName] = $answerValue;
            $post2['SubmitButton'] = 'Submit';

            $r3    = $client->post($action2, ['form_params' => $post2]);
            $html3 = (string) $r3->getBody();

            Log::debug('TFS Submit', ['status' => $r3->getStatusCode(), 'action' => $action2]);

            if ($r3->getStatusCode() >= 500) {
                Log::error('TFS: 500 on Submit step', ['html' => substr($html3, 0, 800)]);
                return ['success' => false, 'message' => 'Server error on Submit step (500) — see Laravel log', 'confirmation_html' => null];
            }

            $success = stripos($html3, 'thank')     !== false
                    || stripos($html3, 'submitted')  !== false
                    || stripos($html3, 'success')    !== false
                    || stripos($html3, 'received')   !== false;

            return [
                'success'           => $success,
                'message'           => $success ? 'Submitted successfully' : 'Unexpected response — check snapshot',
                'confirmation_html' => $html3,
            ];

        } catch (\Throwable $e) {
            Log::error('TFS: submission exception', ['url' => $tfsUrl, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage(), 'confirmation_html' => null];
        }
    }

    /**
     * Parse the form: hidden fields, action URL, select name, and the matching answer value.
     * Answer value is resolved dynamically from option text so it works across survey cycles.
     *
     * Returns [$fields, $actionUrl, $selectName, $answerValue]
     */
    private function parseForm(string $html, string $fallbackUrl, string $responseKey): array
    {
        $fields     = [];
        $actionUrl  = $fallbackUrl;
        $selectName = null;
        $answerValue = null;

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // Form action URL
        $forms = $xpath->query('//form');
        if ($forms->length > 0) {
            $raw = trim($forms->item(0)->getAttribute('action'));
            if ($raw) {
                $actionUrl = str_starts_with($raw, 'http')
                    ? $raw
                    : $this->resolveUrl($fallbackUrl, $raw);
            }
        }

        // Hidden inputs
        foreach ($xpath->query('//form//input[@type="hidden"]') as $node) {
            $name  = $node->getAttribute('name');
            $value = $node->getAttribute('value');
            if ($name) $fields[$name] = $value;
        }

        // Non-hidden inputs that aren't submit buttons (e.g. page field as text/number)
        foreach ($xpath->query('//form//input[not(@type="hidden") and not(@type="submit") and not(@type="button")]') as $node) {
            $name  = $node->getAttribute('name');
            $value = $node->getAttribute('value');
            if ($name && !isset($fields[$name])) $fields[$name] = $value;
        }

        // Select element — find name and matching option value from label text
        $keywords = match($responseKey) {
            'confirmed_match' => ['confirmed', 'match found', 'yes'],
            'partial_match'   => ['partial'],
            default           => ['no match', 'not found', 'no match identified', 'none'],
        };

        foreach ($xpath->query('//form//select') as $select) {
            $selectName = $select->getAttribute('name');
            foreach ($xpath->query('.//option', $select) as $option) {
                $text  = strtolower(trim($option->textContent));
                $value = $option->getAttribute('value');
                if (!$value) continue;
                foreach ($keywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $answerValue = $value;
                        break 2;
                    }
                }
            }
            // If no keyword matched, just pick the first non-empty option
            if (!$answerValue) {
                foreach ($xpath->query('.//option[@value!=""]', $select) as $option) {
                    $answerValue = $option->getAttribute('value');
                    break;
                }
            }
        }

        // Also check for QuestionId hidden field used as select name (UAEIEC pattern)
        if (!$selectName && isset($fields['QuestionId'])) {
            $selectName = $fields['QuestionId'];
        }

        Log::debug('TFS parseForm', [
            'action'      => $actionUrl,
            'selectName'  => $selectName,
            'answerValue' => $answerValue,
            'fields'      => array_keys($fields),
        ]);

        return [$fields, $actionUrl, $selectName, $answerValue];
    }

    private function resolveUrl(string $base, string $relative): string
    {
        $p = parse_url($base);
        $origin = $p['scheme'] . '://' . $p['host'];
        return $origin . '/' . ltrim($relative, '/');
    }

    /**
     * Capture a PDF snapshot of a HTML string using Browsershot.
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
