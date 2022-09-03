<?php

declare(strict_types=1);

namespace Plattry\Event\Dispatch;

/**
 * Describe a handler instance.
 */
interface HandlerInterface
{
    /**
     * The event name.
     * @return string
     */
    public function getName(): string;

    /**
     * The listener priority.
     * @return int
     */
    public function getPriority(): int;

    /**
     * Handle the event and return it.
     * @param object $event
     * @return object
     */
    public function handle(object $event): object;
}
