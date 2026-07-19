<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex;

use Psr\Http\Client\ClientInterface as Psr18Client;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tigusigalpa\Phemex\Endpoints\Assets;
use Tigusigalpa\Phemex\Endpoints\CoinM;
use Tigusigalpa\Phemex\Endpoints\Margin;
use Tigusigalpa\Phemex\Endpoints\Market;
use Tigusigalpa\Phemex\Endpoints\Spot;
use Tigusigalpa\Phemex\Endpoints\Usdm;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * Main entry point for the Phemex PHP client.
 *
 * The client is framework-agnostic: create it directly with an API key and
 * secret, or let the Laravel service provider build it from config.
 */
final class PhemexClient
{
    private readonly HttpClient $http;

    public function __construct(
        public readonly Config $config,
        ?Psr18Client $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->http = new HttpClient($config, $httpClient, $requestFactory, $streamFactory);
    }

    /**
     * Create a client from an array of options.
     *
     * @param array<string, mixed>|Config $config
     */
    public static function create(Config|array $config, ?Psr18Client $httpClient = null): self
    {
        if (!$config instanceof Config) {
            $config = Config::fromArray($config);
        }

        return new self($config, $httpClient);
    }

    /**
     * Public market-data endpoints.
     */
    public function market(): Market
    {
        return new Market($this->http);
    }

    /**
     * Spot trading endpoints.
     */
    public function spot(): Spot
    {
        return new Spot($this->http);
    }

    /**
     * USDⓈ-M perpetual contract endpoints.
     */
    public function usdm(): Usdm
    {
        return new Usdm($this->http);
    }

    /**
     * Coin-M perpetual contract endpoints.
     */
    public function coinM(): CoinM
    {
        return new CoinM($this->http);
    }

    /**
     * Margin trading endpoints.
     */
    public function margin(): Margin
    {
        return new Margin($this->http);
    }

    /**
     * Asset and transfer endpoints.
     */
    public function assets(): Assets
    {
        return new Assets($this->http);
    }
}
