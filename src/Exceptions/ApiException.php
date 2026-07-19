<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Generic error returned by the Phemex API or the HTTP transport.
 */
class ApiException extends PhemexException
{
    public function __construct(
        string $message = 'Phemex API error',
        int $code = 0,
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);
    }
}
