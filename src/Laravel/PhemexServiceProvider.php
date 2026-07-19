<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Laravel;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface as Psr18Client;
use Tigusigalpa\Phemex\Config;
use Tigusigalpa\Phemex\PhemexClient;

/**
 * Laravel service provider for the Phemex PHP client.
 */
final class PhemexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/phemex.php',
            'phemex',
        );

        $this->app->singleton(PhemexClient::class, function ($app): PhemexClient {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('phemex', []);

            $httpClient = null;
            if (!empty($config['http_client']) && is_string($config['http_client'])) {
                $httpClient = $app->make($config['http_client']);
                if (!$httpClient instanceof Psr18Client) {
                    throw new \InvalidArgumentException(
                        'Configured Phemex HTTP client must implement ' . Psr18Client::class,
                    );
                }
            }

            $phemexConfig = Config::fromArray($config);

            return new PhemexClient($phemexConfig, $httpClient);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/phemex.php' => $this->app->configPath('phemex.php'),
            ], 'phemex-config');
        }
    }
}
