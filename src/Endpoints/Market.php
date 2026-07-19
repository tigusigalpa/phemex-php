<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Endpoints;

use Tigusigalpa\Phemex\Dto\ApiResponse;
use Tigusigalpa\Phemex\Dto\MarketDataResponse;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * Public market-data endpoints.
 */
final class Market
{
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Query available products and their metadata.
     *
     * @see https://phemex-docs.github.io/#query-product-information
     */
    public function products(): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/public/products'));
    }

    /**
     * Query the Phemex server time.
     *
     * @see https://phemex-docs.github.io/#query-server-time
     */
    public function time(): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/public/time'));
    }

    /**
     * Query the order book for a symbol.
     *
     * @param string $symbol Trading symbol, e.g. BTCUSD.
     *
     * @see https://phemex-docs.github.io/#query-order-book
     */
    public function orderBook(string $symbol): MarketDataResponse
    {
        return new MarketDataResponse($this->http->send('GET', '/md/orderbook', ['symbol' => $symbol]));
    }

    /**
     * Query the full order book for a symbol.
     *
     * @param string $symbol Trading symbol, e.g. BTCUSD.
     *
     * @see https://phemex-docs.github.io/#query-full-order-book
     */
    public function fullBook(string $symbol): MarketDataResponse
    {
        return new MarketDataResponse($this->http->send('GET', '/md/fullbook', ['symbol' => $symbol]));
    }

    /**
     * Query kline/candlestick data.
     *
     * @param string $symbol Trading symbol.
     * @param string $resolution Kline interval, e.g. 1m, 5m, 1h, 1d.
     * @param int|null $from Start timestamp in milliseconds.
     * @param int|null $to End timestamp in milliseconds.
     * @param int|null $limit Maximum number of records.
     *
     * @see https://phemex-docs.github.io/#query-kline
     */
    public function kline(
        string $symbol,
        string $resolution = '1h',
        ?int $from = null,
        ?int $to = null,
        ?int $limit = null,
    ): MarketDataResponse {
        return new MarketDataResponse($this->http->send('GET', '/exchange/public/md/v2/kline', [
            'symbol' => $symbol,
            'resolution' => $resolution,
            'from' => $from,
            'to' => $to,
            'limit' => $limit,
        ]));
    }

    /**
     * Query recent trades for a symbol.
     *
     * @param string $symbol Trading symbol.
     *
     * @see https://phemex-docs.github.io/#query-recent-trades
     */
    public function trades(string $symbol): MarketDataResponse
    {
        return new MarketDataResponse($this->http->send('GET', '/md/trade', ['symbol' => $symbol]));
    }

    /**
     * Query the 24-hour ticker for a symbol.
     *
     * @param string $symbol Trading symbol.
     *
     * @see https://phemex-docs.github.io/#query-24-hours-ticker
     */
    public function ticker24h(string $symbol): MarketDataResponse
    {
        return new MarketDataResponse($this->http->send('GET', '/md/v3/ticker/24hr', ['symbol' => $symbol]));
    }

    /**
     * Query the 24-hour ticker for all symbols.
     *
     * @see https://phemex-docs.github.io/#query-24-hours-ticker-for-all-symbols
     */
    public function ticker24hAll(): MarketDataResponse
    {
        return new MarketDataResponse($this->http->send('GET', '/md/v3/ticker/24hr/all'));
    }

    /**
     * Query funding rate history.
     *
     * @param string $symbol Trading symbol.
     * @param int|null $start Start timestamp in milliseconds.
     * @param int|null $end End timestamp in milliseconds.
     * @param int|null $limit Maximum number of records.
     * @param int|null $offset Offset for pagination.
     *
     * @see https://phemex-docs.github.io/#query-funding-rate-history
     */
    public function fundingRateHistory(
        string $symbol,
        ?int $start = null,
        ?int $end = null,
        ?int $limit = null,
        ?int $offset = null,
    ): ApiResponse {
        return new ApiResponse($this->http->send('GET', '/api-data/public/data/funding-rate-history', [
            'symbol' => $symbol,
            'start' => $start,
            'end' => $end,
            'limit' => $limit,
            'offset' => $offset,
        ]));
    }
}
