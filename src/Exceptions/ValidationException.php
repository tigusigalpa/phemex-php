<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the API reports a validation or parameter error.
 */
class ValidationException extends ApiException
{
    public function __construct(
        string $message = 'Validation error',
        int $code = 400,
        ?\Throwable $previous = null,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);
    }
}
