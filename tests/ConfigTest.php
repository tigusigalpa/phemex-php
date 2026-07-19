<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\Phemex\Config;

final class ConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = Config::fromArray([
            'api_key' => 'key',
            'api_secret' => 'secret',
        ]);

        self::assertSame('key', $config->apiKey);
        self::assertSame('secret', $config->apiSecret);
        self::assertSame('https://api.phemex.com', $config->baseUri);
        self::assertSame(30, $config->timeout);
        self::assertSame(3, $config->retries);
        self::assertSame(1, $config->retryDelay);
        self::assertTrue($config->hasCredentials());
    }

    public function testPublicOnlyConfigHasNoCredentials(): void
    {
        $config = Config::fromArray([]);

        self::assertSame('', $config->apiKey);
        self::assertSame('', $config->apiSecret);
        self::assertFalse($config->hasCredentials());
    }

    public function testEmptyBaseUriThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Config::fromArray(['base_uri' => '']);
    }

    public function testNegativeTimeoutThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Config::fromArray(['timeout' => -1]);
    }
}
