<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Endpoints;

use Tigusigalpa\Phemex\Dto\ApiResponse;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * USDⓈ-M perpetual contract endpoints.
 */
final class Usdm
{
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Place a new USDⓈ-M order.
     *
     * @param array<string, mixed> $params Order parameters per the Phemex USDⓈ-M API.
     *
     * @see https://phemex-docs.github.io/#place-order-http-put-prefered-2
     */
    public function createOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/g-orders/create', $params));
    }

    /**
     * Amend an existing USDⓈ-M order.
     *
     * @param array<string, mixed> $params Amend parameters per the Phemex USDⓈ-M API.
     *
     * @see https://phemex-docs.github.io/#amend-order-by-orderid
     */
    public function amendOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/g-orders/replace', $params));
    }

    /**
     * Cancel a single USDⓈ-M order.
     *
     * @param array<string, mixed> $params Cancel parameters, e.g. symbol, orderID, clOrdID.
     *
     * @see https://phemex-docs.github.io/#cancel-single-order-by-orderid
     */
    public function cancelOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/g-orders/cancel', $params));
    }

    /**
     * Bulk cancel USDⓈ-M orders.
     *
     * @param array<string, mixed> $params Cancel parameters, e.g. symbol and optional orderIDs.
     *
     * @see https://phemex-docs.github.io/#bulk-cancel-orders-2
     */
    public function bulkCancel(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/g-orders', $params));
    }

    /**
     * Cancel all USDⓈ-M orders for a symbol.
     *
     * @param string $symbol Trading symbol, e.g. BTCUSDT.
     *
     * @see https://phemex-docs.github.io/#cancel-all-orders-2
     */
    public function cancelAllOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/g-orders/all', ['symbol' => $symbol]));
    }

    /**
     * Query open USDⓈ-M orders by symbol.
     *
     * @param string $symbol Trading symbol.
     *
     * @see https://phemex-docs.github.io/#query-open-orders-by-symbol-2
     */
    public function queryOpenOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/g-orders/activeList', ['symbol' => $symbol]));
    }

    /**
     * Query account positions for USDⓈ-M contracts.
     *
     * @see https://phemex-docs.github.io/#query-account-positions
     */
    public function accountPositions(): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/g-accounts/accountPositions'));
    }

    /**
     * Switch position mode (One-way / Hedge) for a symbol.
     *
     * @param array<string, mixed> $params e.g. symbol, posMode.
     *
     * @see https://phemex-docs.github.io/#switch-position-mode-synchronously
     */
    public function switchPosMode(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/g-positions/switch-pos-mode-sync', $params));
    }

    /**
     * Set leverage for a symbol.
     *
     * @param array<string, mixed> $params e.g. symbol, leverage.
     *
     * @see https://phemex-docs.github.io/#set-leverage-2
     */
    public function setLeverage(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/g-positions/leverage', $params));
    }

    /**
     * Assign position balance in isolated margin mode.
     *
     * @param array<string, mixed> $params e.g. symbol, posBalance, posBalanceEv.
     *
     * @see https://phemex-docs.github.io/#assign-position-balance
     */
    public function assignPositionBalance(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('POST', '/g-positions/assign', $params));
    }

    /**
     * Query closed USDⓈ-M orders by symbol.
     *
     * @param array<string, mixed> $params Query parameters, e.g. symbol, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-closed-orders-by-symbol-2
     */
    public function closedOrders(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/exchange/order/v2/orderList', $params));
    }

    /**
     * Query USDⓈ-M trade history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. symbol, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-trades-history
     */
    public function tradeHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/api-data/g-futures/trades', $params));
    }
}
