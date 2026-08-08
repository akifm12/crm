<?php
// app/Console/Commands/FetchRegulatoryFeeds.php

namespace App\Console\Commands;

use App\Services\RssFeedService;
use Illuminate\Console\Command;

class FetchRegulatoryFeeds extends Command
{
    protected $signature = 'content:fetch-rss';

    protected $description = 'Aggregate configured RSS/Atom feeds into news_items (auto-publishes new items)';

    public function __construct(private RssFeedService $rss)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $feeds = config('content.feeds', []);
        $limit = (int) config('content.max_items_per_feed', 8);

        if (empty($feeds)) {
            $this->warn('No feeds configured in config/content.php.');

            return 0;
        }

        $totalCreated = 0;

        foreach ($feeds as $feed) {
            $result = $this->rss->ingest($feed['name'], $feed['url'], $feed['category'], $limit);

            if ($result['error']) {
                $this->warn("  ✗ {$feed['name']}: {$result['error']}");

                continue;
            }

            $this->info("  ✓ {$feed['name']}: {$result['created']} new / {$result['fetched']} fetched");
            $totalCreated += $result['created'];
        }

        $this->newLine();
        $this->info("Done — {$totalCreated} new item(s) published.");

        return 0;
    }
}
