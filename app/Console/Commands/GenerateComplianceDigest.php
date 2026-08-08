<?php
// app/Console/Commands/GenerateComplianceDigest.php

namespace App\Console\Commands;

use App\Models\NewsItem;
use App\Services\AnthropicService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateComplianceDigest extends Command
{
    protected $signature = 'content:ai-digest';

    protected $description = 'Draft a short AI-assisted compliance insight and publish it to news_items (skips gracefully if no API key)';

    public function __construct(private AnthropicService $anthropic)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->anthropic->isConfigured()) {
            $this->warn('ANTHROPIC_API_KEY is not set — skipping AI digest (RSS aggregation is unaffected).');

            return 0;
        }

        $topics = config('content.digest_topics', []);

        if (empty($topics)) {
            $this->warn('No digest topics configured in config/content.php.');

            return 0;
        }

        $recentTitles = NewsItem::where('origin', 'ai_digest')
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('title')
            ->implode(' | ');

        $topic = collect($topics)->shuffle()->first();

        $prompt = <<<PROMPT
            You are drafting a short compliance-insight article for Blue Arrow Management Consultants, a UAE AML/regulatory
            compliance consultancy serving DNFBPs (bullion dealers, real estate brokers, company service providers, accountants).

            Topic: {$topic}

            Recently published article titles (avoid repeating these): {$recentTitles}

            Write a short, plain-English, practical article (250-400 words) aimed at DNFBP compliance officers in the UAE.
            Do not invent specific dates, statistics, or claim specific regulations changed unless it's well-established
            general knowledge — keep it educational and general, not a claim of breaking news.

            Respond with ONLY a raw JSON object (no markdown fences, no commentary) in this exact shape:
            {"title": "...", "summary": "one or two sentence summary, under 240 characters", "body": "the full article body, plain text with paragraph breaks as \\n\\n"}
            PROMPT;

        $result = $this->anthropic->complete($prompt);

        if (! $result['success']) {
            $this->error("AI digest generation failed: {$result['error']}");

            return 1;
        }

        $parsed = json_decode(trim($result['text']), true);

        if (! is_array($parsed) || empty($parsed['title']) || empty($parsed['summary'])) {
            Log::error('GenerateComplianceDigest: could not parse AI response', ['raw' => $result['text']]);
            $this->error('Could not parse AI response as JSON — see log for raw output.');

            return 1;
        }

        NewsItem::create([
            'title'        => $parsed['title'],
            'summary'      => $parsed['summary'],
            'body'         => $parsed['body'] ?? null,
            'source_name'  => 'BA-Digest',
            'source_url'   => null,
            'category'     => 'insight',
            'origin'       => 'ai_digest',
            'published_at' => now(),
            'is_published' => true,
        ]);

        $this->info("Published: {$parsed['title']}");

        return 0;
    }
}
