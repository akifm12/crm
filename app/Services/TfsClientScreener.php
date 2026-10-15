<?php

namespace App\Services;

use App\Models\BullionClient;
use Illuminate\Support\Str;

/**
 * Checks a TFS alert against a tenant's own client base.
 *
 * The alert page's layout isn't something we control, so instead of trying to
 * extract the listed names from it, we turn the question around: take every name
 * and ID number we hold for the tenant (clients, signatories, shareholders, UBOs)
 * and look for it in the page text. Names are matched loosely enough to survive
 * the usual transliteration differences (Mohammed / Muhammad / Mohamad) and are
 * required to appear close together, so two unrelated entries can't combine into a
 * false hit. A hit is never an answer on its own: it stops automatic submission so
 * a person decides. A miss is only trusted when the page was read properly.
 */
class TfsClientScreener
{
    /** Words that carry no identity in a company name. */
    private const COMPANY_NOISE = [
        'llc', 'l', 'c', 'ltd', 'limited', 'fze', 'fzco', 'fz', 'fzc', 'co', 'company', 'est', 'establishment',
        'inc', 'corp', 'corporation', 'pjsc', 'sole', 'proprietorship', 'branch', 'of', 'the', 'and', 'for', 'general',
        'trading', 'group', 'international', 'holding', 'holdings', 'enterprises', 'services', 'dmcc', 'ae', 'uae',
    ];

    // ── Tenant data ───────────────────────────────────────────────────────

    /** Everything we can search the alert for, for one tenant. */
    public function subjects(int $tenantId): array
    {
        $subjects = [];

        $clients = BullionClient::where('tenant_id', $tenantId)
            ->with(['signatories', 'shareholders', 'ubos'])
            ->get();

        foreach ($clients as $c) {
            $clientName = $c->displayName();
            $isCompany  = $c->client_type !== 'individual';

            $this->push($subjects, $c->id, $clientName, $isCompany ? $c->company_name : $c->full_name, $isCompany ? 'company' : 'person', 'client',
                [$c->passport_number, $c->eid_number, $c->trade_license_no]);

            foreach ($c->signatories as $s) {
                $this->push($subjects, $c->id, $clientName, $s->full_name, 'person', 'signatory', [$s->passport_number, $s->eid_number]);
            }
            foreach ($c->shareholders as $s) {
                $this->push($subjects, $c->id, $clientName, $s->name, ($s->shareholder_type === 'corporate') ? 'company' : 'person', 'shareholder', [$s->passport_number, $s->eid_number]);
            }
            foreach ($c->ubos as $u) {
                $this->push($subjects, $c->id, $clientName, $u->full_name, 'person', 'UBO', [$u->passport_number]);
            }
        }

        return $subjects;
    }

    private function push(array &$subjects, int $clientId, string $clientName, ?string $name, string $kind, string $role, array $ids): void
    {
        $name = trim((string) $name);
        $ids  = array_values(array_filter(array_map(fn ($i) => trim((string) $i), $ids)));
        if ($name === '' && !$ids) return;

        $subjects[] = [
            'client_id'   => $clientId,
            'client_name' => $clientName,
            'name'        => $name,
            'kind'        => $kind,
            'role'        => $role,
            'ids'         => $ids,
        ];
    }

    public function check(int $tenantId, string $pageText): array
    {
        return self::match($pageText, $this->subjects($tenantId));
    }

    // ── Matching (pure, no database) ──────────────────────────────────────

    /**
     * @param  array $subjects  see subjects()
     * @return array  candidate matches: client_id, client_name, subject, role, matched_on, strength, snippet
     */
    public static function match(string $pageText, array $subjects): array
    {
        $rawTokens   = self::tokens($pageText);
        $pageCanon   = array_map([self::class, 'canon'], $rawTokens);
        $pageAlnum   = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($pageText)));
        $positions   = [];                       // canonical word => every position it occurs at
        foreach ($pageCanon as $i => $w) $positions[$w][] = $i;
        $unique      = array_keys($positions);
        $candidates  = [];
        $seen        = [];

        foreach ($subjects as $s) {
            // ID numbers: exact, ignoring spaces/punctuation/case. Skip short ones that would match by chance.
            foreach ($s['ids'] as $id) {
                $needle = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($id)));
                if (strlen($needle) >= 7 && str_contains($pageAlnum, $needle)) {
                    self::add($candidates, $seen, $s, $id, 'id', 'strong', 'Identifier ' . $id . ' appears in the alert');
                }
            }

            if ($s['name'] === '') continue;

            $subjectTokens = self::subjectTokens($s['name'], $s['kind']);
            if (!$subjectTokens) continue;

            $count = count($subjectTokens);
            $window = self::findWindow($subjectTokens, $pageCanon, $positions, $unique, $count + 5);
            if ($window === null) continue;

            // A single distinctive word is a weak signal: still flagged, but marked so.
            $strength = $count >= 2 ? 'strong' : 'weak';
            if ($count === 1 && strlen($subjectTokens[0]) < 6) continue;

            $snippet = implode(' ', array_slice($rawTokens, max(0, $window[0] - 2), ($window[1] - $window[0]) + 4));
            self::add($candidates, $seen, $s, $s['name'], 'name', $strength, $snippet);
        }

        return $candidates;
    }

    private static function add(array &$out, array &$seen, array $s, string $subject, string $on, string $strength, string $snippet): void
    {
        $key = $s['client_id'] . '|' . $subject . '|' . $on;
        if (isset($seen[$key])) return;
        $seen[$key] = true;
        $out[] = [
            'client_id'   => $s['client_id'],
            'client_name' => $s['client_name'],
            'subject'     => $subject,
            'role'        => $s['role'],
            'matched_on'  => $on,
            'strength'    => $strength,
            'snippet'     => Str::limit($snippet, 220),
        ];
    }

    // ── Name handling ─────────────────────────────────────────────────────

    /** Words of a text: ASCII, lower case, punctuation stripped, single letters dropped. */
    public static function tokens(string $text): array
    {
        $t = strtolower(Str::ascii($text));
        $t = preg_replace('/[^a-z0-9]+/', ' ', $t);
        $out = [];
        foreach (explode(' ', $t) as $w) {
            if (strlen($w) >= 2) $out[] = $w;
        }
        return $out;
    }

    /** Canonical tokens of a client/shareholder name, with company noise removed. */
    private static function subjectTokens(string $name, string $kind): array
    {
        $tokens = array_map([self::class, 'canon'], array_filter(
            self::tokens($name),
            fn ($w) => $kind !== 'company' || !in_array($w, self::COMPANY_NOISE, true)
        ));
        return array_values(array_filter($tokens, fn ($t) => $t !== ''));
    }

    /**
     * Collapses the usual transliteration noise so spelling variants compare equal:
     * mohammed / muhammad / mohamad, hussein / hussain / husain, abdul / abdel ...
     */
    public static function canon(string $w): string
    {
        $w = strtolower($w);
        $w = strtr($w, ['kh' => 'k', 'gh' => 'g', 'sh' => 's', 'ch' => 'c', 'th' => 't', 'ph' => 'f', 'dh' => 'd', 'q' => 'k', 'z' => 's', 'w' => 'u']);
        $w = preg_replace('/[aeiouy]+/', 'a', $w);   // vowel runs are the unstable part of transliteration
        $w = preg_replace('/(.)\1+/', '$1', $w);     // double letters
        return $w;
    }

    private static function tokenMatches(string $subject, string $page): bool
    {
        if ($subject === $page) return true;
        $len = min(strlen($subject), strlen($page));
        return $len >= 5 && levenshtein($subject, $page) <= 1;
    }

    /**
     * Finds a stretch of $width page words where every subject word appears
     * (in any order), so words scattered across different alert entries don't
     * add up to a name. Returns [start, end) or null.
     */
    private static function findWindow(array $subject, array $page, array $positions, array $unique, int $width): ?array
    {
        $n = count($page);
        if ($n === 0) return null;

        // Anchor on the longest subject word; look it up by index instead of scanning the page
        usort($subject, fn ($a, $b) => strlen($b) <=> strlen($a));
        $anchor = $subject[0];

        $anchorPositions = [];
        foreach ($unique as $u) {
            if (self::tokenMatches($anchor, $u)) {
                array_push($anchorPositions, ...$positions[$u]);
            }
        }
        sort($anchorPositions);

        foreach ($anchorPositions as $i) {
            $start = max(0, $i - $width + 1);
            $end   = min($n, $i + $width);
            $slice = array_slice($page, $start, $end - $start);

            // Every subject word must be present within the window around the anchor
            $all = true;
            foreach ($subject as $t) {
                $found = false;
                foreach ($slice as $p) {
                    if (self::tokenMatches($t, $p)) { $found = true; break; }
                }
                if (!$found) { $all = false; break; }
            }
            if ($all) return [$start, $end];
        }
        return null;
    }
}
