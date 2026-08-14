<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Log;

class TfsService
{
    // UAEIEC answer option values (static across all TFS surveys)
    const ANSWER_NO_MATCH        = '691a6426-f0ee-4a0b-adfd-f7b05fcd1873';
    const ANSWER_CONFIRMED_MATCH = '84fa24e7-cbe2-4434-9197-18293408123f';
    const ANSWER_PARTIAL_MATCH   = 'f668ca85-ba48-4fe9-b946-a7d47a435573';

    public function extractNamesFromAlertUrl(string $url): array
    {
        try {
            $client   = new Client(['timeout' => 20, 'verify' => false]);
            $response = $client->get($url, ['headers' => ['User-Agent' => 'Mozilla/5.0']]);
            $html     = (string) $response->getBody();

            // Strip tags and get text content
            $text = html_entity_decode(strip_tags($html));
            $text = preg_replace('/\s+/', ' ', $text);

            return $this->extractNamesWithClaude($text, $url);
        } catch (\Throwable $e) {
            Log::error('TFS: failed to fetch alert URL', ['url' => $url, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function extractNamesWithClaude(string $pageText, string $sourceUrl): array
    {
        try {
            $client = new \Anthropic\SDK\Anthropic(['apiKey' => env('ANTHROPIC_API_KEY')]);

            $prompt = "The following is text from a UN Security Council press release about sanctions list amendments. "
                . "Extract ALL individual person names and entity/organisation names that are being ADDED or AMENDED on the sanctions list. "
                . "Return ONLY a JSON array of strings — each string is one name exactly as it appears. "
                . "Include aliases (AKA names) as separate entries. Do not include explanatory text, only the JSON array.\n\n"
                . substr($pageText, 0, 8000);

            $message = $client->messages()->create([
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1024,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            $raw = $message->content[0]->text ?? '[]';

            // Extract JSON array from response
            preg_match('/\[.*\]/s', $raw, $matches);
            $names = json_decode($matches[0] ?? '[]', true);

            return is_array($names) ? array_values(array_filter(array_unique($names))) : [];
        } catch (\Throwable $e) {
            Log::error('TFS: Claude name extraction failed', ['error' => $e->getMessage()]);
            // Fallback: basic regex for capitalised names
            return $this->extractNamesRegex($pageText);
        }
    }

    private function extractNamesRegex(string $text): array
    {
        // Simple fallback: capture runs of Title-Cased words (2+ words, all caps or title case)
        preg_match_all('/\b([A-Z][A-Z\s\-\'\.]{5,50})\b/', $text, $matches);
        $names = array_unique(array_map('trim', $matches[1] ?? []));
        return array_values(array_filter($names, fn($n) => str_word_count($n) >= 2));
    }

    /**
     * Submit a single UAEIEC TFS URL.
     * Returns ['success' => bool, 'message' => string, 'confirmation_html' => string|null]
     */
    public function submitTfsUrl(string $tfsUrl, string $responseKey = 'no_match'): array
    {
        $answerValue = match($responseKey) {
            'confirmed_match' => self::ANSWER_CONFIRMED_MATCH,
            'partial_match'   => self::ANSWER_PARTIAL_MATCH,
            default           => self::ANSWER_NO_MATCH,
        };

        try {
            $jar    = new CookieJar();
            $client = new Client([
                'cookies'         => $jar,
                'allow_redirects' => true,
                'verify'          => false,
                'timeout'         => 30,
                'headers'         => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);

            // ── Step 1: GET the form page ────────────────────────────────────
            $page1Response = $client->get($tfsUrl);
            $page1Html     = (string) $page1Response->getBody();

            if (stripos($page1Html, 'already submitted') !== false
                || stripos($page1Html, 'already been submitted') !== false) {
                return ['success' => false, 'message' => 'Already submitted', 'confirmation_html' => null];
            }

            $fields1 = $this->parseFormFields($page1Html);

            if (!isset($fields1['__RequestVerificationToken'])) {
                return ['success' => false, 'message' => 'Could not parse form (no CSRF token)', 'confirmation_html' => null];
            }

            $questionId = $fields1['QuestionId'] ?? null;

            // ── Step 2: POST page 1 with "Continue" ──────────────────────────
            $postData1 = array_merge($fields1, [
                $questionId => $answerValue,
                'SubmitButton' => 'Continue',
            ]);
            unset($postData1['Back'], $postData1['Submit']); // remove other buttons if captured

            $page2Response = $client->post($tfsUrl, ['form_params' => $postData1]);
            $page2Html     = (string) $page2Response->getBody();

            // ── Step 3: POST page 2 with "Submit" ────────────────────────────
            $fields2 = $this->parseFormFields($page2Html);

            $postData2 = array_merge($fields2, [
                $questionId  => $answerValue, // carry answer to page 2
                'SubmitButton' => 'Submit',
            ]);

            $confirmResponse = $client->post($tfsUrl, ['form_params' => $postData2]);
            $confirmHtml     = (string) $confirmResponse->getBody();

            $success = stripos($confirmHtml, 'thank') !== false
                || stripos($confirmHtml, 'submitted') !== false
                || stripos($confirmHtml, 'success') !== false;

            return [
                'success'           => $success,
                'message'           => $success ? 'Submitted successfully' : 'Submission may have failed — check snapshot',
                'confirmation_html' => $confirmHtml,
            ];
        } catch (\Throwable $e) {
            Log::error('TFS: submission failed', ['url' => $tfsUrl, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'confirmation_html' => null];
        }
    }

    private function parseFormFields(string $html): array
    {
        $fields = [];
        $dom    = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath  = new \DOMXPath($dom);

        // Hidden inputs
        foreach ($xpath->query('//form//input[@type="hidden"]') as $input) {
            $name  = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            if ($name) $fields[$name] = $value;
        }

        // Page field (may not be hidden — ensure it's included)
        $pageInputs = $xpath->query('//input[@name="page"]');
        if ($pageInputs->length > 0) {
            $fields['page'] = $pageInputs->item(0)->getAttribute('value');
        }

        return $fields;
    }

    /**
     * Capture a PDF snapshot of a HTML string using Browsershot.
     * Returns the stored path or null.
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
                ->setChromePath(env('CHROME_PATH', '/usr/bin/google-chrome'))
                ->noSandbox()
                ->pdf($full);

            return $path;
        } catch (\Throwable $e) {
            Log::error('TFS: snapshot failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
