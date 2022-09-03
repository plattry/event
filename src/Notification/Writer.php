<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

use Ev;
use EvIo;

/**
 * An io-writer watcher instance.
 */
class Writer extends WatcherAbstract
{
    /**
     * @inheritDoc
     */
    protected static function key(mixed $id): int|string
    {
        return intval($id);
    }

    /**
     * Add a sub-watcher with file describer.
     * @param mixed $fd
     * @param mixed $callback
     * @param string|array|null $data
     * @param int $priority
     * @return void
     */
    public function add(mixed $fd, mixed $callback, string|array|null $data = null, int $priority = 0): void
    {
        $this->pool[static::key($fd)] = $this->loop->io(
            $fd,
            Ev::WRITE,
            fn (EvIo $ev) => $callback($ev->data),
            $data,
            $priority
        );
    }
}
