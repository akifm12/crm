<?php
// app/Services/SentinelService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SentinelService
{
    private string $baseUrl;
    private ?string $email;
    private ?string $password;
    private string $cacheKey = 'sentinel_jwt_token';

    public function __construct()
    {
        $this->baseUrl  = config('sentinel.base_url', 'http://127.0.0.1:8085/api');
        $this->email    = config('sentinel.email');
        $this->password = config('sentinel.password');
    }

    private function token(): ?string
    {
        return Cache::remember($this->cacheKey, now()->addMinutes(5), function () {
            $response = Http::timeout(10)->post("{$this->baseUrl}/auth/login", [
                'email'    => $this->email,
                'password' => $this->password,
            ]);
            if ($response->successful() && isset($response->json()['token'])) {
                return $response->json()['token'];
            }
            Log::error('Sentinel auth failed', ['status' => $response->status()]);
            return null;
        });
    }

    private function http()
    {
        return Http::timeout(30)->withToken($this->token())->acceptJson();
    }

	private function doScreen(array $payload): array
	{
		try {
			$response = $this->http()->post("{$this->baseUrl}/screen", $payload);

			// Handle expired session — clear cache and retry
			if ($response->status() === 401 ||
				str_contains($response->body(), 'SESSION_EXPIRED')) {
				Cache::forget($this->cacheKey);
				$response = $this->http()->post("{$this->baseUrl}/screen", $payload);
			}

			if ($response->successful()) {
				return ['success' => true, 'data' => $response->json()];
			}

			Log::error('Sentinel screen error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 500), 'payload' => $payload]);
			return ['success' => false, 'error' => 'API error ' . $response->status() . ': ' . $response->body()];

		} catch (\Exception $e) {
			Log::error('Sentinel screen failed', ['error' => $e->getMessage(), 'payload' => $payload]);
			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

    public function screenEntity(array $params): array
    {
        $payload = [
            'query'         => (string) trim($params['query']),
            'country'       => $params['country_of_issue'] ?? $params['country'] ?? 'UAE',
            'entityType'    => 'entity',
            'threshold'     => 65,
            'selectedLists' => [],
        ];

        if (!empty($params['license_number'])) $payload['license_number'] = $params['license_number'];
        if (!empty($params['date_of_issue']))  $payload['date_of_issue']  = $params['date_of_issue'];

        return $this->doScreen($payload);
    }

    public function screenIndividual(array $params): array
    {
        $rawCountry = ($params['nationality'] ?: null) ?? ($params['country'] ?: null) ?? 'UAE';

        $payload = [
            'query'         => (string) trim($params['query']),
            'country'       => $this->normaliseCountry((string) $rawCountry),
            'entityType'    => 'individual',
            'threshold'     => 65,
            'selectedLists' => [],
        ];

        if (!empty($params['dob'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $params['dob']);
            $payload['dob'] = $d ? $d->format('d/m/Y') : $params['dob'];
        }

        return $this->doScreen($payload);
    }

    private function normaliseCountry(string $raw): string
    {
        $raw = trim($raw);
        if (!$raw || strlen($raw) !== 2) return $raw ?: 'UAE';

        static $map = [
            'AE' => 'United Arab Emirates', 'SA' => 'Saudi Arabia',   'KW' => 'Kuwait',
            'QA' => 'Qatar',                'BH' => 'Bahrain',         'OM' => 'Oman',
            'IN' => 'India',                'PK' => 'Pakistan',        'EG' => 'Egypt',
            'GB' => 'United Kingdom',       'US' => 'United States',   'CN' => 'China',
            'RU' => 'Russia',               'DE' => 'Germany',         'FR' => 'France',
            'JP' => 'Japan',                'AU' => 'Australia',       'CA' => 'Canada',
            'IR' => 'Iran',                 'IQ' => 'Iraq',            'SY' => 'Syria',
            'LB' => 'Lebanon',              'JO' => 'Jordan',          'TR' => 'Turkey',
            'NG' => 'Nigeria',              'ZA' => 'South Africa',    'KE' => 'Kenya',
            'PH' => 'Philippines',          'BD' => 'Bangladesh',      'LK' => 'Sri Lanka',
            'NP' => 'Nepal',                'ID' => 'Indonesia',       'MY' => 'Malaysia',
            'SG' => 'Singapore',            'TH' => 'Thailand',        'VN' => 'Vietnam',
            'BR' => 'Brazil',               'MX' => 'Mexico',          'AR' => 'Argentina',
            'CO' => 'Colombia',             'BS' => 'Bahamas',         'KY' => 'Cayman Islands',
            'PA' => 'Panama',               'CH' => 'Switzerland',     'NL' => 'Netherlands',
            'IT' => 'Italy',                'ES' => 'Spain',           'SE' => 'Sweden',
            'NO' => 'Norway',               'PL' => 'Poland',          'UA' => 'Ukraine',
            'KZ' => 'Kazakhstan',           'UZ' => 'Uzbekistan',      'AF' => 'Afghanistan',
            'MM' => 'Myanmar',              'SO' => 'Somalia',         'SD' => 'Sudan',
            'YE' => 'Yemen',                'LY' => 'Libya',           'ML' => 'Mali',
            'NI' => 'Nicaragua',            'HT' => 'Haiti',           'KP' => 'North Korea',
            'VE' => 'Venezuela',            'CU' => 'Cuba',            'GH' => 'Ghana',
            'ET' => 'Ethiopia',             'BE' => 'Belgium',         'AT' => 'Austria',
            'PT' => 'Portugal',             'DK' => 'Denmark',         'FI' => 'Finland',
            'CZ' => 'Czech Republic',       'HU' => 'Hungary',         'RO' => 'Romania',
        ];

        return $map[strtoupper($raw)] ?? $raw;
    }

    public static function summarise(array $data): array
    {
        // Sentinel returns 'results' key
        $hits   = $data['results'] ?? $data['hits'] ?? $data['matches'] ?? [];
        $total  = count($hits);
        $status = $total > 0 ? 'match' : 'clear';

        return [
            'status'     => $status,
            'total_hits' => $total,
            'hits'       => array_slice($hits, 0, 10),
            'session_id' => $data['sessionId'] ?? null,
            'raw'        => $data,
        ];
    }
}
