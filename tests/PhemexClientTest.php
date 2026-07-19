<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\Phemex\Config;
use Tigusigalpa\Phemex\PhemexClient;

final class PhemexClientTest extends TestCase
{
    private function createClientWithResponse(array $payload, ?Config $config = null): array
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(200, [], json_encode($payload)));

        $config ??= Config::fromArray([
            'api_key' => 'key',
            'api_secret' => 'secret',
        ]);
        $client = new PhemexClient($config, $mock);

        return [$client, $mock];
    }

    public function testMarketProducts(): void
    {
        [$client, $mock] = $this->createClientWithResponse(['code' => 0, 'msg' => '', 'data' => ['products' => []]]);

        $response = $client->market()->products();

        self::assertSame(0, $response->code());
        self::assertSame('GET', $mock->getLastRequest()?->getMethod());
        self::assertStringContainsString('/public/products', (string) $mock->getLastRequest()?->getUri());
    }

    public function testSpotCreateOrder(): void
    {
        [$client, $mock] = $this->createClientWithResponse(['code' => 0, 'msg' => '', 'data' => []]);

        $response = $client->spot()->createOrder([
            'symbol' => 'BTCUSDT',
            'side' => 'Buy',
            'ordType' => 'Limit',
            'orderQty' => '0.001',
            'priceEp' => 65000000000,
        ]);

        self::assertTrue($response->isSuccess());
        $request = $mock->getLastRequest();
        self::assertSame('PUT', $request?->getMethod());
        self::assertStringContainsString('/spot/orders/create', (string) $request?->getUri());
    }

    public function testStaticCreateFactory(): void
    {
        $client = PhemexClient::create([
            'api_key' => 'key',
            'api_secret' => 'secret',
        ]);

        self::assertInstanceOf(PhemexClient::class, $client);
        self::assertSame('key', $client->config->apiKey);
    }
}
