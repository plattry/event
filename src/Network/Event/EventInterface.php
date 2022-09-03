<?php

declare(strict_types = 1);

namespace Plattry\Event\Network\Event;

use Plattry\Event\Network\ConnectionInterface;

/**
 * Describe a connection event instance.
 */
interface EventInterface
{
    /**
     * Get the connection instance.
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;
}