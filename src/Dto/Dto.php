<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Dto;

/**
 * Base data-transfer object that preserves the raw API payload.
 */
abstract class Dto
{
    /**
     * @param array<string, mixed> $raw Untouched API response body.
     */
    public function __construct(public readonly array $raw)
    {
    }

    /**
     * Access a nested value from the raw payload using dot notation.
     *
     * @param array<int, string> $path
     */
    protected function get(array $path, mixed $default = null): mixed
    {
        $value = $this->raw;

        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
