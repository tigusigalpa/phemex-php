<?php

declare(strict_types=1);

/**
 * Laravel usage examples.
 *
 * 1. Publish the config once:
 *    php artisan vendor:publish --provider="Tigusigalpa\Phemex\Laravel\PhemexServiceProvider"
 *
 * 2. Set your credentials in .env:
 *    PHEMEX_API_KEY=...
 *    PHEMEX_API_SECRET=...
 *
 * 3. Use the facade anywhere in your application:
 */

use Tigusigalpa\Phemex\Laravel\Facades\Phemex;

// Public market data.
$ticker = Phemex::market()->ticker24h('BTCUSDT');

// Spot wallet balance.
$wallets = Phemex::spot()->wallets();

// Place a USDⓈ-M order.
$order = Phemex::usdm()->createOrder([
    'symbol' => 'BTCUSDT',
    'side' => 'Buy',
    'ordType' => 'Market',
    'orderQty' => 1,
]);

// Error handling.
use Tigusigalpa\Phemex\Exceptions\AuthenticationException;
use Tigusigalpa\Phemex\Exceptions\RateLimitException;

try {
    $positions = Phemex::usdm()->accountPositions();
} catch (AuthenticationException $e) {
    // Handle invalid credentials.
} catch (RateLimitException $e) {
    // Back off for $e->retryAfter() seconds.
}
