<?php
// app/Services/RssFeedService.php

namespace App\Services;

use App\Models\NewsItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssFeedService
{
    /**
     * Fetch, parse and store new items from a single RSS/Atom feed. With $filter on,
     * only items whose headline passes isRelevant() are stored; $limit counts stored items.
     *
     * @return array{fetched: int, created: int, error: ?string}
     */
    public function ingest(string $name, string $url, string $category, int $limit = 8, bool $filter = false): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; BlueArrowBot/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('RssFeedService: feed request failed', ['name' => $name, 'status' => $response->status()]);

                return ['fetched' => 0, 'created' => 0, 'error' => "HTTP {$response->status()}"];
            }

            $xml = @simplexml_load_string($response->body());

            if ($xml === false) {
                Log::warning('RssFeedService: could not parse XML', ['name' => $name]);

                return ['fetched' => 0, 'created' => 0, 'error' => 'invalid XML'];
            }

            $entries = $this->extractEntries($xml);
            $fetched = 0;
            $created = 0;

            // Filtered feeds drop most items, so look further down the list; others keep the old top-N
            $scan   = $filter ? $entries : array_slice($entries, 0, $limit);
            $cutoff = now()->subDays((int) config('content.max_age_days', 60));

            foreach ($scan as $entry) {
                if ($created >= $limit) {
                    break;
                }
                $fetched++;

                if (empty($entry['link']) || empty($entry['title'])) {
                    continue;
                }

                $title = $this->clean($entry['title']);

                if ($entry['published_at'] && $entry['published_at']->lt($cutoff)) {
                    continue;
                }

                if (self::isJunkTitle($title) || ($filter && ! self::isRelevant($title))) {
                    continue;
                }

                $source   = $entry['source'] ?: $name;
                $headline = Str::limit(self::headline($title, $source), 250, '');

                // Same story under a new URL (outlets re-publish) counts as a duplicate too
                $exists = NewsItem::where('source_url_hash', sha1($entry['link']))->exists()
                    || NewsItem::where('title', $headline)->where('published_at', '>=', now()->subDays(30))->exists();

                if ($exists) {
                    continue;
                }

                NewsItem::create([
                    'title'        => $headline,
                    'summary'      => Str::limit(self::summary($this->clean($entry['summary'] ?? ''), $headline, $source), 500),
                    'source_name'  => $source,
                    'source_url'   => $entry['link'],
                    'category'     => $filter ? self::classify($headline, $category) : $category,
                    'origin'       => 'rss',
                    'published_at' => $entry['published_at'] ?? now(),
                    'is_published' => true,
                ]);

                $created++;
            }

            return ['fetched' => $fetched, 'created' => $created, 'error' => null];
        } catch (\Exception $e) {
            Log::error('RssFeedService::ingest failed', ['name' => $name, 'error' => $e->getMessage()]);

            return ['fetched' => 0, 'created' => 0, 'error' => $e->getMessage()];
        }
    }

    /** Does this headline look like AML / sanctions / financial-crime news? (config content.relevance_pattern) */
    public static function isRelevant(string $title): bool
    {
        return (bool) preg_match(config('content.relevance_pattern'), $title);
    }

    /** News-outlet category from the headline (config content.category_patterns, first match wins). */
    public static function classify(string $headline, string $default): string
    {
        foreach (config('content.category_patterns', []) as $category => $pattern) {
            if (preg_match($pattern, $headline)) {
                return $category;
            }
        }

        return $default;
    }

    /** Google News titles end in " - Source"; the source is shown separately, so drop it. */
    public static function headline(string $title, ?string $source): string
    {
        if ($source && preg_match('/^(.*\S)\s+-\s+' . preg_quote($source, '/') . '$/iu', $title, $m)) {
            return $m[1];
        }

        return $title;
    }

    /** Google News "descriptions" are just the headline and source again; treat that as no summary. */
    public static function summary(string $summary, string $headline, ?string $source): string
    {
        $norm = fn (string $s) => mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $s));
        $rest = $source ? preg_replace('/\s*' . preg_quote($source, '/') . '\s*$/iu', '', $summary) : $summary;

        return $norm($rest) === '' || $norm($rest) === $norm($headline) ? '' : $summary;
    }

    /** A site page rather than a news item? (config content.junk_title_pattern; ignores the " - Source" suffix) */
    public static function isJunkTitle(string $title): bool
    {
        $headline = preg_replace('/\s+-\s+[^-]+$/u', '', $title);

        return (bool) preg_match(config('content.junk_title_pattern'), $headline);
    }

    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5)));
    }

    private function extractEntries(\SimpleXMLElement $xml): array
    {
        $entries = [];

        // RSS 2.0
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $sourceAttrs = $item->source ? $item->source->attributes() : null;

                $entries[] = [
                    'title'        => (string) $item->title,
                    'link'         => (string) $item->link,
                    'summary'      => (string) ($item->description ?? ''),
                    'source'       => (string) ($item->source ?? ($sourceAttrs['url'] ?? null)),
                    'published_at' => $this->parseDate((string) ($item->pubDate ?? '')),
                ];
            }

            return $entries;
        }

        // Atom
        if (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $link = '';
                foreach ($entry->link as $l) {
                    $attrs = $l->attributes();
                    if (! isset($attrs['rel']) || (string) $attrs['rel'] === 'alternate') {
                        $link = (string) $attrs['href'];
                        break;
                    }
                }

                $entries[] = [
                    'title'        => (string) $entry->title,
                    'link'         => $link,
                    'summary'      => (string) ($entry->summary ?? $entry->content ?? ''),
                    'source'       => null,
                    'published_at' => $this->parseDate((string) ($entry->updated ?? $entry->published ?? '')),
                ];
            }
        }

        return $entries;
    }

    private function parseDate(string $value): ?\Carbon\Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
