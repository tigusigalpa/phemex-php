<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the API responds with HTTP 429 (Too Many Requests).
 */
class RateLimitException extends ApiException
{
    public function __construct(
        string $message = 'Rate limit exceeded',
        int $code = 429,
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null,
        protected readonly int $retryAfter = 0,
        protected readonly int $remaining = 0,
    ) {
        parent::__construct($message, $code, $previous, $response);
    }

    /**
     * Number of seconds the client should wait before retrying.
     */
    public function retryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Remaining quota, if reported by the API.
     */
    public function remaining(): int
    {
        return $this->remaining;
    }
}
