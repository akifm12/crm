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
     * Fetch, parse and store new items from a single RSS/Atom feed.
     *
     * @return array{fetched: int, created: int, error: ?string}
     */
    public function ingest(string $name, string $url, string $category, int $limit = 8): array
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

            foreach (array_slice($entries, 0, $limit) as $entry) {
                $fetched++;

                if (empty($entry['link']) || empty($entry['title'])) {
                    continue;
                }

                $exists = NewsItem::where('source_url_hash', sha1($entry['link']))->exists();

                if ($exists) {
                    continue;
                }

                $title   = $this->clean($entry['title']);
                $summary = $this->clean($entry['summary'] ?? '') ?: $title;

                NewsItem::create([
                    'title'        => Str::limit($title, 250, ''),
                    'summary'      => Str::limit($summary, 500),
                    'source_name'  => $entry['source'] ?? $name,
                    'source_url'   => $entry['link'],
                    'category'     => $category,
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
