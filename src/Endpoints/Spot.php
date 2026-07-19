<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Endpoints;

use Tigusigalpa\Phemex\Dto\ApiResponse;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * Spot trading endpoints.
 */
final class Spot
{
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Place a new spot order.
     *
     * @param array<string, mixed> $params Order parameters per the Phemex spot API.
     *
     * @see https://phemex-docs.github.io/#place-order-http-put-prefered-3
     */
    public function createOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/spot/orders/create', $params));
    }

    /**
     * Amend an existing spot order.
     *
     * @param array<string, mixed> $params Amend parameters per the Phemex spot API.
     *
     * @see https://phemex-docs.github.io/#amend-order
     */
    public function amendOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/spot/orders', $params));
    }

    /**
     * Cancel a spot order by order ID or client order ID.
     *
     * @param array<string, mixed> $params Cancel parameters, e.g. symbol, orderID, clOrdID.
     *
     * @see https://phemex-docs.github.io/#cancel-order
     */
    public function cancelOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/spot/orders', $params));
    }

    /**
     * Cancel all spot orders for a symbol.
     *
     * @param string $symbol Trading symbol, e.g. BTCUSDT.
     *
     * @see https://phemex-docs.github.io/#cancel-all-order-by-symbol
     */
    public function cancelAllOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/spot/orders/all', ['symbol' => $symbol]));
    }

    /**
     * Query a single open spot order by order ID or client order ID.
     *
     * @param array<string, mixed> $params Query parameters, e.g. symbol, orderID, clOrdID.
     *
     * @see https://phemex-docs.github.io/#query-open-order-by-order-id-or-client-order-id
     */
    public function queryOpenOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/spot/orders/active', $params));
    }

    /**
     * Query all open spot orders for a symbol.
     *
     * @param string $symbol Trading symbol.
     *
     * @see https://phemex-docs.github.io/#query-all-open-orders-by-symbol
     */
    public function queryOpenOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/spot/orders', ['symbol' => $symbol]));
    }

    /**
     * Query spot wallets.
     *
     * @see https://phemex-docs.github.io/#query-wallets
     */
    public function wallets(): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/spot/wallets'));
    }

    /**
     * Query spot order history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. symbol, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-order-history
     */
    public function orderHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/api-data/spots/orders', $params));
    }

    /**
     * Query spot trade history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. symbol, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-trade-history
     */
    public function tradeHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/api-data/spots/trades', $params));
    }
}
