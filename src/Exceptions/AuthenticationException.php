<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the API rejects the provided credentials or signature.
 */
class AuthenticationException extends ApiException
{
    public function __construct(
        string $message = 'Authentication failed',
        int $code = 401,
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);
    }
}
