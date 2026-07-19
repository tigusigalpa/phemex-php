<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key' => getenv('PHEMEX_API_KEY') ?: '',
    'api_secret' => getenv('PHEMEX_API_SECRET') ?: '',
]);

// Place a Coin-M limit order.
$order = $client->coinM()->createOrder([
    'symbol' => 'BTCUSD',
    'clOrdID' => 'coinm-order-' . time(),
    'side' => 'Buy',
    'ordType' => 'Limit',
    'orderQty' => 100,
    'priceEp' => 65000000000,
    'timeInForce' => 'GoodTillCancel',
]);
print_r($order->raw);

// Query open Coin-M orders.
$open = $client->coinM()->queryOpenOrders('BTCUSD');
print_r($open->data());

// Query trading account and positions.
$positions = $client->coinM()->accountPositions();
print_r($positions->data());

// Set leverage.
$leverage = $client->coinM()->setLeverage([
    'symbol' => 'BTCUSD',
    'leverage' => 25,
]);
print_r($leverage->raw);
