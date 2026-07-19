<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Phemex\PhemexClient;

$client = PhemexClient::create([
    'api_key' => getenv('PHEMEX_API_KEY') ?: '',
    'api_secret' => getenv('PHEMEX_API_SECRET') ?: '',
]);

// Transfer between spot and futures wallets.
$transfer = $client->assets()->transfer([
    'currency' => 'BTC',
    'amount' => '0.01',
    'from' => 'spot',
    'to' => 'future',
]);
print_r($transfer->raw);

// Query transfer history.
$history = $client->assets()->transferHistory(['limit' => 10]);
print_r($history->data());

// Universal transfer between wallets within an account.
$universal = $client->assets()->universalTransfer([
    'currency' => 'USDT',
    'amount' => '100',
    'from' => 'spot',
    'to' => 'future',
]);
print_r($universal->raw);

// Deposit address.
$deposit = $client->assets()->depositAddress('BTC');
print_r($deposit->data());

// Deposit and withdraw history.
$deposits = $client->assets()->depositHistory(['limit' => 5]);
$withdrawals = $client->assets()->withdrawHistory(['limit' => 5]);
print_r($deposits->data());
print_r($withdrawals->data());
