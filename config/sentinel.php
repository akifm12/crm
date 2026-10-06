<?php
// config/sentinel.php — Sentinel AML API (screening engine shared with aml.bluearrow.ae)

return [
    'base_url' => env('SENTINEL_URL', 'http://127.0.0.1:8085/api'),
    // Service key bound to the portal's Sentinel user; created in Sentinel's api_keys
    'api_key'  => env('SENTINEL_API_KEY'),
];
