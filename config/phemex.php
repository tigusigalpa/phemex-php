<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Phemex API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Phemex API key and secret. Both are required for private endpoints
    | such as trading, wallets, transfers, and account information. Public
    | market endpoints can be queried without credentials.
    |
    */
    'api_key' => env('PHEMEX_API_KEY'),

    'api_secret' => env('PHEMEX_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Phemex API Base URI
    |--------------------------------------------------------------------------
    |
    | The base URI for all REST requests. Change this only if you are using
    | a custom gateway or Phemex announces a different endpoint.
    |
    */
    'base_uri' => env('PHEMEX_BASE_URI', 'https://api.phemex.com'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout in seconds for HTTP requests to the Phemex API.
    |
    */
    'timeout' => (int) env('PHEMEX_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retries
    |--------------------------------------------------------------------------
    |
    | Number of retries when the API responds with HTTP 429 (Too Many Requests)
    | or a transient server error. Set to 0 to disable retries.
    |
    */
    'retries' => (int) env('PHEMEX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Retry Delay
    |--------------------------------------------------------------------------
    |
    | Base delay in seconds used for exponential backoff between retries.
    |
    */
    'retry_delay' => (int) env('PHEMEX_RETRY_DELAY', 1),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    |
    | Optional service container binding for a PSR-18 compatible HTTP client.
    | When left empty, Guzzle will be used automatically.
    |
    */
    'http_client' => env('PHEMEX_HTTP_CLIENT'),

    /*
    |--------------------------------------------------------------------------
    | Request Tracing Token
    |--------------------------------------------------------------------------
    |
    | An optional token sent as x-phemex-request-tracing. Useful when working
    | with Phemex support to diagnose latency issues. Must be under 40 bytes.
    |
    */
    'request_tracing' => env('PHEMEX_REQUEST_TRACING'),
];
