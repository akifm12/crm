<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SentinelService
{
    private const LIST_META = [
        'OFAC (USA)'                  => ['id' => 'OFAC_SDN',       'flag' => '🇺🇸', 'color' => '#1a56db'],
        'UN (Global)'                 => ['id' => 'UN_CONSOLIDATED', 'flag' => '🇺🇳', 'color' => '#0e9f6e'],
        'UK (FCDO)'                   => ['id' => 'UK_HMT',          'flag' => '🇬🇧', 'color' => '#9061f9'],
        'EU (Europe)'                 => ['id' => 'EU_CONSOLIDATED', 'flag' => '🇪🇺', 'color' => '#e3a008'],
        'UAE (Local Terror List)'     => ['id' => 'UAE_CBUAE',       'flag' => '🇦🇪', 'color' => '#e02424'],
        'Switzerland (SECO)'          => ['id' => 'CH_SECO',         'flag' => '🇨🇭', 'color' => '#ff0000'],
        'World Bank (Debarred)'       => ['id' => 'WORLD_BANK',      'flag' => '🌍',  'color' => '#ff5a1f'],
        'Global PEPs (OpenSanctions)' => ['id' => 'GLOBAL_PEPS',     'flag' => '🌐',  'color' => '#6366f1'],
        'Interpol (Red Notices)'      => ['id' => 'INTERPOL',        'flag' => '🔴',  'color' => '#cc0000'],
    ];

    private function db()
    {
        return DB::connection('sentinel');
    }

    private function getMeta(string $source): array
    {
        if (isset(self::LIST_META[$source])) return self::LIST_META[$source];
        foreach (self::LIST_META as $key => $meta) {
            if (stripos($source, explode(' ', $key)[0]) !== false) return $meta;
        }
        return ['id' => 'OTHER', 'flag' => '🌐', 'color' => '#64748b'];
    }

    private function deriveRisk(string $source): string
    {
        $s = strtoupper($source);
        if (str_contains($s, 'UAE') || str_contains($s, 'UN ') || str_contains($s, 'INTERPOL')) return 'CRITICAL';
        if (str_contains($s, 'OFAC') || str_contains($s, 'UK') || str_contains($s, 'EU')) return 'HIGH';
        if (str_contains($s, 'PEP')) return 'HIGH';
        return 'MEDIUM';
    }

    private function normalise(string $s): string
    {
        $s = mb_strtolower($s);
        $s = str_replace(["\u{2019}", "\u{2018}", '`'], '', $s);
        $s = str_replace(['-', '_', '.'], ' ', $s);
        return trim((string) preg_replace('/\s+/', ' ', $s));
    }

    private function calcScore(string $query, string $name): int
    {
        $q = $this->normalise($query);
        $n = $this->normalise($name);

        if ($q === $n) return 100;
        if (str_starts_with($n, $q) || str_starts_with($q, $n)) return 95;
        if (str_contains($n, $q) || str_contains($q, $n)) return 88;

        $qt = array_values(array_filter(explode(' ', $q)));
        $nt = array_values(array_filter(explode(' ', $n)));

        $exactHits = array_filter($qt, fn($t) => in_array($t, $nt, true));
        $hitCount  = count($exactHits);

        if ($hitCount === count($qt)) return 92;
        if ($hitCount > 0) {
            $score = (int) round($hitCount / max(count($qt), count($nt)) * 85);
            if ($score >= 60) return $score;
        }

        $fuzzyHits = 0.0;
        foreach ($qt as $qt_) {
            if (strlen($qt_) < 3) continue;
            $best = 0.0;
            foreach ($nt as $nt_) {
                if (strlen($nt_) < 3) continue;
                $maxLen = max(strlen($qt_), strlen($nt_));
                if ($maxLen === 0) continue;
                $sim = 1 - levenshtein($qt_, $nt_) / $maxLen;
                if ($sim > $best) $best = $sim;
            }
            if ($best >= 0.65) $fuzzyHits += $best;
        }
        $relevant = count(array_filter($qt, fn($t) => strlen($t) >= 3));
        if ($relevant > 0) {
            $score = (int) round($fuzzyHits / $relevant * 82);
            if ($score >= 40) return $score;
        }

        $maxLen = max(strlen($q), strlen($n));
        if ($maxLen === 0) return 0;
        $dist = levenshtein(substr($q, 0, 40), substr($n, 0, 40));
        return (int) round((1 - $dist / max($maxLen, 1)) * 75);
    }

    private function applyContextBoost(int $score, object $row, array $ctx): int
    {
        $remarks = mb_strtolower($row->remarks ?? '');
        $uid     = mb_strtolower($row->uid ?? '');

        if (!empty($ctx['country'])) {
            $c = mb_strtolower($ctx['country']);
            if (str_contains($remarks, $c) || str_contains($uid, $c)) {
                $score = min(100, $score + 8);
            }
        }
        if (!empty($ctx['dob'])) {
            $dobClean = str_replace('-', '', $ctx['dob']);
            $dobSlash = str_replace('-', '/', $ctx['dob']);
            if (str_contains($remarks, $ctx['dob']) || str_contains($remarks, $dobClean) || str_contains($remarks, $dobSlash)) {
                $score = min(100, $score + 10);
            }
        }
        return $score;
    }

    private function screen(string $query, string $entityType, array $ctx = [], int $threshold = 65): array
    {
        try {
            $q = trim($query);
            if (strlen($q) < 2) {
                return ['success' => false, 'error' => 'Name must be at least 2 characters'];
            }

            $longTokens = array_values(array_filter(
                explode(' ', $q),
                fn($t) => strlen($t) >= 4
            ));

            // Build bindings: SELECT uses ?, ? then WHERE uses ?, ?, then lev tokens
            $bindings   = [];
            $bindings[] = $q;         // SIMILARITY(name, ?)
            $bindings[] = $q;         // WORD_SIMILARITY(?, name) in SELECT
            $bindings[] = "%{$q}%";   // name ILIKE ?
            $bindings[] = $q;         // WORD_SIMILARITY(?, name) in WHERE

            $levClauses = [];
            foreach ($longTokens as $t) {
                $maxDist      = strlen($t) <= 5 ? 1 : 2;
                $levClauses[] = "OR EXISTS (
                    SELECT 1 FROM regexp_split_to_table(lower(name), '\\s+') AS w
                    WHERE levenshtein(w, lower(?)) <= {$maxDist} AND length(w) >= 3
                )";
                $bindings[] = $t;
            }

            $typeFilter = match ($entityType) {
                'individual' => "AND type ILIKE ANY(ARRAY['%individual%','%person%','%sanctioned%'])",
                'entity'     => "AND type ILIKE ANY(ARRAY['%entity%','%company%','%legal%','%organisation%','%organization%','%position%','-0-','%unknown%'])",
                default      => '',
            };

            $levSql = implode("\n", $levClauses);

            $sql = "
                SELECT DISTINCT id, source, uid, name, type, program, remarks,
                       GREATEST(SIMILARITY(name, ?), WORD_SIMILARITY(?, name)) AS pg_sim
                FROM sanctions
                WHERE (
                    name ILIKE ?
                    OR WORD_SIMILARITY(?, name) > 0.25
                    {$levSql}
                )
                {$typeFilter}
                ORDER BY pg_sim DESC
                LIMIT 300
            ";

            $rows = $this->db()->select($sql, $bindings);

            $seen    = [];
            $results = [];

            foreach ($rows as $row) {
                if (isset($seen[$row->id])) continue;
                $seen[$row->id] = true;

                $score = $this->calcScore($q, $row->name);
                $score = $this->applyContextBoost($score, $row, $ctx);

                if ($score < $threshold) continue;

                $meta      = $this->getMeta($row->source ?? '');
                $results[] = [
                    'id'         => $row->id,
                    'externalId' => $row->uid,
                    'listId'     => $meta['id'],
                    'name'       => $row->name,
                    'type'       => $row->type ?? 'Entity',
                    'programs'   => array_values(array_filter([$row->program])),
                    'reason'     => $row->remarks,
                    'riskLevel'  => $this->deriveRisk($row->source ?? ''),
                    'matchScore' => $score,
                    'matchType'  => $score >= 95 ? 'exact' : ($score >= 80 ? 'close' : 'fuzzy'),
                    'list'       => [
                        'id'        => $meta['id'],
                        'name'      => $row->source,
                        'authority' => $row->source,
                        'color'     => $meta['color'],
                        'flag'      => $meta['flag'],
                    ],
                ];
            }

            usort($results, fn($a, $b) => $b['matchScore'] <=> $a['matchScore']);

            return ['success' => true, 'data' => [
                'sessionId' => (string) Str::uuid(),
                'query'     => $query,
                'results'   => array_slice($results, 0, 50),
                'total'     => count($results),
            ]];

        } catch (\Exception $e) {
            Log::error('Sentinel screen failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'type'  => $entityType,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function screenEntity(array $params): array
    {
        return $this->screen(
            query:      (string) trim($params['query']),
            entityType: 'entity',
            ctx:        [
                'country' => $params['country_of_issue'] ?? $params['country'] ?? '',
                'dob'     => '',
            ],
        );
    }

    public function screenIndividual(array $params): array
    {
        $country = ($params['nationality'] ?: null) ?? ($params['country'] ?: null) ?? '';

        return $this->screen(
            query:      (string) trim($params['query']),
            entityType: 'individual',
            ctx:        [
                'country' => $country,
                'dob'     => $params['dob'] ?? '',
            ],
        );
    }

    public static function summarise(array $data): array
    {
        $hits   = $data['results'] ?? [];
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
