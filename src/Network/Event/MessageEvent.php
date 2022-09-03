<?php

declare(strict_types = 1);

namespace Plattry\Event\Network\Event;

use Plattry\Event\Network\ConnectionInterface;

/**
 * A message-event instance, triggered on a complete packet arrives from a connection.
 */
class MessageEvent extends EventAbstract
{
    /**
     * The message data, raw or decoded by protocol.
     * @var mixed
     */
    protected mixed $data;

    /**
     * The constructor.
     * @param ConnectionInterface $connection
     * @param mixed $data
     */
    public function __construct(ConnectionInterface $connection, mixed $data)
    {
        parent::__construct($connection);
        $this->data = $data;
    }

    /**
     * Get the data.
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}
