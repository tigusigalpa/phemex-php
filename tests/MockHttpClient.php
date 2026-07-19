<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Tests;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockHttpClient implements ClientInterface
{
    /** @var list<ResponseInterface> */
    private array $responses = [];

    /** @var list<RequestInterface> */
    private array $requests = [];

    public function addResponse(ResponseInterface $response): void
    {
        $this->responses[] = $response;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->responses === []) {
            throw new \RuntimeException('No mock responses left');
        }

        return array_shift($this->responses);
    }

    /**
     * @return list<RequestInterface>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getLastRequest(): ?RequestInterface
    {
        $count = count($this->requests);

        return $count > 0 ? $this->requests[$count - 1] : null;
    }
}
