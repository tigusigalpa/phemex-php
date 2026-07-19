<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\Endpoints;

use Tigusigalpa\Phemex\Dto\ApiResponse;
use Tigusigalpa\Phemex\Http\Client as HttpClient;

/**
 * Asset and transfer endpoints.
 */
final class Assets
{
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Transfer funds between spot and futures wallets.
     *
     * @param array<string, mixed> $params Transfer parameters, e.g. amount, currency, from, to.
     *
     * @see https://phemex-docs.github.io/#transfer-between-spot-and-futures
     */
    public function transfer(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('POST', '/assets/transfer', $params));
    }

    /**
     * Query spot/futures transfer history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-transfer-history
     */
    public function transferHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/assets/transfer', $params));
    }

    /**
     * Universal transfer between wallets within an account.
     *
     * @param array<string, mixed> $params Transfer parameters per the universal transfer API.
     *
     * @see https://phemex-docs.github.io/#transfer-between-wallets-within-an-account
     */
    public function universalTransfer(array $params): ApiResponse
    {
        return new ApiResponse($this->http->send('POST', '/wallets/account/transfer', $params));
    }

    /**
     * Query deposit address for a currency.
     *
     * @param string $currency Currency code, e.g. BTC.
     *
     * @see https://phemex-docs.github.io/#query-deposit-address-information
     */
    public function depositAddress(string $currency): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/exchange/wallets/v2/depositAddress', [
            'currency' => $currency,
        ]));
    }

    /**
     * Query deposit history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. currency, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-deposit-history-records
     */
    public function depositHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/exchange/wallets/depositList', $params));
    }

    /**
     * Query withdraw history.
     *
     * @param array<string, mixed> $params Query parameters, e.g. currency, start, end, limit, offset.
     *
     * @see https://phemex-docs.github.io/#query-withdraw-history-records
     */
    public function withdrawHistory(array $params = []): ApiResponse
    {
        return new ApiResponse($this->http->send('GET', '/exchange/wallets/withdrawList', $params));
    }
}
