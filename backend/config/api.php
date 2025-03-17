<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of API requests a user or IP
    | can make per minute. Set to 0 to disable rate limiting.
    |
    */
    'rate_limit' => env('API_RATE_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | API Versions
    |--------------------------------------------------------------------------
    |
    | List of supported API versions and their status.
    |
    */
    'versions' => [
        'v1' => [
            'status' => 'active',
            'deprecated' => false,
            'sunset_date' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Documentation
    |--------------------------------------------------------------------------
    |
    | Configuration for API documentation and OpenAPI specification.
    |
    */
    'documentation' => [
        'enabled' => env('API_DOCS_ENABLED', true),
        'path' => env('API_DOCS_PATH', 'api/docs'),
        'version' => env('API_DOCS_VERSION', '1.0.0'),
    ],
];