<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the requested resource cannot be found.
 */
class NotFoundException extends ApiException
{
    public function __construct(
        string $message = 'Resource not found',
        int $code = 404,
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);
    }
}
