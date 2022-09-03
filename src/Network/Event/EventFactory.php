<?php

declare(strict_types = 1);

namespace Plattry\Event\Network\Event;

use Plattry\Event\Network\ConnectionInterface;

/**
 * An event factory instance, for creating connection event instance.
 */
class EventFactory
{
    /**
     * Create a close-event instance.
     * @param ConnectionInterface $connection
     * @return CloseEvent
     */
    public static function createCloseEvent(ConnectionInterface $connection): CloseEvent
    {
        return new CloseEvent($connection);
    }

    /**
     * Create a connect-event instance.
     * @param ConnectionInterface $connection
     * @return ConnectEvent
     */
    public static function createConnectEvent(ConnectionInterface $connection): ConnectEvent
    {
        return new ConnectEvent($connection);
    }

    /**
     * Create a message-event instance.
     * @param ConnectionInterface $connection
     * @param mixed $data
     * @return MessageEvent
     */
    public static function createMessageEvent(ConnectionInterface $connection, mixed $data): MessageEvent
    {
        return new MessageEvent($connection, $data);
    }

    /**
     * Create an error-event instance.
     * @param ConnectionInterface $connection
     * @return ErrorEvent
     */
    public static function createErrorEvent(ConnectionInterface $connection): ErrorEvent
    {
        return new ErrorEvent($connection);
    }
}
