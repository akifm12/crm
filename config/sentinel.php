<?php
// config/sentinel.php

return [
    'base_url' => env('SENTINEL_URL', 'http://127.0.0.1:8085/api'),
    'email'    => env('SENTINEL_EMAIL'),
    'password' => env('SENTINEL_PASSWORD'),
];
