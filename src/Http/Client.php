<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Http;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as Psr18Client;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tigusigalpa\Phemex\Config;
use Tigusigalpa\Phemex\Exceptions\ApiException;
use Tigusigalpa\Phemex\Exceptions\AuthenticationException;
use Tigusigalpa\Phemex\Exceptions\NotFoundException;
use Tigusigalpa\Phemex\Exceptions\RateLimitException;
use Tigusigalpa\Phemex\Exceptions\ValidationException;
use Tigusigalpa\Phemex\Signer;

/**
 * Framework-agnostic HTTP client that talks to the Phemex REST API.
 *
 * The client abstracts the transport layer behind PSR-18 interfaces, signs
 * private requests with HMAC SHA256, and handles transient retries.
 */
final class Client
{
    private readonly string $baseUri;

    private ?Psr18Client $resolvedClient = null;

    private ?RequestFactoryInterface $resolvedRequestFactory = null;

    private ?StreamFactoryInterface $resolvedStreamFactory = null;

    private readonly Signer $signer;

    /**
     * @var Closure(int|float): void
     */
    private readonly Closure $sleeper;

    /**
     * @param (callable(int|float): void)|null $sleeper Optional sleep handler (seconds). Defaults to usleep; override in tests.
     */
    public function __construct(
        private readonly Config $config,
        private readonly ?Psr18Client $httpClient = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        ?callable $sleeper = null,
    ) {
        $this->baseUri = rtrim($this->config->baseUri, '/');
        $this->signer = new Signer($this->config->apiSecret);
        $this->sleeper = $sleeper !== null
            ? Closure::fromCallable($sleeper)
            : static fn (int|float $seconds): mixed => usleep((int) ($seconds * 1_000_000));
    }

    /**
     * Send an HTTP request to the Phemex API.
     *
     * Query parameters for GET and DELETE requests and the JSON body for
     * POST/PUT/PATCH requests are both supplied through $params.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function send(string $method, string $uri, array $params = []): array
    {
        $method = strtoupper($method);
        $client = $this->httpClient ?? $this->resolvedClient ??= $this->defaultClient();
        $requestFactory = $this->requestFactory ?? $this->resolvedRequestFactory ??= new HttpFactory();
        $streamFactory = $this->streamFactory ?? $this->resolvedStreamFactory ??= new HttpFactory();

        [$url, $queryString, $body] = $this->preparePayload($method, $uri, $params);

        $request = $requestFactory
            ->createRequest($method, $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('User-Agent', 'tigusigalpa/phemex-php');

        if ($this->config->hasCredentials()) {
            $request = $this->signRequest($request, $method, $url, $queryString, $body);
        }

        if ($this->config->requestTracing !== null && $this->config->requestTracing !== '') {
            $request = $request->withHeader('x-phemex-request-tracing', $this->config->requestTracing);
        }

        if ($body !== '') {
            $request = $request->withBody($streamFactory->createStream($body));
        }

        return $this->executeWithRetries($client, $request);
    }

    /**
     * Build the full URL, query string, and request body from the parameters.
     *
     * @param array<string, mixed> $params
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function preparePayload(string $method, string $uri, array $params): array
    {
        $path = '/' . ltrim($uri, '/');
        $queryString = '';
        $body = '';

        if (in_array($method, ['GET', 'DELETE'], true)) {
            $params = $this->filterParams($params);
            if ($params !== []) {
                $queryString = $this->buildQuery($params);
                $path .= '?' . $queryString;
            }

            return [$this->baseUri . $path, $queryString, $body];
        }

        $params = $this->filterParams($params);
        if ($params !== []) {
            try {
                $body = json_encode($params, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new ApiException('Failed to encode request body: ' . $e->getMessage(), 0, $e);
            }
        }

        return [$this->baseUri . $path, $queryString, $body];
    }

    /**
     * Sign the request with HMAC SHA256.
     */
    private function signRequest(
        \Psr\Http\Message\RequestInterface $request,
        string $method,
        string $url,
        string $queryString,
        string $body,
    ): \Psr\Http\Message\RequestInterface {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $expiry = $this->signer->expiry(60);
        $signature = $this->signer->sign($method, $path, $queryString, $expiry, $body);

        return $request
            ->withHeader('x-phemex-access-token', $this->config->apiKey)
            ->withHeader('x-phemex-request-expiry', (string) $expiry)
            ->withHeader('x-phemex-request-signature', $signature);
    }

    /**
     * Execute the request with automatic retries on rate limits and server errors.
     *
     * @return array<string, mixed>
     */
    private function executeWithRetries(Psr18Client $client, \Psr\Http\Message\RequestInterface $request): array
    {
        $lastException = null;
        $maxAttempts = max(0, $this->config->retries);

        for ($attempt = 0; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = $client->sendRequest($request);
            } catch (ClientExceptionInterface $e) {
                throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
            }

            $status = $response->getStatusCode();

            if ($status >= 200 && $status < 300) {
                return $this->parseResponse($response);
            }

            if ($status === 429) {
                $lastException = $this->buildRateLimitException($response);

                if ($attempt < $maxAttempts) {
                    $this->sleepForRetry($response, $attempt);
                    continue;
                }

                throw $lastException;
            }

            if ($status >= 500) {
                $lastException = $this->buildApiException($response);

                if ($attempt < $maxAttempts) {
                    $this->sleepForBackoff($attempt);
                    continue;
                }

                throw $lastException;
            }

            throw $this->buildApiException($response);
        }

        throw $lastException ?? new ApiException('Unexpected HTTP error');
    }

    /**
     * Parse the JSON response body into an associative array.
     *
     * @return array<string, mixed>
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApiException('Failed to decode API response: ' . $e->getMessage(), 0, $e, $response);
        }

        if (!is_array($decoded)) {
            throw new ApiException('Unexpected API response format: expected JSON object', 0, null, $response);
        }

        return $decoded;
    }

    /**
     * Convert an HTTP error response into a typed exception.
     */
    private function buildApiException(ResponseInterface $response): ApiException
    {
        $status = $response->getStatusCode();
        $message = $this->extractErrorMessage($response);

        return match ($status) {
            401 => new AuthenticationException($message, $status, null, $response),
            400, 403 => new ValidationException($message, $status, null, $response),
            404 => new NotFoundException($message, $status, null, $response),
            default => new ApiException($message, $status, null, $response),
        };
    }

    private function buildRateLimitException(ResponseInterface $response): RateLimitException
    {
        $retryAfter = $this->readHeaderInt($response, 'Retry-After');
        $remaining = max(
            $this->readHeaderInt($response, 'X-RateLimit-Remaining'),
            $this->readHeaderInt($response, 'RateLimit-Remaining'),
        );

        return new RateLimitException(
            $this->extractErrorMessage($response),
            $response->getStatusCode(),
            null,
            $response,
            $retryAfter,
            $remaining,
        );
    }

    /**
     * Extract a human-readable error message from the response body.
     */
    private function extractErrorMessage(ResponseInterface $response): string
    {
        $body = (string) $response->getBody();

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                if (isset($decoded['msg']) && is_string($decoded['msg']) && $decoded['msg'] !== '') {
                    return $decoded['msg'];
                }
                if (isset($decoded['message']) && is_string($decoded['message']) && $decoded['message'] !== '') {
                    return $decoded['message'];
                }
                if (isset($decoded['error']) && is_string($decoded['error']) && $decoded['error'] !== '') {
                    return $decoded['error'];
                }
            }
        } catch (JsonException) {
            // Fall back to raw body.
        }

        if ($body !== '') {
            return $body;
        }

        return 'HTTP ' . $response->getStatusCode() . ' error';
    }

    private function readHeaderInt(ResponseInterface $response, string $name): int
    {
        $value = $response->getHeaderLine($name);
        if ($value === '') {
            return 0;
        }

        if (str_contains($value, ',')) {
            [$value] = explode(',', $value, 2);
        }

        $value = trim($value);
        if (is_numeric($value)) {
            return (int) $value;
        }

        $date = strtotime($value);
        if ($date !== false) {
            return max(0, $date - time());
        }

        return 0;
    }

    /**
     * Build a query string that supports repeated keys without array indices.
     *
     * Phemex expects `ordStatus=New&ordStatus=PartiallyFilled`, not the default
     * PHP array serialization with brackets.
     *
     * @param array<string, mixed> $params
     */
    private function buildQuery(array $params): string
    {
        $pairs = [];

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $pairs[] = rawurlencode((string) $key) . '=' . rawurlencode($this->stringifyValue($item));
                }
            } else {
                $pairs[] = rawurlencode((string) $key) . '=' . rawurlencode($this->stringifyValue($value));
            }
        }

        return implode('&', $pairs);
    }

    /**
     * Convert a scalar parameter value to its string representation.
     *
     * Booleans are serialized as `true`/`false` rather than PHP's default
     * `1`/empty string, which the Phemex API expects for boolean flags.
     */
    private function stringifyValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    /**
     * Remove null values from request parameters.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function filterParams(array $params): array
    {
        return array_filter($params, static fn ($value) => $value !== null);
    }

    private function sleepForRetry(ResponseInterface $response, int $attempt): void
    {
        $retryAfter = $this->readHeaderInt($response, 'Retry-After');
        $baseDelay = max(1, $this->config->retryDelay);
        $exponential = $baseDelay * (2 ** $attempt);

        $delay = $retryAfter > 0 ? $retryAfter : $exponential;

        ($this->sleeper)($delay);
    }

    private function sleepForBackoff(int $attempt): void
    {
        $baseDelay = max(1, $this->config->retryDelay);
        $delay = $baseDelay * (2 ** $attempt);

        ($this->sleeper)($delay);
    }

    private function defaultClient(): Psr18Client
    {
        return new GuzzleClient([
            'timeout' => $this->config->timeout,
            'http_errors' => false,
        ]);
    }
}
