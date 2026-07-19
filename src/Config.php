<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex;

use InvalidArgumentException;

/**
 * Immutable configuration container for the Phemex API client.
 */
final class Config
{
    /**
     * @param string $apiKey Phemex API key. Optional for public market endpoints.
     * @param string $apiSecret Phemex API secret. Required for private endpoints.
     * @param string $baseUri Base URI for the Phemex REST API.
     * @param int $timeout Request timeout in seconds.
     * @param int $retries Number of retries for transient failures and rate limits.
     * @param int $retryDelay Base delay in seconds used for exponential backoff.
     * @param string|null $httpClient Optional service container binding for a PSR-18 client.
     * @param string|null $requestTracing Optional trace token sent with signed requests.
     */
    public function __construct(
        public readonly string $apiKey = '',
        public readonly string $apiSecret = '',
        public readonly string $baseUri = 'https://api.phemex.com',
        public readonly int $timeout = 30,
        public readonly int $retries = 3,
        public readonly int $retryDelay = 1,
        public readonly ?string $httpClient = null,
        public readonly ?string $requestTracing = null,
    ) {
        if ($this->baseUri === '') {
            throw new InvalidArgumentException('Phemex base URI cannot be empty.');
        }

        if ($this->timeout < 0) {
            throw new InvalidArgumentException('Timeout must be non-negative.');
        }

        if ($this->retries < 0) {
            throw new InvalidArgumentException('Retries must be non-negative.');
        }
    }

    /**
     * Build a Config instance from an associative array.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $apiKey = (string) ($config['api_key'] ?? $config['apiKey'] ?? '');
        $apiSecret = (string) ($config['api_secret'] ?? $config['apiSecret'] ?? '');
        $baseUri = (string) ($config['base_uri'] ?? $config['baseUri'] ?? 'https://api.phemex.com');
        $timeout = (int) ($config['timeout'] ?? 30);
        $retries = (int) ($config['retries'] ?? 3);
        $retryDelay = (int) ($config['retry_delay'] ?? $config['retryDelay'] ?? 1);
        $httpClient = $config['http_client'] ?? $config['httpClient'] ?? null;
        $requestTracing = $config['request_tracing'] ?? $config['requestTracing'] ?? null;

        return new self(
            $apiKey,
            $apiSecret,
            $baseUri,
            $timeout,
            $retries,
            $retryDelay,
            $httpClient !== null ? (string) $httpClient : null,
            $requestTracing !== null ? (string) $requestTracing : null,
        );
    }

    /**
     * Determine whether the client has credentials suitable for private endpoints.
     */
    public function hasCredentials(): bool
    {
        return $this->apiKey !== '' && $this->apiSecret !== '';
    }
}
