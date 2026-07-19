<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key' => getenv('PHEMEX_API_KEY') ?: '',
    'api_secret' => getenv('PHEMEX_API_SECRET') ?: '',
]);

// Place a USDⓈ-M limit order.
$order = $client->usdm()->createOrder([
    'symbol' => 'BTCUSDT',
    'clOrdID' => 'usdm-order-' . time(),
    'side' => 'Buy',
    'ordType' => 'Limit',
    'orderQty' => 1,
    'priceEp' => 65000000000,
    'timeInForce' => 'GoodTillCancel',
]);
print_r($order->raw);

// Query open USDⓈ-M orders.
$open = $client->usdm()->queryOpenOrders('BTCUSDT');
print_r($open->data());

// Query account positions.
$positions = $client->usdm()->accountPositions();
print_r($positions->data());

// Set leverage.
$leverage = $client->usdm()->setLeverage([
    'symbol' => 'BTCUSDT',
    'leverage' => 10,
]);
print_r($leverage->raw);

// Query USDⓈ-M trade history.
$trades = $client->usdm()->tradeHistory(['symbol' => 'BTCUSDT', 'limit' => 10]);
print_r($trades->data());
