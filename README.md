# Phemex PHP

![Phemex PHP SDK](https://i.postimg.cc/2SVT6yDg/phemex-api-php.jpg)

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/tigusigalpa/phemex-php.svg)](https://packagist.org/packages/tigusigalpa/phemex-php)

> A typed PHP client for the [Phemex](https://phemex.com/) exchange API. Drop it into any PHP 8.1+ project, and if
> you're on Laravel 10–13 it just works — no extra setup.

I got tired of hand-rolling cURL calls, computing HMAC signatures, and bolting on retry logic every time I touched the
Phemex API. So this library does all of that for you and hands back a clean, object-oriented interface. You write
strategy; it handles the plumbing.

```php
use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key' => getenv('PHEMEX_API_KEY'),
    'api_secret' => getenv('PHEMEX_API_SECRET'),
]);

$ticker = $client->market()->ticker24h('BTCUSDT');
print_r($ticker->result());
```

---

## What's in the box

- **Runs anywhere.** The core is plain PHP 8.1+ built on PSR-18 — no framework required.
- **Laravel, out of the box.** Auto-discovered service provider, publishable config, and a `Phemex` facade.
- **Signing you don't think about.** Private endpoints get HMAC SHA256 signatures automatically, straight from Phemex's own algorithm.
- **Your HTTP client, your rules.** Guzzle ships by default, but swap in any PSR-18 implementation you like.
- **Responses that won't break.** Return values are DTOs that keep the raw payload around, so when Phemex adds a field, you can still reach it.
- **Retries handled for you.** Rate limits (HTTP 429) and flaky server errors are retried with exponential backoff.
- **Exceptions that make sense.** `AuthenticationException`, `RateLimitException`, `NotFoundException`, `ValidationException`, and `ApiException`.
- **Real-time when you need it.** Optional WebSocket streams via `ratchet/pawl`.

---

## Installation

```bash
composer require tigusigalpa/phemex-php
```

### Laravel

The service provider is auto-discovered, so there's nothing to register. Publish the config file whenever you want to tweak the defaults:

```bash
php artisan vendor:publish --provider="Tigusigalpa\Phemex\Laravel\PhemexServiceProvider"
```

Then set your environment variables:

```env
PHEMEX_API_KEY=your_api_key
PHEMEX_API_SECRET=your_api_secret
```

---

## Configuration

After publishing, `config/phemex.php` contains:

```php
return [
    'api_key'    => env('PHEMEX_API_KEY'),
    'api_secret' => env('PHEMEX_API_SECRET'),
    'base_uri'   => env('PHEMEX_BASE_URI', 'https://api.phemex.com'),
    'timeout'    => 30,
    'retries'    => 3,
    'retry_delay' => 1,
];
```

Prefer your own PSR-18 client — maybe with custom middleware, logging, or metrics? Just bind it in a service provider:

```php
$this->app->bind(\Psr\Http\Client\ClientInterface::class, MyPsr18Client::class);
```

---

## Quick start

### Standalone PHP

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key'    => getenv('PHEMEX_API_KEY'),
    'api_secret' => getenv('PHEMEX_API_SECRET'),
]);

$book = $client->market()->orderBook('BTCUSDT');
print_r($book->result());

$wallets = $client->spot()->wallets();
print_r($wallets->data());
```

### Laravel Facade

```php
use Tigusigalpa\Phemex\Laravel\Facades\Phemex;

$ticker = Phemex::market()->ticker24h('BTCUSDT');
$order  = Phemex::spot()->createOrder([
    'symbol'    => 'BTCUSDT',
    'side'      => 'Buy',
    'ordType'   => 'Limit',
    'orderQty'  => '0.001',
    'priceEp'   => 65000000000,
]);
```

### Error handling

```php
use Tigusigalpa\Phemex\Exceptions\AuthenticationException;
use Tigusigalpa\Phemex\Exceptions\NotFoundException;
use Tigusigalpa\Phemex\Exceptions\RateLimitException;

try {
    $positions = $client->usdm()->accountPositions();
} catch (AuthenticationException $e) {
    // Invalid or missing credentials
} catch (NotFoundException $e) {
    // Resource not found
} catch (RateLimitException $e) {
    // $e->retryAfter() and $e->remaining() are available
}
```

---

## Endpoints

Each endpoint group lives behind its own method on the client:

| Group                  | Client method       | Covered endpoints                                                                                                                               |
|------------------------|---------------------|-------------------------------------------------------------------------------------------------------------------------------------------------|
| **Market Data**        | `$client->market()` | products, time, orderBook, fullBook, kline, trades, 24h ticker, 24h all tickers, funding-rate-history                                           |
| **Spot Trading**       | `$client->spot()`   | create, amend, cancel, cancel-all, open order, open orders, wallets, order history, trade history                                               |
| **USDⓈ-M Contracts**   | `$client->usdm()`   | create, amend, cancel, bulk cancel, cancel all, open orders, positions, switch pos mode, leverage, assign balance, closed orders, trade history |
| **Coin-M Contracts**   | `$client->coinM()`  | create, amend, cancel, bulk cancel, cancel all, open orders, account/positions, leverage, risk limit, assign balance                            |
| **Margin Trading**     | `$client->margin()` | create, cancel, cancel all, open order, borrow history, borrow, payback                                                                         |
| **Assets & Transfers** | `$client->assets()` | transfer, transfer history, universal transfer, deposit address, deposit/withdraw history                                                       |

Want to see these in action? Check out the `examples/` directory.

---

## WebSocket streams

Need real-time data? Install `ratchet/pawl` and you're set:

```bash
composer require ratchet/pawl
```

```php
use Tigusigalpa\Phemex\WebSocket\Client;

$ws = new Client('wss://vstream.phemex.com/ws');

$ws->connect(
    onMessage: function ($message, $conn) {
        echo $message->getPayload() . PHP_EOL;
    },
    onClose: function ($e) {
        echo 'closed' . PHP_EOL;
    },
);

$ws->subscribe(['orderbook.subscribe', 'trade.subscribe']);
```

---

## Running the tests

```bash
composer install
vendor/bin/phpunit
```

Everything runs against a mocked HTTP client, so you don't need an API key or a network connection to run the suite.

---

## License

MIT. See [LICENSE](LICENSE) for details.

Built by [Igor Sazonov](mailto:sovletig@gmail.com). Spotted a bug or missing an endpoint? Open an issue or send a PR —
I'd love the help.
