<?php

declare(strict_types = 1);

namespace Plattry\Event\Network\Event;

use Plattry\Event\Dispatch\StoppableEvent;
use Plattry\Event\Network\ConnectionInterface;

/**
 * An abstract connection event instance.
 */
abstract class EventAbstract extends StoppableEvent implements EventInterface
{
    /**
     * The connection instance.
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * The constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}