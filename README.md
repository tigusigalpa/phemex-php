# Phemex PHP

![Phemex PHP SDK](https://i.postimg.cc/2SVT6yDg/phemex-api-php.jpg)

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/tigusigalpa/phemex-php.svg)](https://packagist.org/packages/tigusigalpa/phemex-php)

> A modern, strictly typed PHP client for the [Phemex](https://phemex.com/) cryptocurrency exchange API. Works in any
> PHP 8.1+ project and ships with Laravel 10–13 integration out of the box.

Trading crypto should not mean wrestling with raw cURL, HMAC signatures, and retry logic. This library wraps the Phemex
REST API behind a clean, object-oriented interface so you can focus on strategy instead of plumbing.

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

## What you get

- **Framework-agnostic core.** The client is plain PHP 8.1+ and uses PSR-18 for HTTP, so it runs anywhere.
- **First-class Laravel support.** Auto-discovered service provider, publishable config, and a `Phemex` facade.
- **Proper HMAC SHA256 signing.** Private endpoints are signed automatically using the official Phemex algorithm.
- **Bring your own HTTP client.** Guzzle is used by default, but any PSR-18 implementation can be injected.
- **Typed, future-proof responses.** Return values are DTOs that preserve the raw payload, so newly added API fields are
  always accessible.
- **Automatic retries.** Rate limits (HTTP 429) and transient server errors are retried with exponential backoff.
- **Clear exception hierarchy.** `AuthenticationException`, `RateLimitException`, `NotFoundException`,
  `ValidationException`, and `ApiException`.
- **WebSocket ready.** Optional real-time stream support via `ratchet/pawl`.

---

## Installation

```bash
composer require tigusigalpa/phemex-php
```

### Laravel

The service provider is auto-discovered. Publish the config file when you want to customize defaults:

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

Want to use your own PSR-18 client (with custom middleware, logging, or metrics)? Bind it in a service provider:

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

Every endpoint group is available through a dedicated method on the client.

| Group                  | Client method       | Covered endpoints                                                                                                                               |
|------------------------|---------------------|-------------------------------------------------------------------------------------------------------------------------------------------------|
| **Market Data**        | `$client->market()` | products, time, orderBook, fullBook, kline, trades, 24h ticker, 24h all tickers, funding-rate-history                                           |
| **Spot Trading**       | `$client->spot()`   | create, amend, cancel, cancel-all, open order, open orders, wallets, order history, trade history                                               |
| **USDⓈ-M Contracts**   | `$client->usdm()`   | create, amend, cancel, bulk cancel, cancel all, open orders, positions, switch pos mode, leverage, assign balance, closed orders, trade history |
| **Coin-M Contracts**   | `$client->coinM()`  | create, amend, cancel, bulk cancel, cancel all, open orders, account/positions, leverage, risk limit, assign balance                            |
| **Margin Trading**     | `$client->margin()` | create, cancel, cancel all, open order, borrow history, borrow, payback                                                                         |
| **Assets & Transfers** | `$client->assets()` | transfer, transfer history, universal transfer, deposit address, deposit/withdraw history                                                       |

For concrete usage, see the `examples/` directory.

---

## WebSocket streams

Optional WebSocket support is available when you install `ratchet/pawl`:

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

The suite uses a mocked HTTP client, so no API key or network access is required.

---

## License

MIT. See [LICENSE](LICENSE) for details.

Built by [Igor Sazonov](mailto:sovletig@gmail.com). Found a bug or missing an endpoint? Open an issue or a PR —
contributions are welcome.
