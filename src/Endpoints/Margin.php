<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Endpoints;

use Tigusigalpa\Phemex\Dto\ApiResponse;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * Margin trading endpoints.
 */
final class Margin
{
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Place a new margin order.
     *
     * @param array<string, mixed> $params Order parameters per the Phemex margin API.
     *
     * @see https://phemex-docs.github.io/#place-order-http-put-prefered-4
     */
    public function createOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('PUT', '/margin-trade/orders/create', $params));
    }

    /**
     * Cancel a margin order.
     *
     * @param array<string, mixed> $params Cancel parameters, e.g. symbol, orderID, clOrdID.
     *
     * @see https://phemex-docs.github.io/#cancel-order-2
     */
    public function cancelOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/margin-trade/orders', $params));
    }

    /**
     * Cancel all margin orders for a symbol.
     *
     * @param string $symbol Trading symbol.
     *
     * @see https://phemex-docs.github.io/#cancel-all-order-by-symbol-2
     */
    public function cancelAllOrders(string $symbol): ApiResponse
    {
        return new ApiResponse($this->http->send('DELETE', '/margin-trade/orders/all', ['symbol' => $symbol]));
    }

    /**
     * Query a single open margin order.
     *
     * @param array<string, mixed> $params Query parameters, e.g. symbol, orderID, clOrdID.
     *
     * @see https://phemex-docs.github.io/#query-open-order-by-order-id-or-client-order-id-2
     */
    public function queryOpenOrder(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/margin-trade/orders/active', $params));
    }

    /**
     * Query margin borrow history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. currency, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-margin-borrow-history-records
     */
    public function borrowHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/margin/borrow', $params));
    }

    /**
     * Post a margin borrow request.
     *
     * @param array<string, mixed> $params Borrow parameters, e.g. currency, amount.
     *
     * @see https://phemex-docs.github.io/#post-margin-borrow-request
     */
    public function borrow(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('POST', '/margin/borrow', $params));
    }

    /**
     * Post a margin payback request.
     *
     * @param array<string, mixed> $params Payback parameters, e.g. currency, amount.
     *
     * @see https://phemex-docs.github.io/#post-margin-payback-history
     */
    public function payback(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('POST', '/margin/payback', $params));
    }
}
