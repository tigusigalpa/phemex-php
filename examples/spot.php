<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key' => getenv('PHEMEX_API_KEY') ?: '',
    'api_secret' => getenv('PHEMEX_API_SECRET') ?: '',
]);

// Place a limit buy order.
$order = $client->spot()->createOrder([
    'symbol' => 'BTCUSDT',
    'clOrdID' => 'my-order-' . time(),
    'side' => 'Buy',
    'ordType' => 'Limit',
    'orderQty' => '0.001',
    'priceEp' => 65000000000, // scaled price, verifyEp on the exchange
    'timeInForce' => 'GoodTillCancel',
]);

print_r($order->raw);

// Query open orders.
$open = $client->spot()->queryOpenOrders('BTCUSDT');
print_r($open->data());

// Amend the order by client order ID.
$amended = $client->spot()->amendOrder([
    'symbol' => 'BTCUSDT',
    'clOrdID' => 'my-order-' . time(),
    'priceEp' => 64000000000,
]);
print_r($amended->raw);

// Cancel the order.
$canceled = $client->spot()->cancelOrder([
    'symbol' => 'BTCUSDT',
    'clOrdID' => 'my-order-' . time(),
]);
print_r($canceled->raw);

// Query spot wallets.
$wallets = $client->spot()->wallets();
print_r($wallets->data());
