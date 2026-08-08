<?php
// app/Services/AnthropicService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicService
{
    private ?string $key;
    private string $model;
    private int $maxTokens;

    public function __construct()
    {
        $this->key       = config('services.anthropic.key');
        $this->model     = config('content.anthropic.model', 'claude-sonnet-5');
        $this->maxTokens = config('content.anthropic.max_tokens', 1024);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->key);
    }

    /**
     * Send a single-turn prompt to Claude and return the raw text response.
     *
     * @return array{success: bool, text?: string, error?: string}
     */
    public function complete(string $prompt): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'ANTHROPIC_API_KEY is not configured'];
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key'         => $this->key,
                    'anthropic-version' => '2023-06-01',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                $text = collect($response->json('content', []))
                    ->where('type', 'text')
                    ->pluck('text')
                    ->implode('');

                return ['success' => true, 'text' => trim($text)];
            }

            return ['success' => false, 'error' => 'API error ' . $response->status() . ': ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('AnthropicService::complete failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
