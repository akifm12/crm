<?php
// app/Services/SentinelService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SentinelService
{
    private string $baseUrl;
    private string $email;
    private string $password;
    private string $cacheKey = 'sentinel_jwt_token';

    public function __construct()
    {
        $this->baseUrl = config('sentinel.base_url', 'http://127.0.0.1:8085/api');
        $this->email   = config('sentinel.email');
        $this->password= config('sentinel.password');
    }

    // ── Auth ───────────────────────────────────────────────────────────────

    private function token(): ?string
    {
        return Cache::remember($this->cacheKey, now()->addMinutes(55), function () {
            $response = Http::timeout(10)->post("{$this->baseUrl}/auth/login", [
                'email'    => $this->email,
                'password' => $this->password,
            ]);

            if ($response->successful() && isset($response->json()['token'])) {
                return $response->json()['token'];
            }

            Log::error('Sentinel auth failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        });
    }

    private function http()
    {
        return Http::timeout(30)
            ->withToken($this->token())
            ->acceptJson();
    }

    // ── Screen entity (corporate) ──────────────────────────────────────────

    public function screenEntity(array $params): array
    {
        // params: query, trade_license, country_of_issue, license_number, date_of_issue
        try {
            $payload = array_filter([
                'query'           => $params['query'],
                'entityType'      => 'entity',
                'threshold'       => 65,
                'selectedLists'   => [],
            ]);

            // Add entity-specific fields if provided
            if (!empty($params['trade_license']))    $payload['trade_license']    = $params['trade_license'];
            if (!empty($params['country_of_issue'])) $payload['country_of_issue'] = $params['country_of_issue'];
            if (!empty($params['license_number']))   $payload['license_number']   = $params['license_number'];
            if (!empty($params['date_of_issue']))    $payload['date_of_issue']    = $params['date_of_issue'];

            $response = $this->http()->post("{$this->baseUrl}/screen", $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            // Token might have expired — clear cache and retry once
            if ($response->status() === 401) {
                Cache::forget($this->cacheKey);
                $response = $this->http()->post("{$this->baseUrl}/screen", $payload);
                if ($response->successful()) {
                    return ['success' => true, 'data' => $response->json()];
                }
            }

            return ['success' => false, 'error' => 'Screening API error: ' . $response->status(), 'data' => $response->json()];

        } catch (\Exception $e) {
            Log::error('Sentinel screen entity failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Screen individual ──────────────────────────────────────────────────

    public function screenIndividual(array $params): array
    {
        // params: query, dob, nationality
        try {
            $payload = array_filter([
                'query'         => $params['query'],
                'entityType'    => 'individual',
                'threshold'     => 65,
                'selectedLists' => [],
            ]);

            if (!empty($params['dob']))         $payload['dob']         = $params['dob'];
            if (!empty($params['nationality'])) $payload['nationality'] = $params['nationality'];

            $response = $this->http()->post("{$this->baseUrl}/screen", $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            if ($response->status() === 401) {
                Cache::forget($this->cacheKey);
                $response = $this->http()->post("{$this->baseUrl}/screen", $payload);
                if ($response->successful()) {
                    return ['success' => true, 'data' => $response->json()];
                }
            }

            return ['success' => false, 'error' => 'Screening API error: ' . $response->status()];

        } catch (\Exception $e) {
            Log::error('Sentinel screen individual failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Get screening summary from result ──────────────────────────────────

    public static function summarise(array $data): array
    {
        $hits    = $data['hits']    ?? $data['results']    ?? $data['matches']    ?? [];
        $total   = $data['total']   ?? $data['totalHits']  ?? count($hits);
        $status  = $total > 0 ? 'match' : 'clear';

        return [
            'status'    => $status,
            'total_hits'=> $total,
            'hits'      => array_slice($hits, 0, 10), // store top 10
            'raw'       => $data,
        ];
    }
}
