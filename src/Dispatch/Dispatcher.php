<?php

declare(strict_types=1);

namespace Plattry\Event\Dispatch;

use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * An event dispatcher instance.
 */
class Dispatcher implements EventDispatcherInterface
{
    /**
     * The listener-provider instance.
     * @var ListenerProviderInterface
     */
    protected ListenerProviderInterface $provider;

    /**
     * The constructor.
     * @param ListenerProviderInterface $provider
     */
    public function __construct(ListenerProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function dispatch(object $event): object
    {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            $event = call_user_func($listener, $event);

            !$event instanceof EventInterface &&
            throw new LogicException("Event callable must return the event.");

            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped())
                return $event;
        }

        return $event;
    }
}
