<?php

declare(strict_types=1);

namespace Tigusigalpa\Phemex\WebSocket;

use Closure;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket as RatchetConnection;
use Ratchet\RFC6455\Messaging\MessageInterface;
use RuntimeException;

/**
 * Optional WebSocket client for real-time Phemex market and account streams.
 *
 * This class requires `ratchet/pawl` to be installed. It is suggested in
 * composer.json rather than required, so the package stays lightweight for
 * users who only need the REST API.
 */
final class Client
{
    private ?RatchetConnection $connection = null;

    public function __construct(
        private readonly string $endpoint = 'wss://vstream.phemex.com/ws',
    ) {
        if (!class_exists(Connector::class)) {
            throw new RuntimeException(
                'The WebSocket client requires ratchet/pawl. Install it with: composer require ratchet/pawl',
            );
        }
    }

    /**
     * Establish a WebSocket connection and register message/error handlers.
     *
     * @param Closure(MessageInterface, RatchetConnection): void $onMessage
     * @param Closure(\Exception|null): void|null $onClose
     */
    public function connect(Closure $onMessage, ?Closure $onClose = null): void
    {
        $connector = new Connector();
        $connector($this->endpoint)->then(
            function (RatchetConnection $conn) use ($onMessage, $onClose): void {
                $this->connection = $conn;
                $conn->on('message', $onMessage);
                $conn->on('close', function ($code = null, $reason = null) use ($onClose): void {
                    if ($onClose !== null) {
                        $onClose($code === null ? null : new \Exception($reason ?? 'Connection closed'));
                    }
                });
            },
            function (\Throwable $e): void {
                throw new RuntimeException('WebSocket connection failed: ' . $e->getMessage(), 0, $e);
            },
        );
    }

    /**
     * Subscribe to one or more Phemex channels.
     *
     * @param list<string> $topics
     */
    public function subscribe(array $topics): void
    {
        $this->send([
            'id' => time(),
            'method' => 'subscribe',
            'params' => $topics,
        ]);
    }

    /**
     * Unsubscribe from one or more Phemex channels.
     *
     * @param list<string> $topics
     */
    public function unsubscribe(array $topics): void
    {
        $this->send([
            'id' => time(),
            'method' => 'unsubscribe',
            'params' => $topics,
        ]);
    }

    /**
     * Send a raw JSON message to the WebSocket server.
     *
     * @param array<string, mixed> $payload
     */
    public function send(array $payload): void
    {
        if ($this->connection === null) {
            throw new RuntimeException('WebSocket connection has not been established. Call connect() first.');
        }

        $this->connection->send(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * Close the WebSocket connection.
     */
    public function close(): void
    {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}
