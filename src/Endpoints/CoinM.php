<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Endpoints;

use Tigusigalpa\Phemex\Dto\ApiResponse;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * Coin-M perpetual contract endpoints.
 */
final class CoinM
{
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Place a new Coin-M order.
     *
     * @param array<string, mixed> $params Order parameters per the Phemex Coin-M API.
     *
     * @see https://phemex-docs.github.io/#place-order-http-put-prefered
     */
    public function createOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/orders/create', $params));
    }

    /**
     * Amend an existing Coin-M order.
     *
     * @param array<string, mixed> $params Amend parameters per the Phemex Coin-M API.
     *
     * @see https://phemex-docs.github.io/#amend-order-by-order-id
     */
    public function amendOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/orders/replace', $params));
    }

    /**
     * Cancel a Coin-M order by order ID or client order ID.
     *
     * @param array<string, mixed> $params Cancel parameters, e.g. symbol, orderID, clOrdID.
     *
     * @see https://phemex-docs.github.io/#cancel-order-by-order-id-or-client-order-id
     */
    public function cancelOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/orders/cancel', $params));
    }

    /**
     * Bulk cancel Coin-M orders.
     *
     * @param array<string, mixed> $params Cancel parameters, e.g. symbol and optional orderIDs.
     *
     * @see https://phemex-docs.github.io/#bulk-cancel-orders
     */
    public function bulkCancel(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/orders', $params));
    }

    /**
     * Cancel all Coin-M orders for a symbol.
     *
     * @param string $symbol Trading symbol, e.g. BTCUSD.
     *
     * @see https://phemex-docs.github.io/#cancel-all-orders
     */
    public function cancelAllOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/orders/all', ['symbol' => $symbol]));
    }

    /**
     * Query open Coin-M orders by symbol.
     *
     * @param string $symbol Trading symbol.
     *
     * @see https://phemex-docs.github.io/#query-open-orders-by-symbol
     */
    public function queryOpenOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/orders/activeList', ['symbol' => $symbol]));
    }

    /**
     * Query trading account and positions for Coin-M contracts.
     *
     * @see https://phemex-docs.github.io/#query-trading-account-and-positions
     */
    public function accountPositions(): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/accounts/accountPositions'));
    }

    /**
     * Set leverage for a Coin-M symbol.
     *
     * @param array<string, mixed> $params e.g. symbol, leverage.
     *
     * @see https://phemex-docs.github.io/#set-leverage
     */
    public function setLeverage(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/positions/leverage', $params));
    }

    /**
     * Set position risk limit for a Coin-M symbol.
     *
     * @param array<string, mixed> $params e.g. symbol, riskLimit.
     *
     * @see https://phemex-docs.github.io/#set-position-risklimit
     */
    public function setRiskLimit(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/positions/riskLimit', $params));
    }

    /**
     * Assign position balance in isolated margin mode for Coin-M.
     *
     * @param array<string, mixed> $params e.g. symbol, posBalance, posBalanceEv.
     *
     * @see https://phemex-docs.github.io/#assign-position-balance-in-isolated-marign-mode
     */
    public function assignPositionBalance(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('POST', '/positions/assign', $params));
    }
}
