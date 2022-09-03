<?php

declare(strict_types=1);

namespace Plattry\Event\Dispatch;

use Closure;
use InvalidArgumentException;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * An event-handler provider instance.
 */
class Provider implements ListenerProviderInterface
{
    /**
     * The map that event and event-handlers.
     * @var Closure[][]
     */
    protected array $listeners = [];

    /**
     * Add a handler.
     * @param HandlerInterface $handler
     * @return void
     */
    public function addHandler(HandlerInterface $handler): void
    {
        $this->addListener($handler->getName(), $handler->handle(...), $handler->getPriority());
    }

    /**
     * Add a listener.
     * @param string $eventName
     * @param callable $callable
     * @param int $priority
     * @return void
     */
    public function addListener(string $eventName, callable $callable, int $priority = 0): void
    {
        !class_exists($eventName) &&
        throw new InvalidArgumentException("Not found class $eventName");

        $implements = class_implements($eventName);
        $implements === false || !isset($implements[EventInterface::class]) &&
        throw new InvalidArgumentException("Invalid event $eventName");

        $this->listeners[$eventName][$priority][] = $callable;

        ksort($this->listeners[$eventName]);
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        return array_reduce(
            $this->listeners[$event::class] ?? [],
            fn($front, $next) => [...$front ?: [], ...$next],
            []
        );
    }
}
