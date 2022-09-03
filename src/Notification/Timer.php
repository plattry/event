<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

use EvTimer;

/**
 * A timer watcher instance.
 */
class Timer extends WatcherAbstract
{
    /**
     * @inheritDoc
     */
    protected static function key(mixed $id): int|string
    {
        return strval($id);
    }

    /**
     * Add a sub-watcher with delay seconds and interval seconds, and return id.
     * @param float $delay Execute immediately if 0.
     * @param float $interval Execute only once if 0.
     * @param callable $callback
     * @param string|array|null $data
     * @param int $priority
     * @return string
     */
    public function add(float $delay, float $interval, callable $callback, string|array|null $data = null, int $priority = 0): string
    {
        $id = uniqid();

        $this->pool[self::key($id)] = $this->loop->timer(
            $delay,
            $interval,
            fn (EvTimer $ev) => $callback($ev->data),
            $data,
            $priority
        );

        return $id;
    }
}
