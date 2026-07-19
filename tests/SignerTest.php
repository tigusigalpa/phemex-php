<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Tests;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\Phemex\Signer;

final class SignerTest extends TestCase
{
    public function testExpiryIsInTheFuture(): void
    {
        $signer = new Signer('secret');
        $expiry = $signer->expiry();

        self::assertGreaterThan(time(), $expiry);
        self::assertLessThanOrEqual(time() + 60, $expiry);
    }

    public function testSignGetRequest(): void
    {
        $signer = new Signer('secret');
        $signature = $signer->sign('GET', '/accounts/accountPositions', 'currency=BTC', 1575735514);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }

    public function testSignPutRequest(): void
    {
        $signer = new Signer('secret');
        $body = '{"symbol":"BTCUSD","orderQty":7}';
        $signature = $signer->sign('PUT', '/orders', '', 1575735514, $body);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }
}
