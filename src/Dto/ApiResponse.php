<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Dto;

/**
 * Standard Phemex response envelope returned by the majority of REST endpoints.
 *
 * Most endpoints reply with `{ "code": <int>, "msg": <string>, "data": <mixed> }`.
 * Market-data endpoints under `/md` use {@see MarketDataResponse} instead.
 */
final class ApiResponse extends Dto
{
    /**
     * API result code. `0` usually indicates success.
     */
    public function code(): int
    {
        $code = $this->get(['code']);

        return is_int($code) ? $code : (int) $code;
    }

    /**
     * Human-readable message from the API.
     */
    public function msg(): string
    {
        $msg = $this->get(['msg']);

        return is_string($msg) ? $msg : '';
    }

    /**
     * Typed data payload. Shape depends on the endpoint.
     *
     * @return array<string, mixed>|list<mixed>|null
     */
    public function data(): ?array
    {
        $data = $this->get(['data']);

        return is_array($data) ? $data : null;
    }

    /**
     * Whether the API reported a successful response.
     */
    public function isSuccess(): bool
    {
        return $this->code() === 0;
    }
}
