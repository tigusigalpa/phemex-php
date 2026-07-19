<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\Phemex\Config;
use Tigusigalpa\Phemex\Exceptions\NotFoundException;
use Tigusigalpa\Phemex\Exceptions\RateLimitException;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

final class HttpClientTest extends TestCase
{
    public function testPublicRequestDoesNotSendSignatureHeaders(): void
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(200, [], json_encode(['code' => 0, 'msg' => ''])));

        $client = new HttpClient(Config::fromArray([]), $mock);
        $client->send('GET', '/public/products');

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertFalse($request->hasHeader('x-phemex-access-token'));
        self::assertFalse($request->hasHeader('x-phemex-request-signature'));
    }

    public function testPrivateRequestAddsSignatureHeaders(): void
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(200, [], json_encode(['code' => 0, 'msg' => ''])));

        $config = Config::fromArray([
            'api_key' => 'api-key',
            'api_secret' => 'api-secret',
        ]);
        $client = new HttpClient($config, $mock);
        $client->send('GET', '/accounts/accountPositions', ['currency' => 'BTC']);

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame(['api-key'], $request->getHeader('x-phemex-access-token'));
        self::assertTrue($request->hasHeader('x-phemex-request-expiry'));
        self::assertTrue($request->hasHeader('x-phemex-request-signature'));
        self::assertStringContainsString('currency=BTC', (string) $request->getUri());
    }

    public function testPostRequestBodyIsSigned(): void
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(200, [], json_encode(['code' => 0, 'msg' => ''])));

        $config = Config::fromArray([
            'api_key' => 'api-key',
            'api_secret' => 'api-secret',
        ]);
        $client = new HttpClient($config, $mock);
        $client->send('POST', '/assets/transfer', ['currency' => 'BTC', 'amount' => '0.1']);

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('{"currency":"BTC","amount":"0.1"}', (string) $request->getBody());
        self::assertTrue($request->hasHeader('x-phemex-request-signature'));
    }

    public function testRepeatedQueryKeysAreSerializedCorrectly(): void
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(200, [], json_encode(['code' => 0, 'msg' => ''])));

        $client = new HttpClient(Config::fromArray([]), $mock);
        $client->send('GET', '/orders/activeList', [
            'ordStatus' => ['New', 'PartiallyFilled', 'Untriggered'],
            'symbol' => 'BTCUSD',
        ]);

        $uri = (string) $mock->getLastRequest()?->getUri();
        self::assertStringContainsString('ordStatus=New&ordStatus=PartiallyFilled&ordStatus=Untriggered', $uri);
        self::assertStringContainsString('symbol=BTCUSD', $uri);
    }

    public function testNotFoundException(): void
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(404, [], json_encode(['msg' => 'Not found'])));

        $client = new HttpClient(Config::fromArray([]), $mock);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');

        $client->send('GET', '/unknown');
    }

    public function testRateLimitExceptionExposesRetryAfter(): void
    {
        $mock = new MockHttpClient();
        $mock->addResponse(new Response(429, ['Retry-After' => '5'], json_encode(['msg' => 'Rate limit'])));
        $mock->addResponse(new Response(429, ['Retry-After' => '5'], json_encode(['msg' => 'Rate limit'])));
        $mock->addResponse(new Response(429, ['Retry-After' => '5'], json_encode(['msg' => 'Rate limit'])));
        $mock->addResponse(new Response(429, ['Retry-After' => '5'], json_encode(['msg' => 'Rate limit'])));

        $config = Config::fromArray(['retries' => 3]);
        $client = new HttpClient($config, $mock);

        $this->expectException(RateLimitException::class);

        $client->send('GET', '/public/products');
    }
}
