<?php

return [
    'redis' => [
        'host' => env('PROMETHEUS_REDIS_HOST', 'redis'),
        'port' => (int) env('PROMETHEUS_REDIS_PORT', 6379),
        'database' => (int) env('PROMETHEUS_REDIS_DATABASE', 1),
    ],

    'scrape_token' => env('PROMETHEUS_SCRAPE_TOKEN'),
];
