<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tigusigalpa\Phemex\PhemexClient;

/**
 * Laravel facade for the Phemex API client.
 *
 * @mixin PhemexClient
 */
final class Phemex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PhemexClient::class;
    }
}
