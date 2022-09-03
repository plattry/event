<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

use EvSignal;

/**
 * A system signal watcher instance.
 */
class Signal extends WatcherAbstract
{
    /**
     * @inheritDoc
     */
    protected static function key(mixed $id): int|string
    {
        return intval($id);
    }

    /**
     * Add a sub-watcher with system signal.
     * @param int $signal
     * @param mixed $callback
     * @param string|array|null $data
     * @param int $priority
     * @return void
     */
    public function add(int $signal, mixed $callback, string|array|null $data = null, int $priority = 0): void
    {
        $this->pool[static::key($signal)] = $this->loop->signal(
            $signal,
            fn (EvSignal $ev) => $callback($ev->data),
            $data,
            $priority
        );
    }
}
