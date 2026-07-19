<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex;

/**
 * Generates HMAC SHA256 request signatures for the Phemex API.
 *
 * The signing material is constructed according to the official Phemex
 * documentation: the URL path (including the leading slash), the raw query
 * string (without the leading question mark), the request expiry timestamp
 * in seconds, and the request body for POST/PUT requests.
 */
final class Signer
{
    public function __construct(
        private readonly string $apiSecret,
    ) {
    }

    /**
     * Generate a Unix epoch timestamp in seconds for request expiry.
     *
     * The Phemex documentation recommends an expiry of roughly one minute
     * from the current time.
     *
     * @param int $offsetSeconds Number of seconds until the request expires.
     */
    public function expiry(int $offsetSeconds = 60): int
    {
        return (int) (time() + max(0, $offsetSeconds));
    }

    /**
     * Create an HMAC SHA256 signature for a request.
     *
     * @param string $method HTTP method in any case.
     * @param string $path URL path including the leading slash, e.g. /accounts/accountPositions.
     * @param string $queryString Raw query string without the leading question mark, or an empty string.
     * @param int $expiry Unix epoch seconds when the request expires.
     * @param string $body JSON-encoded request body, or an empty string for GET/DELETE requests.
     */
    public function sign(string $method, string $path, string $queryString, int $expiry, string $body = ''): string
    {
        $method = strtoupper($method);
        $message = $path . $queryString . (string) $expiry . $body;

        return hash_hmac('sha256', $message, $this->apiSecret, false);
    }
}
