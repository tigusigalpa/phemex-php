<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key' => getenv('PHEMEX_API_KEY') ?: '',
    'api_secret' => getenv('PHEMEX_API_SECRET') ?: '',
]);

// Place a margin order.
$order = $client->margin()->createOrder([
    'symbol' => 'BTCUSDT',
    'clOrdID' => 'margin-order-' . time(),
    'side' => 'Buy',
    'ordType' => 'Limit',
    'orderQty' => '0.001',
    'priceEp' => 65000000000,
    'timeInForce' => 'GoodTillCancel',
]);
print_r($order->raw);

// Query open margin order.
$open = $client->margin()->queryOpenOrder([
    'symbol' => 'BTCUSDT',
    'clOrdID' => 'margin-order-' . time(),
]);
print_r($open->data());

// Borrow funds.
$borrow = $client->margin()->borrow([
    'currency' => 'USDT',
    'amount' => '100',
]);
print_r($borrow->raw);

// Payback funds.
$payback = $client->margin()->payback([
    'currency' => 'USDT',
    'amount' => '100',
]);
print_r($payback->raw);
