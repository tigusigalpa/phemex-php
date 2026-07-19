<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Dto;

/**
 * Response wrapper for Phemex market-data endpoints under `/md`.
 *
 * These endpoints typically return an envelope such as
 * `{ "error": null, "id": 0, "result": <mixed> }`.
 */
final class MarketDataResponse extends Dto
{
    /**
     * Error code or message. `null` indicates success.
     */
    public function error(): ?string
    {
        $error = $this->get(['error']);

        return $error === null ? null : (string) $error;
    }

    /**
     * Request correlation id.
     */
    public function id(): int
    {
        $id = $this->get(['id'], 0);

        return is_int($id) ? $id : (int) $id;
    }

    /**
     * Result payload. Shape depends on the endpoint.
     *
     * @return array<string, mixed>|list<mixed>|null
     */
    public function result(): ?array
    {
        $result = $this->get(['result']);

        return is_array($result) ? $result : null;
    }

    /**
     * Whether the API reported a successful response.
     */
    public function isSuccess(): bool
    {
        return $this->error() === null;
    }
}
