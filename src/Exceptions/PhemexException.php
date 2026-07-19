<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Base exception for all Phemex client errors.
 */
class PhemexException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        protected readonly ?ResponseInterface $response = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * The underlying PSR-7 response, if available.
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
