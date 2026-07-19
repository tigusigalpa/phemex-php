<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

// Public market data does not require authentication.
$client = PhemexClient::create([
    'base_uri' => 'https://api.phemex.com',
]);

// Query available products and server time.
$products = $client->market()->products();
$time = $client->market()->time();

echo 'Products code: ' . $products->code() . PHP_EOL;
echo 'Server time: ' . ($time->data()['timestamp'] ?? 'n/a') . PHP_EOL;

// Order book for BTCUSD.
$book = $client->market()->orderBook('BTCUSD');
if ($book->isSuccess()) {
    print_r($book->result());
}

// Recent klines for BTCUSDT.
$klines = $client->market()->kline(
    symbol: 'BTCUSDT',
    resolution: '1h',
    limit: 10,
);
print_r($klines->result());

// 24-hour ticker for all symbols.
$ticker = $client->market()->ticker24hAll();
print_r($ticker->result());
