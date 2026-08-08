<?php
// config/content.php

return [

    // RSS/Atom sources aggregated by `content:fetch-rss`. Each is fetched
    // independently — a dead feed is logged and skipped, never blocks the rest.
    //
    // Deliberately restricted to (a) primary UAE/regional regulators and
    // (b) a short allow-list of major, reliable news outlets — via Google
    // News `site:` search restricted to ONE domain per feed with tight topic
    // keywords (combining multiple `site:` domains in one query made Google
    // News' boolean parsing unreliable and let irrelevant/off-topic results
    // through). No generic/unrestricted news search and no vendor blogs.
    //
    // Deliberately NO `when:` date filter — testing showed it makes Google
    // News fall back to loose/irrelevant matches when few results exist in
    // a narrow window. Recency is handled by our own dedup (source_url_hash)
    // converging to "new since last run" on each scheduled fetch instead.
    'feeds' => [
        // ── Regulators (primary sources) ────────────────────────────────
        [
            'name'     => 'UAE Securities & Commodities Authority (SCA)',
            'url'      => 'https://news.google.com/rss/search?q=site:sca.gov.ae%20(fine%20OR%20circular%20OR%20notice%20OR%20regulation%20OR%20violation)&hl=en-US&gl=US&ceid=US:en',
            'category' => 'regulatory',
        ],
        [
            'name'     => 'UAE Central Bank (CBUAE)',
            'url'      => 'https://news.google.com/rss/search?q=site:centralbank.ae%20(circular%20OR%20%22anti-money%20laundering%22%20OR%20sanctions%20OR%20notice)&hl=en-US&gl=US&ceid=US:en',
            'category' => 'regulatory',
        ],
        [
            'name'     => 'ADGM / FSRA',
            'url'      => 'https://news.google.com/rss/search?q=site:adgm.com%20(%22money%20laundering%22%20OR%20sanctions%20OR%20DNFBP%20OR%20fine%20OR%20notice)&hl=en-US&gl=US&ceid=US:en',
            'category' => 'regulatory',
        ],
        [
            'name'     => 'DFSA (DIFC)',
            'url'      => 'https://news.google.com/rss/search?q=site:dfsa.ae%20(fine%20OR%20notice%20OR%20consultation%20OR%20%22money%20laundering%22)&hl=en-US&gl=US&ceid=US:en',
            'category' => 'regulatory',
        ],

        // ── Trusted news outlets (one domain per feed, allow-listed) ────
        [
            'name'     => 'Reuters — UAE AML & Sanctions',
            'url'      => 'https://news.google.com/rss/search?q=site:reuters.com%20UAE%20(%22money%20laundering%22%20OR%20DNFBP%20OR%20%22financial%20crime%22)&hl=en-US&gl=US&ceid=US:en',
            'category' => 'aml',
        ],
        [
            'name'     => 'Bloomberg — UAE AML & Sanctions',
            'url'      => 'https://news.google.com/rss/search?q=site:bloomberg.com%20UAE%20%22money%20laundering%22&hl=en-US&gl=US&ceid=US:en',
            'category' => 'aml',
        ],
        [
            'name'     => 'Gulf News — AML & Compliance',
            'url'      => 'https://news.google.com/rss/search?q=site:gulfnews.com%20%22money%20laundering%22%20OR%20AML%20OR%20DNFBP&hl=en-US&gl=US&ceid=US:en',
            'category' => 'aml',
        ],
        [
            'name'     => 'Khaleej Times — AML & Compliance',
            'url'      => 'https://news.google.com/rss/search?q=site:khaleejtimes.com%20%22money%20laundering%22%20OR%20AML%20OR%20DNFBP&hl=en-US&gl=US&ceid=US:en',
            'category' => 'aml',
        ],
        [
            'name'     => 'The National — AML & Compliance',
            'url'      => 'https://news.google.com/rss/search?q=site:thenationalnews.com%20%22money%20laundering%22%20OR%20AML%20OR%20DNFBP&hl=en-US&gl=US&ceid=US:en',
            'category' => 'aml',
        ],
    ],

    // Max items imported per feed run, to keep aggregation fast & bounded.
    'max_items_per_feed' => 8,

    // Rotating topics for `content:ai-digest` — short, plain-English compliance
    // commentary tied to Blue Arrow's actual sectors. One topic is drafted per run.
    'digest_topics' => [
        'Why sanctions re-screening of existing customers matters for UAE DNFBPs, not just new onboarding',
        'A plain-English guide to the AED 55,000 goAML DPMSR reporting threshold for precious metals dealers',
        'What UAE real estate brokers need to know about source-of-funds verification',
        'Beneficial ownership (UBO) declarations: common mistakes company service providers make',
        'How accounting and audit firms should structure a risk-based AML approach',
        'CAHRA (Countries with AML/CFT deficiencies) screening — what changed recently and why it matters',
        'Suspicious Transaction Reports (STRs): when and how DNFBPs should file with the UAE FIU',
        'Practical AML training cadence for small and mid-size DNFBPs',
    ],

    'anthropic' => [
        'model'      => 'claude-sonnet-5',
        'max_tokens' => 1024,
    ],

];
